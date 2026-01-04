<?php

namespace App\Services;

use App\Models\User;
use App\Models\SyncQueue;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use App\Models\AuditLog;
use Exception;

class OfflineSyncService
{
    /**
     * Validate and sync offline data
     * Called when client reconnects
     */
    public function syncOfflineData(User $student, array $offlineData): array
    {
        $syncResults = [
            'status' => 'success',
            'synced' => 0,
            'failed' => 0,
            'errors' => [],
            'timestamp' => now(),
        ];

        try {
            // Process each sync item
            foreach ($offlineData['sync_items'] ?? [] as $item) {
                try {
                    $this->processSyncItem($student, $item);
                    $syncResults['synced']++;
                } catch (Exception $e) {
                    $syncResults['failed']++;
                    $syncResults['errors'][] = [
                        'item' => $item['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Log sync attempt
            AuditLog::log(
                userId: $student->id,
                action: 'offline_sync_completed',
                newValues: [
                    'synced' => $syncResults['synced'],
                    'failed' => $syncResults['failed'],
                ]
            );

            return $syncResults;
        } catch (Exception $e) {
            $syncResults['status'] = 'failed';
            $syncResults['errors'][] = $e->getMessage();
            
            AuditLog::log(
                userId: $student->id,
                action: 'offline_sync_failed',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return $syncResults;
        }
    }

    /**
     * Process individual sync item
     */
    private function processSyncItem(User $student, array $item): void
    {
        $entityType = $item['entity_type'] ?? null;
        $action = $item['action'] ?? 'create';

        match ($entityType) {
            'exam_session' => $this->syncExamSession($student, $item, $action),
            'student_answer' => $this->syncStudentAnswer($student, $item, $action),
            default => throw new Exception("Unknown entity type: $entityType"),
        };
    }

    /**
     * Sync exam session data
     */
    private function syncExamSession(User $student, array $item, string $action): void
    {
        $data = $item['payload'];

        // Validate exam code
        $examCode = \App\Models\ExamCode::where('code', $data['exam_code'] ?? '')
            ->firstOrFail();

        if (!$examCode->isValid()) {
            throw new Exception('Exam code is no longer valid');
        }

        // Find or create session
        $session = ExamSession::where('exam_code_id', $examCode->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$session) {
            if ($action !== 'create') {
                throw new Exception('Exam session not found');
            }

            // Create new session
            $session = ExamSession::create([
                'exam_id' => $examCode->exam_id,
                'student_id' => $student->id,
                'exam_code_id' => $examCode->id,
                'started_at' => $data['started_at'],
                'status' => 'in_progress',
                'device_fingerprint' => $data['device_fingerprint'] ?? null,
                'ip_address' => request()->ip(),
                'meta_data' => $data['meta_data'] ?? [],
            ]);

            // Create blank result
            \App\Models\Result::create([
                'exam_session_id' => $session->id,
                'exam_id' => $examCode->exam_id,
                'student_id' => $student->id,
                'total_marks' => $examCode->exam->total_marks,
                'status' => 'pending',
            ]);
        }

        // Validate device fingerprint (anti-cheating)
        if ($session->device_fingerprint && $session->device_fingerprint !== ($data['device_fingerprint'] ?? '')) {
            $session->update([
                'flagged_for_review' => true,
                'flag_reason' => 'Device fingerprint mismatch - possible device change during offline sync',
            ]);

            throw new Exception('Device verification failed - session flagged for review');
        }

        // Check exam time didn't exceed limit
        $examDuration = $session->exam->duration_minutes * 60;
        $timeElapsed = now()->diffInSeconds($session->started_at);
        
        if ($timeElapsed > $examDuration) {
            $session->update(['status' => 'timed_out']);
            throw new Exception('Exam time has exceeded the limit');
        }
    }

    /**
     * Sync student answers
     */
    private function syncStudentAnswer(User $student, array $item, string $action): void
    {
        $data = $item['payload'];

        // Find exam session
        $session = ExamSession::find($data['exam_session_id'])
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Validate session is still active
        if ($session->status !== 'in_progress') {
            throw new Exception('Exam session is not active');
        }

        // Check time limit
        if ($session->isExpired()) {
            $session->update(['status' => 'timed_out']);
            throw new Exception('Exam time has expired');
        }

        // Find or create answer
        $answer = StudentAnswer::where('exam_session_id', $session->id)
            ->where('exam_question_id', $data['exam_question_id'])
            ->first();

        if ($action === 'create' && !$answer) {
            StudentAnswer::create([
                'exam_session_id' => $session->id,
                'exam_question_id' => $data['exam_question_id'],
                'answer_text' => $data['answer_text'] ?? null,
                'selected_option' => $data['selected_option'] ?? null,
                'answered_at' => $data['answered_at'],
                'time_spent_seconds' => $data['time_spent_seconds'] ?? 0,
                'is_final' => false,
            ]);
        } elseif ($action === 'update' && $answer) {
            $answer->update([
                'answer_text' => $data['answer_text'] ?? $answer->answer_text,
                'selected_option' => $data['selected_option'] ?? $answer->selected_option,
                'answered_at' => $data['answered_at'],
                'time_spent_seconds' => $data['time_spent_seconds'] ?? $answer->time_spent_seconds,
            ]);
        }
    }

    /**
     * Get pending sync items for offline client
     */
    public function getPendingSyncItems(User $student): array
    {
        return SyncQueue::where('student_id', $student->id)
            ->where('status', 'pending')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'entity_type' => $item->entity_type,
                'entity_id' => $item->entity_id,
                'action' => $item->action,
                'payload' => $item->payload,
            ])
            ->toArray();
    }

    /**
     * Mark sync item as synced
     */
    public function markAsSynced(User $student, int $itemId): void
    {
        SyncQueue::where('id', $itemId)
            ->where('student_id', $student->id)
            ->update([
                'status' => 'synced',
                'synced_at' => now(),
            ]);
    }

    /**
     * Queue data for offline sync
     */
    public function queueForSync(
        User $student,
        string $entityType,
        int $entityId,
        string $action,
        array $payload
    ): SyncQueue {
        return SyncQueue::create([
            'student_id' => $student->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }
}
