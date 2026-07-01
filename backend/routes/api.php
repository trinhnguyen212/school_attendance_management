<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClasseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttendanceResultController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\SchoolOfThoughtController;
use App\Http\Controllers\Api\StudentStudyingController;
use App\Http\Controllers\Api\StudentCurrentLevelController;
use App\Http\Controllers\Api\LearningObjectiveController;
use App\Http\Controllers\Api\SemesterController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;


use Illuminate\Support\Facades\Auth;

Route::middleware('auth:sanctum')->get('/debug-user', function (Request $request) {
    return response()->json([
        'request_user' => $request->user(),
        'auth_user' => Auth::user(),
        'sanctum_user' => Auth::guard('sanctum')->user(),
        'current_token' => $request->user()?->currentAccessToken(),
    ]);
});


Route::get('/debug-auth-manager', function (Request $request) {

    $guard = Auth::guard('sanctum');

    return response()->json([
        'guardClass' => get_class($guard),
        'defaultDriver' => Auth::getDefaultDriver(),
        'check' => $guard->check(),
        'user' => $guard->user(),
    ]);
});

use Laravel\Sanctum\Sanctum;

Route::get('/debug-model', function () {
    return response()->json([
        'configured_model' => Sanctum::$personalAccessTokenModel,
    ]);
});


Route::get('/debug-auth-full', function (Request $request) {
    $plain = $request->bearerToken();

    $token = $plain
        ? PersonalAccessToken::findToken($plain)
        : null;

    return response()->json([
        'bearer' => $plain,
        'tokenFound' => $token !== null,
        'tokenModel' => $token ? get_class($token) : null,
        'tokenable' => $token?->tokenable?->email,
        'sanctumCheck' => Auth::guard('sanctum')->check(),
        'sanctumUser' => Auth::guard('sanctum')->user(),
        'requestUser' => $request->user('sanctum'),
    ]);
});


Route::middleware('auth:sanctum')->get('/whoami', function (Request $request) {
    return response()->json([
        'request_user' => $request->user(),
        'auth_user' => Auth::user(),
        'sanctum_user' => Auth::guard('sanctum')->user(),
        'default_guard' => Auth::getDefaultDriver(),
    ]);
});


Route::get('/debug-guards', function () {
    return response()->json([
        'defaultGuard' => Auth::getDefaultDriver(),
        'guards' => config('auth.guards'),
        'sanctumDriver' => get_class(Auth::guard('sanctum')),
    ]);
});


Route::get('/debug-request-user', function (Request $request) {
    return response()->json([
        'bearer' => $request->bearerToken(),
        'request_user_web' => $request->user(),
        'request_user_sanctum' => $request->user('sanctum'),
        'guard_web' => Auth::guard('web')->user(),
        'guard_sanctum' => Auth::guard('sanctum')->user(),
    ]);
});

Route::get('/debug-sanctum', function (Request $request) {

    return response()->json([
        'default_guard' => Auth::getDefaultDriver(),
        'sanctum_check' => Auth::guard('sanctum')->check(),
        'sanctum_user' => Auth::guard('sanctum')->user(),
        'request_user' => $request->user(),
        'request_user_sanctum' => $request->user('sanctum'),
    ]);
});



if (!defined('AUTH_MIDDLEWARES')) define('AUTH_MIDDLEWARES', ['auth:sanctum',]);



Route::prefix('/auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::middleware(AUTH_MIDDLEWARES)->group(function () {
        Route::get('/sessions', 'loginSessions');
        Route::delete('/sessions/{id}', 'revokeLoginSession');
        Route::post('/logout', 'logout');
        Route::post('/change-password', 'changePassword');
    });

    Route::post('/send-email-verification', 'sendEmailVerification')->middleware('throttle:3,1');
    Route::post('/verify-email', 'verifyEmail');

    Route::post('/send-password-reset-email', 'sendPasswordResetEmail')->middleware('throttle:3,1');
    Route::post('/verify-password-reset-code', 'verifyPasswordResetCode');
    Route::post('/reset-password', 'resetPassword');
});

