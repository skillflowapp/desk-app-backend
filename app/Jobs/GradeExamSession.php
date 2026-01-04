<?php

namespace App\Jobs;

use App\Models\ExamSession;
use App\Models\AuditLog;
use App\Services\GradingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GradeExamSession implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300; // 5 minutes
    public int $tries = 2;

    public function __construct(
        public ExamSession $examSession
    ) {}

    public function handle(): void
    {
        try {
            $gradingService = new GradingService();
            $result = $gradingService->gradeExamSession($this->examSession);

            AuditLog::log(
                userId: $this->examSession->student_id,
                action: 'exam_graded',
                entityType: 'result',
                entityId: $result->id,
                newValues: [
                    'status' => 'graded',
                    'obtained_marks' => $result->obtained_marks,
                    'total_marks' => $result->total_marks,
                    'percentage' => $result->percentage,
                ]
            );
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $this->examSession->student_id,
                action: 'exam_grading_failed',
                entityType: 'exam_session',
                entityId: $this->examSession->id,
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                throw $e;
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        $result = $this->examSession->result;
        if ($result) {
            $result->update([
                'status' => 'failed',
            ]);
        }

        AuditLog::log(
            userId: $this->examSession->student_id,
            action: 'exam_grading_failed_final',
            entityType: 'exam_session',
            entityId: $this->examSession->id,
            status: 'failed',
            errorMessage: $exception->getMessage()
        );
    }
}
