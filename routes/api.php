<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

//Login Backoffice
Route::post('/user_login_back', [LoginController::class, 'user_login_back']);

//Users Backoffice
Route::post('/table_user_back', [UserController::class, 'table_user_back']);

//Member Backoffice
Route::post('/table_member_back', [MemberController::class, 'table_member_back']);

//Product Web
Route::post('/get_product_home', [ProductController::class, 'get_product_home']);
//Product Backoffice
Route::post('/table_product_back', [ProductController::class, 'table_product_back']);
Route::post('/create_product_back', [ProductController::class, 'create_product_back']);

//Category Backoffice
Route::post('/get_category_back', [CategoryController::class, 'get_category_back']);