Route::prefix('/users')->middleware(AUTH_MIDDLEWARES)
    ->controller(UserController::class)->group(function () {
        Route::get('/paginate', 'paginateUsers');
        Route::post('/import', 'importUsers');
        Route::get('/export', 'exportUsers');
        Route::get('/exportable', 'exportableFields');
        Route::get('/all-user', 'getAllUsers');
        Route::get('/search', 'searchUsers');
        Route::get('/complete', 'autocomplete');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/', 'destroy');
    });

Route::prefix('/dashboard')->middleware(AUTH_MIDDLEWARES)
    ->controller(DashboardController::class)->group(function () {
        Route::get('/', 'index');
    });



Route::prefix('/school-of-thoughts')->middleware(AUTH_MIDDLEWARES)
    ->controller(SchoolOfThoughtController::class)->group(function () {
        Route::get('/complete', 'autocomplete');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/', 'destroy');
    });
Route::prefix('/learning-objectives')->middleware(AUTH_MIDDLEWARES)
    ->controller(LearningObjectiveController::class)->group(function () {
        Route::get('/complete', 'autocomplete');

    });
Route::prefix('/student-studyings')->middleware(AUTH_MIDDLEWARES)
    ->controller(StudentStudyingController::class)->group(function () {
        Route::get('/complete', 'autocomplete');

    });
Route::prefix('/student-current-levels')->middleware(AUTH_MIDDLEWARES)
    ->controller(StudentCurrentLevelController::class)->group(function () {
        Route::get('/complete', 'autocomplete');

    });

Route::prefix('/branches')->middleware(AUTH_MIDDLEWARES)
    ->controller(BranchController::class)->group(function () {
        Route::get('/complete', 'autocomplete');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/', 'destroy');
    });

Route::prefix('/courses')->middleware(AUTH_MIDDLEWARES)
    ->controller(CourseController::class)->group(function () {
        Route::get('/complete', 'autocomplete');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::get('/', 'index');
        Route::post('/', 'store');
    });



Route::prefix('/role-permissions')->middleware(AUTH_MIDDLEWARES)
    ->controller(RolePermissionController::class)->group(function () {
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::get('/', 'index');
    });



Route::prefix('/semesters')->middleware(AUTH_MIDDLEWARES)
    ->controller(SemesterController::class)->group(function () {
        Route::get('/complete', 'autocomplete');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::get('/', 'index');
        Route::post('/', 'store');
    });

Route::prefix('/classes')->middleware(AUTH_MIDDLEWARES)
    ->controller(ClasseController::class)->group(function () {
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::put('/{id}/students', 'updateStudents');
        Route::get('/', 'index');
        Route::post('/', 'store');
    });



    Route::prefix('/attendances')->middleware(AUTH_MIDDLEWARES)
    ->controller(AttendanceController::class)->group(function () {
        // Route::middleware(App\Http\Middleware\RevokeOtherTokens::class)
        //     ->group(function () {
        //         Route::post('/{id}/sync-cache', 'syncCache');
        //         Route::get('/{id}/take', 'take');
        //         Route::post('/{id}/submit', 'submit');
        //     });
        Route::get('/{id}/results', 'getResults');
      //  Route::put('/{id}/results', 'updateResults');

       // Route::get('/{id}/export-results', 'exportResults');
       // Route::post('/{id}/status', 'updateStatus');


        Route::get('/{id}', 'show');
       Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::get('/', 'index');
        Route::post('/', 'store');
    });




//
Route::prefix('/attendance-results')->middleware(AUTH_MIDDLEWARES)
    ->controller(AttendanceResultController::class)->group(function () {
      //  Route::get('/{id}', 'show');
//
        Route::put('/{id}/attendance_status', 'updateAttendanceStatus');
  //      
     //   Route::post('/{id}/cancel', 'cancel');
      //  Route::delete('/{id}', 'destroy');
     //   Route::put('/{id}', 'update');
       // Route::get('/user/{id}', 'getByUser');
        // Add the new route for getting attendance result
     //   Route::get('/{id}/attendance_status', 'getAttendanceStatus');  // New route for getting attendance_status


    });
//
