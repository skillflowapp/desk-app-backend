<?php

namespace App\Http\Controllers\Api;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamCode;
use App\Models\Result;
use App\Models\PdfUpload;
use App\Models\AuditLog;
use App\Services\ExamGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExamController
{
    /**
     * Get all exams for teacher
     */
    public function index(Request $request)
    {
        $exams = $request->user()
            ->createdExams()
            ->with('pdfUpload', 'questions', 'codes', 'sessions')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'exams' => $exams,
        ], 200);
    }

    /**
     * Create a new exam
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'total_marks' => 'required|integer|min:1|max:1000',
            'passing_marks' => 'required|integer|min:0',
            'pdf_upload_id' => 'nullable|exists:pdf_uploads,id',
            'show_answers_after' => 'nullable|boolean',
            'ai_prompt' => 'nullable|json',
        ]);

        // Ensure passing marks < total marks
        if ($validated['passing_marks'] >= $validated['total_marks']) {
            return response()->json([
                'message' => 'Passing marks must be less than total marks',
            ], 422);
        }

        $exam = $request->user()->createdExams()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'total_marks' => $validated['total_marks'],
            'passing_marks' => $validated['passing_marks'],
            'pdf_upload_id' => $validated['pdf_upload_id'] ?? null,
            'show_answers_after' => $validated['show_answers_after'] ?? true,
            'ai_prompt' => $validated['ai_prompt'] ?? null,
            'status' => 'draft',
        ]);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'exam_created',
            entityType: 'exam',
            entityId: $exam->id,
            newValues: $exam->toArray()
        );

        return response()->json([
            'message' => 'Exam created successfully',
            'exam' => $exam,
        ], 201);
    }

    /**
     * Get exam details
     */
    public function show(Request $request, Exam $exam)
    {
        // Authorization: only teacher who created it or student in session can view
        if ($request->user()->id !== $exam->teacher_id) {
            // Check if student is in an active session
            $session = $exam->sessions()
                ->where('student_id', $request->user()->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$session) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }
        }

        $exam->load('questions', 'pdfUpload');

        return response()->json([
            'exam' => $exam,
        ], 200);
    }

    /**
     * Update exam (only in draft status)
     */
    public function update(Request $request, Exam $exam)
    {
        // Authorization
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Only allow updates on draft exams
        if ($exam->status !== 'draft') {
            return response()->json([
                'message' => 'Can only update draft exams',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:5|max:480',
            'total_marks' => 'nullable|integer|min:1|max:1000',
            'passing_marks' => 'nullable|integer|min:0',
            'show_answers_after' => 'nullable|boolean',
            'ai_prompt' => 'nullable|json',
        ]);

        $old_values = $exam->toArray();
        $exam->update($validated);
        
        AuditLog::log(
            userId: $request->user()->id,
            action: 'exam_updated',
            entityType: 'exam',
            entityId: $exam->id,
            oldValues: $old_values,
            newValues: $exam->toArray()
        );

        return response()->json([
            'message' => 'Exam updated successfully',
            'exam' => $exam,
        ], 200);
    }

    /**
     * Delete exam (only in draft status)
     */
    public function destroy(Request $request, Exam $exam)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($exam->status !== 'draft') {
            return response()->json([
                'message' => 'Can only delete draft exams',
            ], 422);
        }

        $examId = $exam->id;
        $exam->delete();

        AuditLog::log(
            userId: $request->user()->id,
            action: 'exam_deleted',
            entityType: 'exam',
            entityId: $examId,
            newValues: null
        );

        return response()->json([
            'message' => 'Exam deleted successfully',
        ], 200);
    }

    /**
     * Publish exam (mark as published)
     */
    public function publish(Request $request, Exam $exam)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($exam->status === 'published') {
            return response()->json([
                'message' => 'Exam is already published',
            ], 422);
        }

        // Validate exam has questions
        if ($exam->questions()->count() === 0) {
            return response()->json([
                'message' => 'Exam must have at least one question',
            ], 422);
        }

        $exam->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'exam_published',
            entityType: 'exam',
            entityId: $exam->id,
            newValues: ['status' => 'published']
        );

        return response()->json([
            'message' => 'Exam published successfully',
            'exam' => $exam,
        ], 200);
    }

    /**
     * Add question to exam
     */
    public function addQuestion(Request $request, Exam $exam)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in('mcq', 'short_answer', 'essay')],
            'question_text' => 'required|string',
            'options' => 'required_if:type,mcq|json',
            'correct_answer' => 'required_if:type,mcq|string',
            'model_answer' => 'nullable|string',
            'marks' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string',
        ]);

        // Get next question number
        $nextNumber = $exam->questions()->max('question_number') + 1;

        $question = $exam->questions()->create([
            'question_number' => $nextNumber,
            'type' => $validated['type'],
            'question_text' => $validated['question_text'],
            'options' => $validated['options'] ?? null,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'model_answer' => $validated['model_answer'] ?? null,
            'marks' => $validated['marks'],
            'explanation' => $validated['explanation'] ?? null,
            'order' => $nextNumber,
        ]);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'question_added',
            entityType: 'exam_question',
            entityId: $question->id,
            newValues: $question->toArray()
        );

        return response()->json([
            'message' => 'Question added successfully',
            'question' => $question,
        ], 201);
    }

    /**
     * Update exam question
     */
    public function updateQuestion(Request $request, Exam $exam, ExamQuestion $question)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($question->exam_id !== $exam->id) {
            return response()->json([
                'message' => 'Question does not belong to this exam',
            ], 422);
        }

        $validated = $request->validate([
            'question_text' => 'nullable|string',
            'options' => 'nullable|json',
            'correct_answer' => 'nullable|string',
            'model_answer' => 'nullable|string',
            'marks' => 'nullable|integer|min:1|max:100',
            'explanation' => 'nullable|string',
        ]);

        $old_values = $question->toArray();
        $question->update($validated);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'question_updated',
            entityType: 'exam_question',
            entityId: $question->id,
            oldValues: $old_values,
            newValues: $question->toArray()
        );

        return response()->json([
            'message' => 'Question updated successfully',
            'question' => $question,
        ], 200);
    }

    /**
     * Delete exam question
     */
    public function deleteQuestion(Request $request, Exam $exam, ExamQuestion $question)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($question->exam_id !== $exam->id) {
            return response()->json([
                'message' => 'Question does not belong to this exam',
            ], 422);
        }

        $questionId = $question->id;
        $question->delete();

        AuditLog::log(
            userId: $request->user()->id,
            action: 'question_deleted',
            entityType: 'exam_question',
            entityId: $questionId
        );

        return response()->json([
            'message' => 'Question deleted successfully',
        ], 200);
    }

    /**
     * Generate exam code for exam access
     */
    public function generateCode(Request $request, Exam $exam)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'max_attempts' => 'nullable|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Generate unique code
        do {
            $code = Str::upper(Str::random(8));
        } while (ExamCode::where('code', $code)->exists());

        $examCode = $exam->codes()->create([
            'code' => $code,
            'max_attempts' => $validated['max_attempts'] ?? 1,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'exam_code_generated',
            entityType: 'exam_code',
            entityId: $examCode->id,
            newValues: ['code' => $code]
        );

        return response()->json([
            'message' => 'Exam code generated successfully',
            'code' => $examCode->code,
            'exam_code' => $examCode,
        ], 201);
    }

    /**
     * Get exam results
     */
    public function results(Request $request, Exam $exam)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $results = $exam->results()
            ->with('student', 'examSession')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'results' => $results,
        ], 200);
    }

    /**
     * Publish result to student
     */
    public function publishResult(Request $request, Result $result)
    {
        $exam = $result->exam;

        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($result->status === 'published') {
            return response()->json([
                'message' => 'Result already published',
            ], 422);
        }

        $result->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'result_published',
            entityType: 'result',
            entityId: $result->id,
            newValues: ['status' => 'published']
        );

        return response()->json([
            'message' => 'Result published successfully',
            'result' => $result,
        ], 200);
    }

    /**
     * Grade exam (called by AI job)
     */
    public function grade(Request $request, Exam $exam)
    {
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'exam_session_id' => 'required|exists:exam_sessions,id',
        ]);

        // This would be called by a queue job
        // For now, just return success message
        return response()->json([
            'message' => 'Grading job queued',
        ], 200);
    }

    /**
     * Generate exam questions from PDF using AI
     */
    public function generateFromPdf(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'pdf_upload_id' => 'required|exists:pdf_uploads,id',
            'ai_prompt' => 'required|string',
            'number_of_questions' => 'nullable|integer|min:1|max:50',
            'question_types' => 'nullable|string', // 'mcq,short_answer,essay'
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);

        // Authorization
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Exam must be in draft status
        if ($exam->status !== 'draft') {
            return response()->json([
                'message' => 'Can only generate questions for draft exams',
            ], 422);
        }

        $pdfUpload = PdfUpload::findOrFail($validated['pdf_upload_id']);

        // Verify user owns the PDF
        if ($request->user()->id !== $pdfUpload->user_id) {
            return response()->json([
                'message' => 'Unauthorized to use this PDF',
            ], 403);
        }

        try {
            $generationService = new ExamGenerationService();
            
            $questions = $generationService->generateQuestionsFromPdf(
                $pdfUpload,
                $validated['ai_prompt'],
                $validated['number_of_questions'] ?? 10,
                $validated['question_types'] ?? 'mcq,short_answer'
            );

            // Create questions in exam
            $createdQuestions = [];
            $totalMarks = 0;
            $marksPerQuestion = max(1, intdiv($exam->total_marks, count($questions)));

            foreach ($questions as $index => $questionData) {
                $marks = $questionData['marks'] ?? $marksPerQuestion;
                $totalMarks += $marks;

                $question = $exam->questions()->create([
                    'question_number' => $index + 1,
                    'type' => $questionData['type'],
                    'question_text' => $questionData['question_text'],
                    'options' => $questionData['options'] ?? null,
                    'correct_answer' => $questionData['correct_answer'] ?? null,
                    'model_answer' => $questionData['model_answer'] ?? null,
                    'marks' => $marks,
                    'explanation' => $questionData['explanation'] ?? null,
                    'order' => $index + 1,
                ]);

                $createdQuestions[] = $question;
            }

            // Update exam total marks if generated questions exceed it
            if ($totalMarks > $exam->total_marks) {
                $exam->update(['total_marks' => $totalMarks]);
            }

            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_questions_generated',
                entityType: 'exam',
                entityId: $exam->id,
                newValues: ['questions_count' => count($questions), 'total_marks' => $totalMarks]
            );

            return response()->json([
                'message' => 'Questions generated successfully',
                'questions' => $createdQuestions,
                'exam' => $exam,
            ], 201);
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'exam_generation_failed',
                entityType: 'exam',
                entityId: $exam->id,
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => 'Failed to generate questions',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
