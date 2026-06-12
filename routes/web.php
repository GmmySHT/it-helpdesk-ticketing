<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketResponseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Main route file for the ticketing system.
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (controller selects view by role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== PROFILE ROUTES ====================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ==================== TICKET ROUTES (All authenticated users) ====================
    Route::resource('tickets', TicketController::class);

    // Ticket responses (store reply)
    Route::post('tickets/{ticket}/responses', [TicketResponseController::class, 'store'])
        ->name('tickets.responses.store');

    // Header search
    Route::get('/tickets/search', [TicketController::class, 'search'])->name('tickets.search');

    // ==================== NOTIFICATION ROUTES ====================
    Route::get('/notifications', [DashboardController::class, 'notificationsPage'])
        ->name('notifications.page');

    Route::prefix('api')->name('api.')->group(function() {
        Route::get('/notifications', [DashboardController::class, 'getNotifications'])
            ->name('notifications.get');
        Route::get('/notifications/count', [DashboardController::class, 'getNotificationCount'])
            ->name('notifications.count');
        Route::post('/notifications/{id}/read', [DashboardController::class, 'markAsRead'])
            ->name('notifications.read');
        Route::post('/notifications/read-all', [DashboardController::class, 'markAllAsRead'])
            ->name('notifications.read.all');
    });

    // ==================== REPORT ROUTES ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics');
        Route::get('/laporan/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
    });

    // ==================== ADMIN ONLY ROUTES ====================
    Route::middleware(['role:admin'])->group(function () {

        // User Management (Full CRUD + verification)
        Route::resource('users', UserController::class);

        // Email verification for users
        Route::prefix('users/{user}')->name('users.')->group(function () {
            Route::post('/verify-email', [UserController::class, 'verifyEmail'])->name('verify-email');
            Route::post('/unverify-email', [UserController::class, 'unverifyEmail'])->name('unverify-email');
        });

        // Category Management
        Route::resource('categories', CategoryController::class);

        // Admin ticket management
        Route::prefix('tickets/{ticket}')->name('tickets.')->group(function () {
            Route::post('/assign', [TicketController::class, 'assign'])->name('assign');
            Route::post('/status', [TicketController::class, 'updateStatus'])->name('status');
            Route::post('/reopen', [TicketController::class, 'reopen'])->name('reopen');
        });

        // Admin can take any ticket
        Route::post('/tickets/{ticket}/take', [TicketController::class, 'take'])->name('tickets.take');
    });

    // ==================== IT STAFF ONLY ROUTES ====================
    Route::middleware(['role:it_staff,it'])->prefix('it')->name('it.')->group(function () {

        // View all tickets (read-only)
        Route::get('/tickets', [TicketController::class, 'itAllTickets'])->name('tickets.all');

        // My assigned tickets (can manage)
        Route::get('/my-tickets', [TicketController::class, 'itMyTickets'])->name('tickets.my');

        // Ticket detail
        Route::get('/tickets/{ticket}', [TicketController::class, 'itShow'])->name('tickets.show');

        // IT can update status for tickets assigned to them
        Route::post('/tickets/{ticket}/status', [TicketController::class, 'itUpdateStatus'])->name('tickets.status');

        // IT can reopen tickets assigned to them
        Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'itReopen'])->name('tickets.reopen');

        // IT can take (self-assign) available tickets
        Route::post('/tickets/{ticket}/take', [TicketController::class, 'itTake'])->name('tickets.take');
    });

    // ==================== IT STAFF & USER COMMON ROUTES ====================
    Route::middleware(['role:it_staff,it,user'])->group(function () {
        // User/IT can view their own notifications list
        Route::get('/tickets/notifications', [TicketController::class, 'notificationPage'])
            ->name('tickets.notifications');
    });

});

// ==================== AUTH ROUTES (Breeze/Jetstream) ====================
require __DIR__.'/auth.php';
