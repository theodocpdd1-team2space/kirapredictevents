<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EstimationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\EstimationShareController;

use App\Http\Controllers\Settings\RuleController;
use App\Http\Controllers\Settings\BusinessController;
use App\Http\Controllers\Settings\LanguageController;
use App\Http\Controllers\Settings\ThemeController;
use App\Http\Controllers\Settings\CostController;
use App\Http\Controllers\Settings\StaffController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/**
 * PUBLIC (NO LOGIN)
 */
Route::get('/s/{token}', [EstimationShareController::class, 'show'])
    ->name('share.estimations.show');

/**
 * AUTH ONLY
 */
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');

    // Estimations
    Route::get('/estimations', [EstimationController::class, 'index'])->name('estimations.index');
    Route::delete('/estimations/bulk-delete', [EstimationController::class, 'bulkDelete'])->name('estimations.bulkDelete');

    Route::get('/estimations/{estimation}', [EstimationController::class, 'show'])->name('estimations.show');
    Route::get('/estimations/{estimation}/edit', [EstimationController::class, 'edit'])->name('estimations.edit');
    Route::patch('/estimations/{estimation}', [EstimationController::class, 'update'])->name('estimations.update');

    Route::patch('/estimations/{estimation}/status', [EstimationController::class, 'updateStatus'])->name('estimations.status');
    Route::patch('/estimations/{estimation}/accuracy', [EstimationController::class, 'updateAccuracy'])->name('estimations.accuracy');

    Route::get('/estimations/{estimation}/pdf', [EstimationController::class, 'pdf'])->name('estimations.pdf');
    Route::get('/estimations/{estimation}/wa', [EstimationController::class, 'wa'])->name('estimations.wa');

    Route::post('/estimations/{estimation}/share-token', [EstimationController::class, 'ensureShareToken'])
        ->name('estimations.shareToken');

    // Inventory
    Route::get('/inventories/import', [InventoryController::class, 'importForm'])->name('inventories.import.form');
    Route::post('/inventories/import', [InventoryController::class, 'import'])->name('inventories.import');
    Route::resource('inventories', InventoryController::class)->except(['show']);

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {

        Route::get('/', function () {
            return auth()->user()?->isOwner()
                ? redirect()->route('settings.rules.index')
                : redirect()->route('settings.language.edit');
        })->name('home');

        Route::get('/about', fn () => view('pages.settings.about'))->name('about');

        /**
         * Staff Management - Owner only
         * Protection juga nanti tetap kita taruh di StaffController.
         */
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{user}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::patch('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');

        // Cost & Rates - Owner only, protected inside controller
        Route::get('/cost', [CostController::class, 'edit'])->name('cost.edit');
        Route::patch('/cost', [CostController::class, 'update'])->name('cost.update');

        // Rules - Owner only, protected inside controller
        Route::get('/rules', [RuleController::class, 'index'])->name('rules.index');
        Route::get('/rules/create', [RuleController::class, 'create'])->name('rules.create');
        Route::post('/rules', [RuleController::class, 'store'])->name('rules.store');

        Route::delete('/rules/bulk-delete', [RuleController::class, 'bulkDelete'])->name('rules.bulkDelete');
        Route::get('/rules/import', [RuleController::class, 'importForm'])->name('rules.import.form');
        Route::post('/rules/import', [RuleController::class, 'import'])->name('rules.import');

        Route::get('/rules/{rule}/edit', [RuleController::class, 'edit'])->name('rules.edit');
        Route::put('/rules/{rule}', [RuleController::class, 'update'])->name('rules.update');
        Route::patch('/rules/{rule}/toggle', [RuleController::class, 'toggle'])->name('rules.toggle');
        Route::delete('/rules/{rule}', [RuleController::class, 'destroy'])->name('rules.destroy');

        // Business - Owner only, protected inside controller
        Route::get('/business', [BusinessController::class, 'edit'])->name('business.edit');
        Route::patch('/business', [BusinessController::class, 'update'])->name('business.update');

        // Language - Owner & Staff
        Route::get('/language', [LanguageController::class, 'edit'])->name('language.edit');
        Route::patch('/language', [LanguageController::class, 'update'])->name('language.update');

        // Theme - Owner & Staff
        Route::get('/theme', [ThemeController::class, 'edit'])->name('theme.edit');
        Route::patch('/theme', [ThemeController::class, 'update'])->name('theme.update');

        // Debug locale
        Route::get('/_locale', fn () => app()->getLocale())->name('locale');
    });

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';