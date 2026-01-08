<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\BillFollowerController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\StanceController;
use App\Services\LocalBillService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/community-guidelines', function () {
    $guidelines = file_get_contents(resource_path('markdown/guidelines.md'));
    return Inertia::render('CommunityGuidelines', [
        'guidelines' => \Illuminate\Support\Str::markdown($guidelines),
    ]);
})->name('community-guidelines');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Onboarding routes (excluded from profile.complete check)
    Route::get('/onboarding/zip-code', [OnboardingController::class, 'showZipCode'])
        ->name('onboarding.zip-code');
    Route::post('/onboarding/zip-code', [OnboardingController::class, 'storeZipCode'])
        ->name('onboarding.zip-code.store');

    // Protected routes requiring complete profile
    Route::middleware(['profile.complete'])->group(function () {
        Route::get('/dashboard', function (LocalBillService $localBillService) {
            $user = auth()->user();

            return Inertia::render('Dashboard', [
                'localBills' => $localBillService->getBillsForUser($user, 5)->map(fn($bill) => [
                    'id' => $bill->id,
                    'identifier' => $bill->identifier,
                    'title' => $bill->title,
                    'summary' => $bill->summary,
                    'status_display' => ucwords(str_replace('_', ' ', $bill->status)),
                    'chamber' => $bill->chamber,
                    'sponsor' => $bill->sponsor() ? [
                        'name' => $bill->sponsor()->name,
                        'party' => $bill->sponsor()->party,
                    ] : null,
                ]),
                'nationalBills' => $localBillService->getNationalBills(5)->map(fn($bill) => [
                    'id' => $bill->id,
                    'identifier' => $bill->identifier,
                    'title' => $bill->title,
                    'summary' => $bill->summary,
                    'status_display' => ucwords(str_replace('_', ' ', $bill->status)),
                    'chamber' => $bill->chamber,
                    'sponsor' => $bill->sponsor() ? [
                        'name' => $bill->sponsor()->name,
                        'party' => $bill->sponsor()->party,
                    ] : null,
                ]),
                'userDistrict' => $user->congressional_district,
            ]);
        })->name('dashboard');

        // Bill routes
        Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');

        // Bill follow routes
        Route::get('/following', [BillFollowerController::class, 'index'])->name('bills.following');
        Route::post('/bills/{bill}/follow', [BillFollowerController::class, 'store'])->name('bills.follow');
        Route::delete('/bills/{bill}/follow', [BillFollowerController::class, 'destroy'])->name('bills.unfollow');
        Route::put('/bills/{bill}/follow', [BillFollowerController::class, 'update'])->name('bills.follow.update');

        // Stance routes
        Route::post('/bills/{bill}/stances', [StanceController::class, 'store'])->name('bills.stances.store');
        Route::delete('/bills/{bill}/stances', [StanceController::class, 'destroy'])->name('bills.stances.destroy');

        // Discussion routes
        Route::get('/bills/{bill}/discussions/{section}', [DiscussionController::class, 'getOrCreate'])->name('bills.discussions.get');
        Route::post('/bills/{bill}/discussions/{discussion}/comments', [DiscussionController::class, 'storeComment'])->name('bills.discussions.comments.store');
        Route::post('/comments/{comment}/helpful', [DiscussionController::class, 'markHelpful'])->name('comments.helpful');
        Route::post('/comments/{comment}/flag', [DiscussionController::class, 'flagComment'])->name('comments.flag');
        Route::delete('/comments/{comment}', [DiscussionController::class, 'destroyComment'])->name('comments.destroy');
    });
});
