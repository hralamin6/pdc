<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Notifications\ActivityNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct()
    {
        // Add permission middleware to all methods
        $this->middleware('permission:activity.feed')->only(['export', 'stats']);
        $this->middleware('permission:activity.delete')->only(['notifyAdmins', 'clear']);
    }

    /**
     * Export activities as JSON
     */
    public function export(Request $request): JsonResponse
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->when($request->log_name, fn($q) => $q->where('log_name', $request->log_name))
            ->when($request->event, fn($q) => $q->where('event', $request->event))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $query->count(),
            'activities' => $query,
        ]);
    }

    /**
     * Get activity statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        $stats = [
            'total' => Activity::where('created_at', '>=', $startDate)->count(),
            'by_log' => Activity::where('created_at', '>=', $startDate)
                ->selectRaw('log_name, count(*) as count')
                ->groupBy('log_name')
                ->get(),
            'by_event' => Activity::where('created_at', '>=', $startDate)
                ->selectRaw('event, count(*) as count')
                ->groupBy('event')
                ->get(),
            'unique_users' => Activity::where('created_at', '>=', $startDate)
                ->whereNotNull('causer_id')
                ->distinct('causer_id')
                ->count('causer_id'),
        ];

        return response()->json($stats);
    }

    /**
     * Notify admin users about important activity
     */
    public function notifyAdmins(Activity $activity): JsonResponse
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            if ($admin->pushSubscriptions()->exists()) {
                $admin->notify(new ActivityNotification(
                    $activity,
                    'Important activity: ' . $activity->description
                ));
            }
        }

        return response()->json([
            'success' => true,
            'notified' => $admins->count(),
        ]);
    }

    /**
     * Clear old activities
     */
    public function clear(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
            'log_name' => 'nullable|string',
            'event' => 'nullable|string',
        ]);

        $query = Activity::query();

        // Apply filters
        if (isset($validated['days'])) {
            $date = now()->subDays($validated['days']);
            $query->where('created_at', '<', $date);
        }

        if (isset($validated['log_name'])) {
            $query->where('log_name', $validated['log_name']);
        }

        if (isset($validated['event'])) {
            $query->where('event', $validated['event']);
        }

        $count = $query->count();
        $query->delete();

        // Log the cleanup action
        \App\Services\ActivityLogger::logSystem('Activities cleared', [
            'count' => $count,
            'filters' => $validated,
        ]);

        return response()->json([
            'success' => true,
            'deleted' => $count,
            'message' => "Successfully deleted {$count} activities.",
        ]);
    }
}
