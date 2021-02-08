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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\FooterController;

//Login
Route::post('/member_login', [LoginController::class, 'member_login']);
//Login Backoffice
Route::post('/user_login_back', [LoginController::class, 'user_login_back']);
//Product Web
Route::post('/get_product_home', [ProductController::class, 'get_product_home']);
Route::post('/get_product_detail', [ProductController::class, 'get_product_detail']);
Route::post('/get_data_page', [ProductController::class, 'get_data_page']);
Route::post('/get_product_page', [ProductController::class, 'get_product_page']);
//Category
Route::post('/get_category', [CategoryController::class, 'get_category']);
//Header
Route::post('/get_header', [HeaderController::class, 'get_header']);
//Footer
Route::post('/get_footer', [FooterController::class, 'get_footer']);
//Member
Route::post('/create_member', [MemberController::class, 'create_member']);

Route::group(['middleware' => 'checkjwt'], function () {
    //Header
    Route::post('/get_header_login', [HeaderController::class, 'get_header_login']);
    Route::post('/delete_cart', [HeaderController::class, 'delete_cart']);

    //Users Backoffice
    Route::post('/table_user_back', [UserController::class, 'table_user_back']);

    //Member Web
    Route::post('/get_memder_account', [MemberController::class, 'get_memder_account']);
    Route::post('/update_memder_account', [MemberController::class, 'update_memder_account']);
    Route::post('/reset_password', [MemberController::class, 'reset_password']);

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

    //Checkout
    Route::post('/add_cart', [CheckoutController::class, 'add_cart']);
    Route::post('/get_checkout', [CheckoutController::class, 'get_checkout']);
    Route::post('/get_qrcode', [CheckoutController::class, 'get_qrcode']);
    Route::post('/create_order', [CheckoutController::class, 'create_order']);

    //Order
    Route::post('/get_order_list', [OrderController::class, 'get_order_list']);
});
