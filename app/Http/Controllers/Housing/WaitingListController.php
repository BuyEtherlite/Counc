<?php

namespace App\Http\Controllers\Housing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Housing\WaitingList;
use App\Models\Housing\HousingApplication;

class WaitingListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = WaitingList::with(['application'])
                           ->orderBy('position');

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        if ($request->housing_type) {
            $query->where('housing_type_preference', $request->housing_type);
        }

        $waitingList = $query->paginate(15);
        
        $stats = [
            'total_active' => WaitingList::where('status', 'active')->count(),
            'total_contacted' => WaitingList::where('status', 'contacted')->count(),
            'total_offered' => WaitingList::where('status', 'offered')->count(),
            'average_wait_time' => $this->calculateAverageWaitTime(),
        ];

        return view('housing.waiting-list.index', compact('waitingList', 'stats'));
    }

    public function show(WaitingList $waitingList)
    {
        $waitingList->load(['application', 'allocation']);
        return view('housing.waiting-list.show', compact('waitingList'));
    }

    public function contact(Request $request, WaitingList $waitingList)
    {
        $request->validate([
            'contact_method' => 'required|in:phone,email,sms',
            'notes' => 'nullable|string',
        ]);

        $waitingList->update([
            'status' => 'contacted',
            'last_contacted' => now(),
            'contact_attempts' => $waitingList->contact_attempts + 1,
            'notes' => $request->notes,
        ]);

        return redirect()->route('housing.waiting-list.show', $waitingList)
                        ->with('success', 'Contact recorded successfully.');
    }

    public function updateStatus(Request $request, WaitingList $waitingList)
    {
        $request->validate([
            'status' => 'required|in:active,contacted,offered,declined,allocated,removed',
            'notes' => 'nullable|string',
        ]);

        $waitingList->update([
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // If status changed to declined or removed, recalculate positions
        if (in_array($request->status, ['declined', 'removed', 'allocated'])) {
            WaitingList::recalculateAllPositions();
        }

        return redirect()->route('housing.waiting-list.show', $waitingList)
                        ->with('success', 'Status updated successfully.');
    }

    public function updatePriority(Request $request, WaitingList $waitingList)
    {
        $request->validate([
            'priority_score' => 'required|integer|min:0|max:200',
            'reason' => 'required|string',
        ]);

        $waitingList->update([
            'priority_score' => $request->priority_score,
            'notes' => ($waitingList->notes ?? '') . "\n[" . now()->format('Y-m-d H:i') . "] Priority updated: " . $request->reason,
        ]);

        // Recalculate positions after priority change
        WaitingList::recalculateAllPositions();

        return redirect()->route('housing.waiting-list.show', $waitingList)
                        ->with('success', 'Priority score updated successfully.');
    }

    public function bulkContact(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:housing_waiting_list,id',
            'contact_method' => 'required|in:phone,email,sms',
            'message' => 'required|string',
        ]);

        $updated = WaitingList::whereIn('id', $request->ids)
                             ->where('status', 'active')
                             ->update([
                                 'status' => 'contacted',
                                 'last_contacted' => now(),
                                 'contact_attempts' => \DB::raw('contact_attempts + 1'),
                             ]);

        return redirect()->route('housing.waiting-list.index')
                        ->with('success', "Contacted {$updated} applicants successfully.");
    }

    public function recalculatePositions()
    {
        WaitingList::recalculateAllPositions();

        return redirect()->route('housing.waiting-list.index')
                        ->with('success', 'All positions recalculated successfully.');
    }

    private function calculateAverageWaitTime()
    {
        $allocatedEntries = WaitingList::where('status', 'allocated')
                                     ->whereHas('allocation')
                                     ->with('allocation')
                                     ->get();

        if ($allocatedEntries->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        foreach ($allocatedEntries as $entry) {
            $waitDays = $entry->date_added->diffInDays($entry->allocation->allocation_date);
            $totalDays += $waitDays;
        }

        return round($totalDays / $allocatedEntries->count());
    }
}