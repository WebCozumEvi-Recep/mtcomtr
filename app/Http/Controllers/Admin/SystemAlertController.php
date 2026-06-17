<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemAlert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemAlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = SystemAlert::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $users = User::all();

        return view('admin.alerts.index', compact('alerts', 'users'));
    }

    public function userStatus(User $user)
    {
        $alerts = SystemAlert::with(['causer', 'users' => function($query) use ($user) {
            $query->where('users.id', $user->id);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('admin.alerts.user', compact('user', 'alerts'));
    }

    public function getLatest(Request $request)
    {
        $user = auth()->user();
        
        // Get unread alerts for this user
        $unreadAlerts = SystemAlert::whereDoesntHave('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

        return response()->json([
            'alerts' => $unreadAlerts,
            'unread_count' => $unreadAlerts->count()
        ]);
    }

    public function markAsRead(Request $request, SystemAlert $alert)
    {
        $user = auth()->user();
        
        if (!$alert->users()->where('users.id', $user->id)->exists()) {
            $alert->users()->attach($user->id, ['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $unreadAlerts = SystemAlert::whereDoesntHave('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })->get();

        foreach ($unreadAlerts as $alert) {
            $alert->users()->attach($user->id, ['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
