<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;

/**
 * Managing emails routes.
 */
//Route::group(['prefix' => 'emails'], function () {
Route::middleware(\App\Http\Middleware\Authenticate::class)->prefix('emails')->group(function () {
    /**
     * Create a new email.
     *
     * @param \Illuminate\Http\Request $request HTTP request with email data.
     */
    Route::post('/', [EmailController::class, 'store']);

    /**
     * Get a list of all non-deleted emails.
     */
    Route::get('/', [EmailController::class, 'index']);

    /**
     * Get a single email by ID.
     *
     * @param int $id ID of the email to be found.
     */
    Route::get('/{id}', [EmailController::class, 'show']);

    /**
     * Update an existing email by ID
     *
     * @param int $id ID of the email to be updated.
     * @param \Illuminate\Http\Request $request HTTP request with updated email data.
     */
    Route::put('/{id}', [EmailController::class, 'update']);

    /**
     * Delete an email by ID.
     *
     * @param int $id ID of the email to be deleted.
     */
    Route::delete('/{id}', [EmailController::class, 'destroy']);
    
});

/**
 * Auth routes to generate the JWT and access the routes of email.
 */
Route::group(['prefix' => ''], function() {
  Route::post('/newUser', [AuthController::class, 'register']);
  Route::post('/authenticate', [AuthController::class, 'authenticate']);
});