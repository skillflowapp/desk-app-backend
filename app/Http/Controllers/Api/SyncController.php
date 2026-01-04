<?php

namespace App\Http\Controllers\Api;

use App\Services\OfflineSyncService;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SyncController
{
    /**
     * Sync offline data to server
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'sync_items' => 'required|array',
            'sync_items.*.entity_type' => 'required|string',
            'sync_items.*.entity_id' => 'required|integer',
            'sync_items.*.action' => 'required|in:create,update,delete',
            'sync_items.*.payload' => 'required|array',
            'device_info' => 'nullable|array',
        ]);

        try {
            $syncService = new OfflineSyncService();
            $result = $syncService->syncOfflineData($request->user(), $validated);

            return response()->json([
                'message' => 'Offline data synced',
                'sync_result' => $result,
            ], 200);
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'sync_failed',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get pending sync items
     * Used by offline client to fetch what needs to be sent
     */
    public function getPending(Request $request)
    {
        try {
            $syncService = new OfflineSyncService();
            $pendingItems = $syncService->getPendingSyncItems($request->user());

            return response()->json([
                'pending_items' => $pendingItems,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch pending items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Acknowledge sync item (mark as synced)
     */
    public function acknowledge(Request $request)
    {
        $validated = $request->validate([
            'sync_item_id' => 'required|integer|exists:sync_queues,id',
        ]);

        try {
            $syncService = new OfflineSyncService();
            $syncService->markAsSynced($request->user(), $validated['sync_item_id']);

            return response()->json([
                'message' => 'Sync item acknowledged',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to acknowledge',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check sync status
     */
    public function status(Request $request)
    {
        $pendingSyncCount = \App\Models\SyncQueue::where('student_id', $request->user()->id)
            ->where('status', 'pending')
            ->count();

        $failedSyncCount = \App\Models\SyncQueue::where('student_id', $request->user()->id)
            ->where('status', 'failed')
            ->count();

        return response()->json([
            'pending_count' => $pendingSyncCount,
            'failed_count' => $failedSyncCount,
            'last_sync' => \App\Models\SyncQueue::where('student_id', $request->user()->id)
                ->where('status', 'synced')
                ->latest('synced_at')
                ->first()?->synced_at,
        ], 200);
    }
}
