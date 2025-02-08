<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{PermissionController, ProjectController, IssuesController,AuthController, WebhookController, DashboardController, OpenProjectController};
use App\Http\Middleware\ApiAuthMiddleware;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/webhooks/sentry', [WebhookController::class, 'handleWebhook']);

Route::middleware([ApiAuthMiddleware::class])->group(function () {

    /////////////////////////////////////////// USER /////////////////////////////////////////////
    Route::middleware(['permission:show user'])->group(function () {
        Route::get('/users', [AuthController::class, 'listAllUsers']);
        Route::get('/user/{id}', [AuthController::class, 'getUserById']);
    });
    Route::middleware(['permission:create user'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
    });
    Route::middleware(['permission:edit user'])->group(function () {
        Route::put('/user/{id}', [AuthController::class, 'editUserById']);
    });
    Route::middleware(['permission:delete user'])->group(function () {
        Route::delete('/user/{id}', [AuthController::class, 'deleteUserById']);
    });  
    /////////////////////////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////// PERMISSION ///////////////////////////////////////////
    Route::middleware(['permission:show permission'])->group(function () {
        Route::get('/permission', [PermissionController::class, 'getAllPermission']);
        Route::get('/permission/{id}', [PermissionController::class, 'getPermissionById']);
    });
    Route::middleware(['permission:create permission'])->group(function () {
        Route::post('/permission', [PermissionController::class, 'createPermission']);
    });
    Route::middleware(['permission:edit permission'])->group(function () {
        Route::put('/permission/{id}', [PermissionController::class, 'editPermission']);
    });
    Route::middleware(['permission:delete permission'])->group(function () {
        Route::delete('/permission/{id}', [PermissionController::class, 'deletePermission']);
    });
    ///////////////////////////////////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////// ROLE ////////////////////////////////////////////
    Route::middleware(['permission:show role'])->group(function () {
        Route::get('/role', [PermissionController::class, 'getAllRole']);
        Route::get('/role/{id}', [PermissionController::class, 'getRoleById']);
    });
    Route::middleware(['permission:create role'])->group(function () {
        Route::post('/role', [PermissionController::class, 'createRole']);
    });
    Route::middleware(['permission:edit role'])->group(function () {
        Route::put('/role/{id}', [PermissionController::class, 'editRole']);
    });
    Route::middleware(['permission:delete role'])->group(function () {
        Route::delete('/role/{id}', [PermissionController::class, 'deleteRole']);
    });
    ///////////////////////////////////////////////////////////////////////////////////////////////

    ///////////////////////////////////////// ISSUE SENTRY ////////////////////////////////////////
    Route::middleware(['permission:update issue'])->group(function () {
        Route::put('/issues/{id}/status', [IssuesController::class, 'updateStatus']);
    });
    Route::middleware(['permission:assign issue'])->group(function () {
        Route::post('/issues/{id}/post', [OpenProjectController::class, 'postIssue']);
        Route::patch('/work-package/{id}', [OpenProjectController::class, 'updateWorkPackage']);
    });
    ///////////////////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////// OPEN PROJECT /////////////////////////////////////////
    Route::middleware(['permission:assign project'])->group(function () {
        Route::get('/assignees', [OpenProjectController::class, 'getAssignees']);
        Route::get('/openproject', [OpenProjectController::class, 'getOpenProjects']);
        Route::get('/openproject/projects', [OpenProjectController::class, 'getProjects']);
        Route::post('/root-project/{slug}', [OpenProjectController::class, 'saveProjectData']);
    });
    ///////////////////////////////////////////////////////////////////////////////////////////////

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    
    Route::get('/dashboard/issues/{type}/{slug?}', [DashboardController::class, 'countIssues']);
    Route::get('/projects', [ProjectController::class, 'viewProjects']);
    Route::get('/projects/{slug}/issues', [ProjectController::class, 'showIssuesByProject']);

    Route::get('/issues', [IssuesController::class, 'showAllIssue']);
    Route::get('/issues/{id}', [IssuesController::class, 'showIssueId']);

    Route::get('/issues/{id}/trace', [IssuesController::class, 'showStackTrace']);
    Route::get('/issues/{id}/events', [IssuesController::class, 'showEvents']);

});
