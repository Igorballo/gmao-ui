<?php

use App\Http\Controllers\Api\AccueilController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PanneController;
use App\Http\Controllers\Api\RapportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// --- Authentification (public) ---
Route::post('/login', [AuthController::class, 'login']);

// --- Routes protégées (token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'update']);
    Route::post('/me/photo', [AuthController::class, 'uploadPhoto']);
    Route::post('/me/password', [AuthController::class, 'updatePassword']);
    Route::patch('/me/notifications', [AuthController::class, 'updateNotifications']);
    Route::post('/me/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/me/device-token', [DeviceTokenController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    Route::get('/accueil', [AccueilController::class, 'index']);

    Route::get('/machines', [MachineController::class, 'index']);

    Route::get('/utilisateurs', [UserController::class, 'index']);

    Route::get('/pannes', [PanneController::class, 'index']);
    Route::post('/pannes', [PanneController::class, 'store']);
    Route::get('/pannes/{panne}', [PanneController::class, 'show']);
    Route::post('/pannes/{panne}/deleguer', [PanneController::class, 'deleguer']);

    // Suivi d'intervention
    Route::post('/pannes/{panne}/intervention/demarrer', [InterventionController::class, 'demarrer']);
    Route::post('/interventions/{intervention}', [InterventionController::class, 'update']);
    Route::post('/interventions/{intervention}/photos', [InterventionController::class, 'ajouterPhoto']);
    Route::post('/interventions/{intervention}/cloturer', [InterventionController::class, 'cloturer']);

    // Rapports
    Route::get('/rapports', [RapportController::class, 'index']);
    Route::post('/rapports/generer', [RapportController::class, 'generer']);
    Route::post('/rapports/generer-periode', [RapportController::class, 'genererPeriode']);
    Route::get('/rapports/{rapport}/telecharger', [RapportController::class, 'telecharger']);
});
