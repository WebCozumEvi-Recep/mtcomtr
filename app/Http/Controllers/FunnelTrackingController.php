<?php

namespace App\Http\Controllers;

use App\Models\FunnelEvent;
use Illuminate\Http\Request;

class FunnelTrackingController extends Controller
{
    public function track(Request $request)
    {
        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'event_type' => 'required|string',
            'session_id' => 'required|string',
        ]);

        FunnelEvent::create([
            'domain_id' => $request->domain_id,
            'session_id' => $request->session_id,
            'event_type' => $request->event_type,
            'event_value' => $request->event_value,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true]);
    }
}
