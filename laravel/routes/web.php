<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserPreferenceController;

Route::get('/', function () {
    return inertia('Home');
});

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.post.login');
});


Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update')->middleware('throttle:3,1');
});

Route::middleware(['auth', 'active', 'session.timeout'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/preferences/theme', [UserPreferenceController::class, 'update'])->name('preferences.theme');
    Route::put('/preferences/timezone', [UserPreferenceController::class, 'updateTimeZone'])->name('preferences.timezone');

    Route::post('pages/bulk-action', [PageController::class, 'bulkAction'])->name('pages.bulkAction');
    Route::resource('pages', PageController::class)->except('show');
    Route::post('pages/{page}/restore', [PageController::class, 'restore'])->name('pages.restore')->withTrashed();
    Route::delete('pages/{page}/force-delete', [PageController::class, 'forceDelete'])->name('pages.forceDelete')->withTrashed();
    Route::post('pages/{page}/autosave', [PageController::class, 'autosave'])->name('pages.autosave');
    Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');

    Route::post('posts/bulk-action', [PostController::class, 'bulkAction'])->name('posts.bulkAction');
    Route::resource('posts', PostController::class)->except('show');
    Route::post('posts/{post}/restore', [PostController::class, 'restore'])->name('posts.restore')->withTrashed();
    Route::delete('posts/{post}/force-delete', [PostController::class, 'forceDelete'])->name('posts.forceDelete')->withTrashed();
    Route::post('posts/{post}/autosave', [PostController::class, 'autosave'])->name('posts.autosave');
    Route::post('posts/{post}/duplicate', [PostController::class, 'duplicate'])->name('posts.duplicate');

    Route::post('categories/bulk-action', [CategoryController::class, 'bulkAction'])->name('categories.bulkAction');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])->name('categories.restore')->withTrashed();
    Route::delete('categories/{category}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.forceDelete')->withTrashed();

    Route::post('tags/bulk-action', [TagController::class, 'bulkAction'])->name('tags.bulkAction');
    Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);
});
