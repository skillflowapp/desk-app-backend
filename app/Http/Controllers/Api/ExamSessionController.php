<?php

namespace App\Http\Controllers\Api;

use App\Models\Exam;
use App\Models\ExamCode;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use App\Models\Result;
use App\Models\AuditLog;
use App\Jobs\GradeExamSession;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExamSessionController
{
    /**
     * Student enters exam using exam code
     */
    public function enter(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'device_fingerprint' => 'nullable|string',
        ]);

        // Find exam code
        $examCode = ExamCode::where('code', $validated['code'])->first();

        if (!$examCode) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_enter_invalid_code',
                entityType: 'exam_code',
                status: 'failed',
                errorMessage: 'Invalid code'
            );

            return response()->json([
                'message' => 'Invalid exam code',
            ], 422);
        }

        // Validate code is active and not expired
        if (!$examCode->isValid()) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_enter_expired_code',
                entityType: 'exam_code',
                entityId: $examCode->id,
                status: 'failed',
                errorMessage: 'Code expired or inactive'
            );

            return response()->json([
                'message' => 'This exam code is no longer valid',
            ], 422);
        }

        // Check if student already has an active session
        $existingSession = ExamSession::where('exam_code_id', $examCode->id)
            ->where('student_id', $request->user()->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingSession) {
            return response()->json([
                'message' => 'You already have an active session for this exam',
                'session' => $existingSession->load('exam'),
            ], 200);
        }

        // Check attempt limit
        $previousSessions = ExamSession::where('exam_code_id', $examCode->id)
            ->where('student_id', $request->user()->id)
            ->count();

        if ($previousSessions >= $examCode->max_attempts) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_enter_max_attempts',
                entityType: 'exam_code',
                entityId: $examCode->id,
                status: 'failed',
                errorMessage: 'Maximum attempts exceeded'
            );

            return response()->json([
                'message' => 'You have reached the maximum attempts for this exam',
            ], 422);
        }

        try {
            // Create exam session
            $session = ExamSession::create([
                'exam_id' => $examCode->exam_id,
                'student_id' => $request->user()->id,
                'exam_code_id' => $examCode->id,
                'started_at' => now(),
                'status' => 'in_progress',
                'device_fingerprint' => $validated['device_fingerprint'] ?? null,
                'ip_address' => $request->ip(),
                'meta_data' => [
                    'user_agent' => $request->userAgent(),
                    'browser' => $this->parseUserAgent($request->userAgent()),
                ],
            ]);

            // Create blank result
            Result::create([
                'exam_session_id' => $session->id,
                'exam_id' => $examCode->exam_id,
                'student_id' => $request->user()->id,
                'total_marks' => $examCode->exam->total_marks,
                'status' => 'pending',
            ]);

            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_session_started',
                entityType: 'exam_session',
                entityId: $session->id,
                newValues: ['exam_id' => $session->exam_id, 'session_id' => $session->id]
            );

            return response()->json([
                'message' => 'Exam session started',
                'session' => $session->load('exam'),
            ], 201);
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_session_failed',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => 'Failed to start exam session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student's active exam session
     */
    public function active(Request $request)
    {
        $session = ExamSession::where('student_id', $request->user()->id)
            ->where('status', 'in_progress')
            ->with('exam.questions', 'answers')
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'No active exam session',
            ], 404);
        }

        return response()->json([
            'session' => $session,
            'time_remaining_seconds' => $session->timeRemainingSeconds(),
        ], 200);
    }

    /**
     * Get exam session details
     */
    public function show(Request $request, ExamSession $examSession)
    {
        // Authorization
        if ($request->user()->id !== $examSession->student_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if session is expired
        if ($examSession->isExpired() && $examSession->status === 'in_progress') {
            $examSession->update(['status' => 'timed_out']);
            
            return response()->json([
                'message' => 'Exam time has expired',
                'session' => $examSession,
            ], 422);
        }

        $examSession->load('exam.questions', 'answers');

        return response()->json([
            'session' => $examSession,
            'time_remaining_seconds' => $examSession->timeRemainingSeconds(),
        ], 200);
    }

    /**
     * Submit an answer
     */
    public function submitAnswer(Request $request, ExamSession $examSession)
    {
        // Authorization
        if ($request->user()->id !== $examSession->student_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check session status
        if ($examSession->status !== 'in_progress') {
            return response()->json([
                'message' => 'Exam session is no longer active',
            ], 422);
        }

        // Check if time expired
        if ($examSession->isExpired()) {
            $examSession->update(['status' => 'timed_out']);
            
            return response()->json([
                'message' => 'Exam time has expired',
            ], 422);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:exam_questions,id',
            'answer_text' => 'nullable|string',
            'selected_option' => 'nullable|string|max:10',
            'time_spent_seconds' => 'nullable|integer|min:0',
        ]);

        try {
            // Verify question belongs to exam
            $question = $examSession->exam->questions()
                ->find($validated['question_id']);

            if (!$question) {
                return response()->json([
                    'message' => 'Question not found in exam',
                ], 422);
            }

            // Check for existing answer
            $existingAnswer = StudentAnswer::where('exam_session_id', $examSession->id)
                ->where('exam_question_id', $question->id)
                ->first();

            if ($existingAnswer) {
                // Update existing answer
                $existingAnswer->update([
                    'answer_text' => $validated['answer_text'] ?? $existingAnswer->answer_text,
                    'selected_option' => $validated['selected_option'] ?? $existingAnswer->selected_option,
                    'time_spent_seconds' => $validated['time_spent_seconds'] ?? $existingAnswer->time_spent_seconds,
                    'answered_at' => now(),
                ]);

                AuditLog::log(
                    userId: $request->user()->id,
                    action: 'answer_updated',
                    entityType: 'student_answer',
                    entityId: $existingAnswer->id
                );

                return response()->json([
                    'message' => 'Answer updated',
                    'answer' => $existingAnswer,
                ], 200);
            } else {
                // Create new answer
                $answer = StudentAnswer::create([
                    'exam_session_id' => $examSession->id,
                    'exam_question_id' => $question->id,
                    'answer_text' => $validated['answer_text'] ?? null,
                    'selected_option' => $validated['selected_option'] ?? null,
                    'answered_at' => now(),
                    'time_spent_seconds' => $validated['time_spent_seconds'] ?? 0,
                    'is_final' => true,
                ]);

                AuditLog::log(
                    userId: $request->user()->id,
                    action: 'answer_submitted',
                    entityType: 'student_answer',
                    entityId: $answer->id
                );

                return response()->json([
                    'message' => 'Answer recorded',
                    'answer' => $answer,
                ], 201);
            }
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'answer_submission_failed',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => 'Failed to record answer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit exam (finish and lock session)
     */
    public function submit(Request $request, ExamSession $examSession)
    {
        // Authorization
        if ($request->user()->id !== $examSession->student_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check status
        if ($examSession->status !== 'in_progress') {
            return response()->json([
                'message' => 'Exam session is not active',
            ], 422);
        }

        try {
            // Mark as submitted
            $examSession->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // Mark all answers as final
            StudentAnswer::where('exam_session_id', $examSession->id)
                ->update(['is_final' => true]);

            // Dispatch grading job
            GradeExamSession::dispatch($examSession);

            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_submitted',
                entityType: 'exam_session',
                entityId: $examSession->id,
                newValues: ['status' => 'submitted']
            );

            return response()->json([
                'message' => 'Exam submitted successfully. Grading in progress.',
                'session' => $examSession,
            ], 200);
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_submission_failed',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => 'Failed to submit exam',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student's results
     */
    public function myResults(Request $request)
    {
        $results = Result::where('student_id', $request->user()->id)
            ->where('status', 'published')
            ->with('exam', 'examSession')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'results' => $results,
        ], 200);
    }

    /**
     * Get individual result
     */
    public function showResult(Request $request, Result $result)
    {
        // Authorization
        if ($request->user()->id !== $result->student_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Only show published results to student
        if ($result->status !== 'published' && !$request->user()->isTeacher()) {
            return response()->json([
                'message' => 'Result not yet published',
            ], 403);
        }

        $result->load('exam', 'examSession.answers');

        return response()->json([
            'result' => $result,
        ], 200);
    }

    /**
     * Parse user agent to extract browser info
     */
    private function parseUserAgent(string $userAgent): array
    {
        $browser = 'Unknown';
        $os = 'Unknown';

        if (preg_match('/Chrome\//', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\//', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\//', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge\//', $userAgent)) {
            $browser = 'Edge';
        }

        if (preg_match('/Windows/', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            $os = 'iOS';
        }

        return compact('browser', 'os');
    }
}
