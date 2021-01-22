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
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HeaderController;

//Login
Route::post('/member_login', [LoginController::class, 'member_login']);
//Login Backoffice
Route::post('/user_login_back', [LoginController::class, 'user_login_back']);
//Product Web
Route::post('/get_product_home', [ProductController::class, 'get_product_home']);
Route::post('/get_product_detail', [ProductController::class, 'get_product_detail']);
//Category
Route::post('/get_category', [CategoryController::class, 'get_category']);
//Category
Route::post('/get_header', [HeaderController::class, 'get_header']);
//Member
Route::post('/create_member', [MemberController::class, 'create_member']);

Route::group(['middleware' => 'checkjwt'], function () {

    //Users Backoffice
    Route::post('/table_user_back', [UserController::class, 'table_user_back']);

    //Member Web
    Route::post('/get_memder_account', [MemberController::class, 'get_memder_account']);
    Route::post('/update_memder_account', [MemberController::class, 'update_memder_account']);

    //Member Backoffice
    Route::post('/table_member_back', [MemberController::class, 'table_member_back']);

    //Product Backoffice
    Route::post('/table_product_back', [ProductController::class, 'table_product_back']);
    Route::post('/create_product_back', [ProductController::class, 'create_product_back']);

    //Category Backoffice
    Route::post('/get_category_back', [CategoryController::class, 'get_category_back']);

    //Address
    Route::post('/get_address', [AddressController::class, 'get_address']);
    Route::post('/update_address', [AddressController::class, 'update_address']);

    //Cart
    Route::post('/add_cart', [CartController::class, 'add_cart']);

    //Order
    //Route::post('/create_order', [OrderController::class, 'create_order']);
});
