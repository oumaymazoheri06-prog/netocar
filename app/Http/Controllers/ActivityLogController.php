<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\agencies;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['agency', 'user'])->latest();

        if (! $this->userIsAdmin()) {
            $query->where('agency_id', $this->requiredAgencyId());
        } elseif ($request->filled('agency_id')) {
            $query->where('agency_id', $request->integer('agency_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();

            $query->where(function ($query) use ($search) {
                $query->where('user_name', 'like', "%{$search}%")
                    ->orWhere('subject_label', 'like', "%{$search}%")
                    ->orWhere('agency_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $actionQuery = ActivityLog::query();

        if (! $this->userIsAdmin()) {
            $actionQuery->where('agency_id', $this->requiredAgencyId());
        }

        return view('activity_logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'actions' => $actionQuery->select('action')->distinct()->orderBy('action')->pluck('action'),
            'agencies' => $this->userIsAdmin() ? agencies::orderBy('name')->get() : collect(),
        ]);
    }
}
