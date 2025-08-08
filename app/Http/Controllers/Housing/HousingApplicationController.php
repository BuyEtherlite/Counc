<?php

namespace App\Http\Controllers\Housing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Housing\HousingApplication;
use App\Models\Housing\WaitingList;
use App\Models\Office;

class HousingApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $applications = HousingApplication::with(['assessor', 'office'])
                                         ->orderBy('created_at', 'desc')
                                         ->paginate(15);
                                         
        return view('housing.applications.index', compact('applications'));
    }

    public function create()
    {
        $offices = Office::where('is_active', true)->get();
        return view('housing.applications.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'applicant_email' => 'required|email',
            'applicant_phone' => 'required|string',
            'applicant_address' => 'required|string',
            'applicant_id_number' => 'required|string|unique:housing_applications',
            'family_size' => 'required|integer|min:1',
            'monthly_income' => 'required|numeric|min:0',
            'employment_status' => 'required|string',
            'preferred_area' => 'nullable|string',
            'housing_type_preference' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'office_id' => 'required|exists:offices,id',
        ]);

        $application = HousingApplication::create([
            'application_number' => $this->generateApplicationNumber(),
            'applicant_name' => $request->applicant_name,
            'applicant_email' => $request->applicant_email,
            'applicant_phone' => $request->applicant_phone,
            'applicant_address' => $request->applicant_address,
            'applicant_id_number' => $request->applicant_id_number,
            'family_size' => $request->family_size,
            'monthly_income' => $request->monthly_income,
            'employment_status' => $request->employment_status,
            'preferred_area' => $request->preferred_area,
            'housing_type_preference' => $request->housing_type_preference,
            'special_needs' => $request->special_needs,
            'application_date' => now(),
            'office_id' => $request->office_id,
        ]);

        return redirect()->route('housing.applications.show', $application)
                        ->with('success', 'Housing application submitted successfully.');
    }

    public function show(HousingApplication $application)
    {
        $application->load(['assessor', 'office', 'waitingListEntry', 'allocation']);
        return view('housing.applications.show', compact('application'));
    }

    public function edit(HousingApplication $application)
    {
        $offices = Office::where('is_active', true)->get();
        return view('housing.applications.edit', compact('application', 'offices'));
    }

    public function update(Request $request, HousingApplication $application)
    {
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'applicant_email' => 'required|email',
            'applicant_phone' => 'required|string',
            'applicant_address' => 'required|string',
            'family_size' => 'required|integer|min:1',
            'monthly_income' => 'required|numeric|min:0',
            'employment_status' => 'required|string',
            'preferred_area' => 'nullable|string',
            'housing_type_preference' => 'nullable|string',
            'special_needs' => 'nullable|string',
        ]);

        $application->update($request->only([
            'applicant_name', 'applicant_email', 'applicant_phone', 'applicant_address',
            'family_size', 'monthly_income', 'employment_status', 'preferred_area',
            'housing_type_preference', 'special_needs'
        ]));

        return redirect()->route('housing.applications.show', $application)
                        ->with('success', 'Application updated successfully.');
    }

    public function assess(Request $request, HousingApplication $application)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,on_waiting_list',
            'assessment_notes' => 'nullable|string',
        ]);

        $application->update([
            'status' => $request->status,
            'assessment_notes' => $request->assessment_notes,
            'assessed_by' => auth()->id(),
            'priority_score' => $application->calculatePriorityScore(),
        ]);

        // If approved and placed on waiting list, create waiting list entry
        if ($request->status === 'on_waiting_list') {
            WaitingList::create([
                'housing_application_id' => $application->id,
                'priority_score' => $application->priority_score,
                'date_added' => now(),
                'preferred_areas' => $application->preferred_area ? [$application->preferred_area] : [],
                'housing_type_preference' => $application->housing_type_preference,
                'special_requirements' => $application->special_needs,
                'status' => 'active',
            ]);

            // Recalculate all positions
            WaitingList::recalculateAllPositions();
        }

        return redirect()->route('housing.applications.show', $application)
                        ->with('success', 'Application assessed successfully.');
    }

    private function generateApplicationNumber()
    {
        $year = now()->year;
        $count = HousingApplication::whereYear('created_at', $year)->count() + 1;
        return "HA{$year}" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}