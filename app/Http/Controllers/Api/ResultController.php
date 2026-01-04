<?php

namespace App\Http\Controllers\Api;

use App\Models\Exam;
use App\Models\Result;
use App\Models\AuditLog;
use App\Services\PdfExportService;
use Illuminate\Http\Request;

class ResultController
{
    /**
     * Get single result (for teacher or student)
     */
    public function show(Request $request, Result $result)
    {
        // Authorization
        $isTeacher = $request->user()->id === $result->exam->teacher_id;
        $isStudent = $request->user()->id === $result->student_id;

        if (!$isTeacher && !$isStudent) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Students can only view published results
        if (!$isTeacher && $result->status !== 'published') {
            return response()->json([
                'message' => 'Result not yet published',
            ], 403);
        }

        $result->load('exam', 'student', 'examSession.answers');

        return response()->json([
            'result' => $result,
        ], 200);
    }

    /**
     * Publish result to student
     */
    public function publish(Request $request, Result $result)
    {
        // Authorization: only teacher can publish
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

        if ($result->status !== 'graded') {
            return response()->json([
                'message' => 'Only graded results can be published',
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
            newValues: ['status' => 'published', 'published_at' => $result->published_at]
        );

        return response()->json([
            'message' => 'Result published successfully',
            'result' => $result,
        ], 200);
    }

    /**
     * Add teacher remarks to result
     */
    public function addRemarks(Request $request, Result $result)
    {
        // Authorization: only teacher
        $exam = $result->exam;
        if ($request->user()->id !== $exam->teacher_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $old_remarks = $result->teacher_remarks;
        $result->update([
            'teacher_remarks' => $validated['remarks'],
        ]);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'result_remarks_added',
            entityType: 'result',
            entityId: $result->id,
            oldValues: ['remarks' => $old_remarks],
            newValues: ['remarks' => $validated['remarks']]
        );

        return response()->json([
            'message' => 'Remarks added successfully',
            'result' => $result,
        ], 200);
    }

    /**
     * Export result as PDF
     */
    public function exportPdf(Request $request, Result $result)
    {
        // Authorization
        $isTeacher = $request->user()->id === $result->exam->teacher_id;
        $isStudent = $request->user()->id === $result->student_id;

        if (!$isTeacher && !$isStudent) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Students can only export published results
        if (!$isTeacher && $result->status !== 'published') {
            return response()->json([
                'message' => 'Result not yet published',
            ], 403);
        }

        try {
            $pdfService = new PdfExportService();
            $pdfContent = $pdfService->exportResult($result);

            AuditLog::log(
                userId: $request->user()->id,
                action: 'result_exported',
                entityType: 'result',
                entityId: $result->id,
                newValues: ['format' => 'pdf']
            );

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="result_' . $result->id . '.pdf"');
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'result_export_failed',
                entityType: 'result',
                entityId: $result->id,
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => 'Failed to export PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Flag result for review
     */
    public function flag(Request $request, Result $result)
    {
        // Authorization: only teacher or student
        $isTeacher = $request->user()->id === $result->exam->teacher_id;
        $isStudent = $request->user()->id === $result->student_id;

        if (!$isTeacher && !$isStudent) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $examSession = $result->examSession;
        $examSession->update([
            'flagged_for_review' => true,
            'flag_reason' => $validated['reason'],
        ]);

        AuditLog::log(
            userId: $request->user()->id,
            action: 'result_flagged',
            entityType: 'exam_session',
            entityId: $examSession->id,
            newValues: ['flagged' => true, 'reason' => $validated['reason']]
        );

        return response()->json([
            'message' => 'Result flagged for review',
            'session' => $examSession,
        ], 200);
    }
}
