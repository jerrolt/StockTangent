<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransactionController;
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
})->name('welcome');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


/** 
 * Standard CRUD routes for Organizations, Inventories, and Members (Users)
 */
Route::middleware('auth')->group(function () {
    
    /**
     * Routes for Inventories with soft delete functionality.
     */
    Route::patch('/inventories/{inventory}/restore', [InventoryController::class, 'restore'])->withTrashed()->name('inventories.restore');
    Route::resource('inventories', InventoryController::class)->except('show')->withTrashed(['edit', 'update']);
    Route::get('/inventories/{inventory}', [InventoryController::class, 'show'])->withTrashed()->name('inventories.show');
    
    Route::get('/inventories/{inventory}/transactions', [InventoryController::class, 'transactions'])->withTrashed()->name('inventories.transactions');
    Route::get('/inventories/{inventory}/items', [InventoryController::class, 'items'])->withTrashed()->name('inventories.items');

    /**
     * Transaction details.  
     * All transaction items related to the transaction.  
     * Show all products related to the items in the transaction.
    */
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    
    /**
     * Routes for Organizations with soft delete functionality.
     */
    Route::patch('/organizations/{organization}/restore', [OrganizationController::class, 'restore'])->withTrashed()->name('organizations.restore');
    Route::resource('organizations', OrganizationController::class)->except('show')->withTrashed(['edit', 'update']);
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->withTrashed()->name('organizations.show');
    
    /**
     * Routes for a User(Member) profile with soft delete functionality.
     */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/inventory', [ProfileController::class, 'inventory'])->name('profile.inventory');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    /**
     * Routes for Members (Users) with soft delete functionality.
     */
    Route::get('/members', [\App\Http\Controllers\MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [\App\Http\Controllers\MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [\App\Http\Controllers\MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{user}', [\App\Http\Controllers\MemberController::class, 'show'])->name('members.show');
    Route::get('/members/{user}/edit', [\App\Http\Controllers\MemberController::class, 'edit'])->name('members.edit');
    Route::patch('/members/{user}', [\App\Http\Controllers\MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{user}', [\App\Http\Controllers\MemberController::class, 'destroy'])->name('members.destroy');
});

require __DIR__.'/auth.php';
