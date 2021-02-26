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
use App\Http\Controllers\FooterController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\CancelController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NewController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SettingController;

//Login
Route::post('/member_login', [LoginController::class, 'member_login']);
Route::post('/member_login_social', [LoginController::class, 'member_login_social']);
//Login Backoffice
//Login Backoffice
Route::post('/user_login_back', [LoginController::class, 'user_login_back']);
//Product Web
Route::post('/get_home', [ProductController::class, 'get_home']);
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
//Blog
Route::post('/get_blog_page', [BlogController::class, 'get_blog_page']);
Route::post('/get_blog_date', [BlogController::class, 'get_blog_date']);
Route::post('/get_blog_detail', [BlogController::class, 'get_blog_detail']);
//News
Route::post('/get_news_page', [NewController::class, 'get_news_page']);
Route::post('/get_news_date', [NewController::class, 'get_news_date']);
Route::post('/get_news_detail', [NewController::class, 'get_news_detail']);

Route::group(['middleware' => 'checkjwt'], function () {
    //Header
    Route::post('/get_header_login', [HeaderController::class, 'get_header_login']);
    Route::post('/delete_cart', [HeaderController::class, 'delete_cart']);

    //Users Backoffice
    Route::post('/table_user_back', [UserController::class, 'table_user_back']);
    Route::post('/create_user_back', [UserController::class, 'create_user_back']);
    Route::post('/get_user_detail_back', [UserController::class, 'get_user_detail_back']);
    Route::post('/update_user_back', [UserController::class, 'update_user_back']);
    Route::post('/get_user_permission_back', [UserController::class, 'get_user_permission_back']);
    Route::post('/get_alert_back', [UserController::class, 'get_alert_back']);

    //Member Web
    Route::post('/get_memder_account', [MemberController::class, 'get_memder_account']);
    Route::post('/update_memder_account', [MemberController::class, 'update_memder_account']);
    Route::post('/reset_password', [MemberController::class, 'reset_password']);

    //Member Backoffice
    Route::post('/table_member_back', [MemberController::class, 'table_member_back']);
    Route::post('/create_member_back', [MemberController::class, 'create_member_back']);
    Route::post('/update_memder_back', [MemberController::class, 'update_memder_back']);
    Route::post('/update_password_back', [MemberController::class, 'update_password_back']);

    //Product Backoffice
    Route::post('/table_product_back', [ProductController::class, 'table_product_back']);
    Route::post('/create_product_back', [ProductController::class, 'create_product_back']);
    Route::post('/get_product_detail_back', [ProductController::class, 'get_product_detail_back']);
    Route::post('/update_product_back', [ProductController::class, 'update_product_back']);
    Route::post('/update_product_active_back', [ProductController::class, 'update_product_active_back']);
    Route::post('/update_product_noactive_back', [ProductController::class, 'update_product_noactive_back']);

    //Category Backoffice
    Route::post('/get_category_back', [CategoryController::class, 'get_category_back']);
    Route::post('/table_category_back', [CategoryController::class, 'table_category_back']);
    Route::post('/create_category_back', [CategoryController::class, 'create_category_back']);
    Route::post('/update_category_back', [CategoryController::class, 'update_category_back']);
    Route::post('/delete_category_back', [CategoryController::class, 'delete_category_back']);

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
    //Order Backoffice
    Route::post('/table_order_back', [OrderController::class, 'table_order_back']);
    Route::post('/get_order_detail_back', [OrderController::class, 'get_order_detail_back']);
    //Payment Backoffice
    Route::post('/table_payment_back', [PaymentController::class, 'table_payment_back']);
    Route::post('/get_payment_detail_back', [PaymentController::class, 'get_payment_detail_back']);
    Route::post('/accept_payment_back', [PaymentController::class, 'accept_payment_back']);
    Route::post('/reject_payment_back', [PaymentController::class, 'reject_payment_back']);
    //Packing Backoffice
    Route::post('/table_packing_back', [PackingController::class, 'table_packing_back']);
    Route::post('/get_packing_detail_back', [PackingController::class, 'get_packing_detail_back']);
    Route::post('/update_packing_back', [PackingController::class, 'update_packing_back']);
    //Delivery Backoffice
    Route::post('/table_delivery_back', [DeliveryController::class, 'table_delivery_back']);
    Route::post('/get_delivery_detail_back', [DeliveryController::class, 'get_delivery_detail_back']);
    Route::post('/update_delivery_back', [DeliveryController::class, 'update_delivery_back']);
    //Cancel Backoffice
    Route::post('/table_cancel_back', [CancelController::class, 'table_cancel_back']);
    Route::post('/get_cancel_detail_back', [CancelController::class, 'get_cancel_detail_back']);
    Route::post('/update_cancel_back', [CancelController::class, 'update_cancel_back']);
    //Blog Backoffice
    Route::post('/table_blog_back', [BlogController::class, 'table_blog_back']);
    Route::post('/create_blog_back', [BlogController::class, 'create_blog_back']);
    Route::post('/get_blog_detail_back', [BlogController::class, 'get_blog_detail_back']);
    Route::post('/update_blog_back', [BlogController::class, 'update_blog_back']);
    Route::post('/delete_blog_back', [BlogController::class, 'delete_blog_back']);
    //News Backoffice
    Route::post('/table_news_back', [NewController::class, 'table_news_back']);
    Route::post('/create_news_back', [NewController::class, 'create_news_back']);
    Route::post('/get_news_detail_back', [NewController::class, 'get_news_detail_back']);
    Route::post('/update_news_back', [NewController::class, 'update_news_back']);
    Route::post('/delete_news_back', [NewController::class, 'delete_news_back']);
    Route::post('/update_news_show_back', [NewController::class, 'update_news_show_back']);
    Route::post('/update_news_noshow_back', [NewController::class, 'update_news_noshow_back']);
    //Setting Backoffice
    Route::post('/get_setting_banner_back', [SettingController::class, 'get_setting_banner_back']);
    Route::post('/update_setting_banner_back', [SettingController::class, 'update_setting_banner_back']);
    //Stock Backoffice
    Route::post('/table_stock_back', [StockController::class, 'table_stock_back']);
    Route::post('/get_stock_detail', [StockController::class, 'get_stock_detail']);
    Route::post('/update_stock_back', [StockController::class, 'update_stock_back']);
});
