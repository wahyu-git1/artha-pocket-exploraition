<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
| Semua route menggunakan prefix /api/v1 dan middleware auth:sanctum
| kecuali endpoint autentikasi (register, login, refresh).
|
| Fase 1C  — Auth & Profil
| Fase 1D  — Income & Receipts
| Fase 1E  — Category
| Fase 1F  — Expense
| Fase 1G  — Smart Entry
| Fase 1H  — Summary & Reports
| Fase 1I  — Sync
| Fase 2   — Savings Goals
| Fase 3   — Emergency Fund
| Fase 4   — Allocations & Investments
| Fase 2-4 — Notifications & Devices
|
*/

Route::prefix('v1')->group(function () {

    // -------------------------------------------------------------------------
    // FASE 1C — Auth (public)
    // -------------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('register', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'register']);
        Route::post('login',    [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('refresh',  [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'refresh']);
    });

    // -------------------------------------------------------------------------
    // Protected routes — requires sanctum token
    // -------------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // --- Auth ---
        Route::post('auth/logout', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout']);

        // --- User Profile ---
        Route::prefix('users/me')->group(function () {
            Route::get('/',    [\App\Http\Controllers\Api\V1\UserController::class, 'me']);
            Route::patch('/',  [\App\Http\Controllers\Api\V1\UserController::class, 'update']);
            Route::delete('/', [\App\Http\Controllers\Api\V1\UserController::class, 'destroy']);
        });

        // -----------------------------------------------------------------------
        // FASE 1D — Income & Receipts
        // -----------------------------------------------------------------------
        Route::prefix('incomes')->group(function () {
            Route::get('/',    [\App\Http\Controllers\Api\V1\IncomeController::class, 'index']);
            Route::post('/',   [\App\Http\Controllers\Api\V1\IncomeController::class, 'store']);
            Route::get('/{income}',    [\App\Http\Controllers\Api\V1\IncomeController::class, 'show']);
            Route::patch('/{income}',  [\App\Http\Controllers\Api\V1\IncomeController::class, 'update']);
            Route::delete('/{income}', [\App\Http\Controllers\Api\V1\IncomeController::class, 'destroy']);

            // Receipts (nested)
            Route::prefix('/{income}/receipts')->group(function () {
                Route::get('/',          [\App\Http\Controllers\Api\V1\IncomeReceiptController::class, 'index']);
                Route::post('/',         [\App\Http\Controllers\Api\V1\IncomeReceiptController::class, 'store']);
                Route::patch('/{receipt}',  [\App\Http\Controllers\Api\V1\IncomeReceiptController::class, 'update']);
                Route::delete('/{receipt}', [\App\Http\Controllers\Api\V1\IncomeReceiptController::class, 'destroy']);
            });
        });

        // -----------------------------------------------------------------------
        // FASE 1E — Categories
        // -----------------------------------------------------------------------
        Route::apiResource('categories', \App\Http\Controllers\Api\V1\CategoryController::class);

        // -----------------------------------------------------------------------
        // FASE 1F — Expenses
        // -----------------------------------------------------------------------
        Route::prefix('expenses')->group(function () {
            Route::get('/',         [\App\Http\Controllers\Api\V1\ExpenseController::class, 'index']);
            Route::post('/',        [\App\Http\Controllers\Api\V1\ExpenseController::class, 'store']);
            Route::post('/bulk',    [\App\Http\Controllers\Api\V1\ExpenseController::class, 'bulk']);
            Route::get('/{expense}',    [\App\Http\Controllers\Api\V1\ExpenseController::class, 'show']);
            Route::patch('/{expense}',  [\App\Http\Controllers\Api\V1\ExpenseController::class, 'update']);
            Route::delete('/{expense}', [\App\Http\Controllers\Api\V1\ExpenseController::class, 'destroy']);
        });

        // -----------------------------------------------------------------------
        // FASE 1G — Smart Entry
        // -----------------------------------------------------------------------
        Route::prefix('smart-entry')->group(function () {
            Route::post('/parse',    [\App\Http\Controllers\Api\V1\SmartEntryController::class, 'parse']);
            Route::post('/feedback', [\App\Http\Controllers\Api\V1\SmartEntryController::class, 'feedback']);
        });

        // -----------------------------------------------------------------------
        // FASE 1H — Summary & Reports
        // -----------------------------------------------------------------------
        Route::get('/summary', [\App\Http\Controllers\Api\V1\SummaryController::class, 'index']);
        Route::prefix('reports')->group(function () {
            Route::get('/categories', [\App\Http\Controllers\Api\V1\ReportController::class, 'categories']);
            Route::get('/monthly',    [\App\Http\Controllers\Api\V1\ReportController::class, 'monthly']);
        });
        Route::get('/exports/transactions', [\App\Http\Controllers\Api\V1\ExportController::class, 'transactions']);

        // -----------------------------------------------------------------------
        // FASE 1I — Sync
        // -----------------------------------------------------------------------
        Route::prefix('sync')->group(function () {
            Route::post('/push', [\App\Http\Controllers\Api\V1\SyncController::class, 'push']);
            Route::get('/pull',  [\App\Http\Controllers\Api\V1\SyncController::class, 'pull']);
        });

        // -----------------------------------------------------------------------
        // FASE 2 — Savings Goals
        // -----------------------------------------------------------------------
        Route::prefix('savings-goals')->group(function () {
            Route::post('/simulate',  [\App\Http\Controllers\Api\V1\SavingsGoalController::class, 'simulate']);
            Route::get('/',           [\App\Http\Controllers\Api\V1\SavingsGoalController::class, 'index']);
            Route::post('/',          [\App\Http\Controllers\Api\V1\SavingsGoalController::class, 'store']);
            Route::get('/{goal}',     [\App\Http\Controllers\Api\V1\SavingsGoalController::class, 'show']);
            Route::patch('/{goal}',   [\App\Http\Controllers\Api\V1\SavingsGoalController::class, 'update']);
            Route::delete('/{goal}',  [\App\Http\Controllers\Api\V1\SavingsGoalController::class, 'destroy']);

            Route::prefix('/{goal}/deposits')->group(function () {
                Route::get('/',  [\App\Http\Controllers\Api\V1\GoalDepositController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\Api\V1\GoalDepositController::class, 'store']);
            });
        });

        // -----------------------------------------------------------------------
        // FASE 3 — Emergency Fund
        // -----------------------------------------------------------------------
        Route::prefix('emergency-fund')->group(function () {
            Route::post('/recommendation', [\App\Http\Controllers\Api\V1\EmergencyFundController::class, 'recommendation']);
            Route::post('/',               [\App\Http\Controllers\Api\V1\EmergencyFundController::class, 'store']);
            Route::get('/',                [\App\Http\Controllers\Api\V1\EmergencyFundController::class, 'show']);
            Route::patch('/',              [\App\Http\Controllers\Api\V1\EmergencyFundController::class, 'update']);
            Route::post('/deposits',       [\App\Http\Controllers\Api\V1\EmergencyFundController::class, 'deposit']);
            Route::post('/withdrawals',    [\App\Http\Controllers\Api\V1\EmergencyFundController::class, 'withdrawal']);
            Route::get('/transactions',    [\App\Http\Controllers\Api\V1\EmergencyFundController::class, 'transactions']);
        });

        // -----------------------------------------------------------------------
        // FASE 4 — Allocations & Investments
        // -----------------------------------------------------------------------
        Route::prefix('allocations')->group(function () {
            Route::get('/templates',        [\App\Http\Controllers\Api\V1\AllocationController::class, 'templates']);
            Route::get('/recommendation',   [\App\Http\Controllers\Api\V1\AllocationController::class, 'recommendation']);
            Route::get('/summary',          [\App\Http\Controllers\Api\V1\AllocationController::class, 'summary']);
            Route::get('/category-mapping', [\App\Http\Controllers\Api\V1\AllocationController::class, 'categoryMapping']);
            Route::put('/category-mapping', [\App\Http\Controllers\Api\V1\AllocationController::class, 'updateCategoryMapping']);
            Route::get('/',                 [\App\Http\Controllers\Api\V1\AllocationController::class, 'index']);
            Route::put('/',                 [\App\Http\Controllers\Api\V1\AllocationController::class, 'update']);
        });

        Route::apiResource('investments', \App\Http\Controllers\Api\V1\InvestmentController::class)
            ->except(['show']);

        // -----------------------------------------------------------------------
        // FASE 2-4 — Devices & Notifications
        // -----------------------------------------------------------------------
        Route::post('devices',          [\App\Http\Controllers\Api\V1\DeviceController::class, 'store']);
        Route::delete('devices/{device}', [\App\Http\Controllers\Api\V1\DeviceController::class, 'destroy']);

        Route::prefix('notifications')->group(function () {
            Route::get('/',              [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
            Route::patch('/{notif}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markRead']);
            Route::post('/read-all',     [\App\Http\Controllers\Api\V1\NotificationController::class, 'readAll']);
        });

    }); // end auth:sanctum

}); // end v1
