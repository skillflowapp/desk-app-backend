<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController
{
    /**
     * Get audit logs (admin only)
     */
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|string',
            'status' => 'nullable|in:success,failed',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = AuditLog::query();

        if ($validated['user_id'] ?? null) {
            $query->where('user_id', $validated['user_id']);
        }

        if ($validated['action'] ?? null) {
            $query->where('action', 'like', '%' . $validated['action'] . '%');
        }

        if ($validated['status'] ?? null) {
            $query->where('status', $validated['status']);
        }

        if ($validated['from_date'] ?? null) {
            $query->whereDate('created_at', '>=', $validated['from_date']);
        }

        if ($validated['to_date'] ?? null) {
            $query->whereDate('created_at', '<=', $validated['to_date']);
        }

        $logs = $query->with('user')
            ->latest('created_at')
            ->paginate($validated['per_page'] ?? 50);

        return response()->json([
            'logs' => $logs,
        ], 200);
    }

    /**
     * Get audit log detail
     */
    public function show(Request $request, AuditLog $auditLog)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $auditLog->load('user');

        return response()->json([
            'log' => $auditLog,
        ], 200);
    }

    /**
     * Get user activity summary
     */
    public function userActivity(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $activities = AuditLog::where('user_id', $user->id)
            ->selectRaw('action, COUNT(*) as count, status')
            ->groupBy('action', 'status')
            ->latest('created_at')
            ->get();

        $loginAttempts = AuditLog::where('user_id', $user->id)
            ->where('action', 'like', '%login%')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'user' => $user->load('roles'),
            'activity_summary' => $activities,
            'recent_logins' => $loginAttempts,
            'last_activity' => AuditLog::where('user_id', $user->id)
                ->latest('created_at')
                ->first(),
        ], 200);
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'format' => 'nullable|in:csv,json',
        ]);

        $logs = AuditLog::whereDate('created_at', '>=', $validated['from_date'])
            ->whereDate('created_at', '<=', $validated['to_date'])
            ->with('user')
            ->get();

        if (($validated['format'] ?? 'json') === 'csv') {
            return $this->exportCsv($logs);
        }

        return response()->json([
            'logs' => $logs,
            'count' => $logs->count(),
            'period' => [
                'from' => $validated['from_date'],
                'to' => $validated['to_date'],
            ],
        ], 200);
    }

    private function exportCsv($logs)
    {
        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, ['User', 'Action', 'Entity Type', 'Entity ID', 'Status', 'IP Address', 'Timestamp']);

        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->user?->name ?? 'System',
                $log->action,
                $log->entity_type,
                $log->entity_id,
                $log->status,
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }
}
