<?php

namespace App\Http\Controllers;

use App\Services\CongressionalDistrictService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function __construct(
        private CongressionalDistrictService $districtService
    ) {}

    /**
     * Show the ZIP code onboarding page.
     */
    public function showZipCode()
    {
        // Redirect to dashboard if already completed
        if (auth()->user()->hasCompletedProfile()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/ZipCode');
    }

    /**
     * Store the user's ZIP code and derive congressional district.
     */
    public function storeZipCode(Request $request)
    {
        $validated = $request->validate([
            'zip_code' => ['required', 'regex:/^\d{5}$/', 'string'],
        ]);

        $user = $request->user();

        // Validate ZIP code format
        if (!$this->districtService->isValidZipCode($validated['zip_code'])) {
            return back()->withErrors([
                'zip_code' => 'Please enter a valid 5-digit U.S. ZIP code.',
            ]);
        }

        // Lookup congressional district
        $district = $this->districtService->lookupDistrict($validated['zip_code']);

        // Update user
        $user->update([
            'zip_code' => $validated['zip_code'],
            'congressional_district' => $district,
            'zip_code_verified_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Profile completed! Welcome to the platform.');
    }
}
