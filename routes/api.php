<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthenticationController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;

use App\Http\Resources\UserResource;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

//Route::post('user/signup', [AuthenticationController::class, 'userSignUp']);
Route::post('user/login', [AuthenticationController::class, 'userLogin']);

Route::middleware('auth:api')->group(function () 
{
    Route::resource('user', UserController::class);
    Route::resource('role', RoleController::class);

    //Roles and Permissions
    Route::get('user/role/{id}/{slug}', [AuthenticationController::class, 'hasRole']);
    Route::get('user/permission/assign/{id}/{slug}', [AuthenticationController::class, 'givePermissionsTo']);
    Route::get('user/permission/check/{id}/{slug}', [AuthenticationController::class, 'hasPermission']);
});
