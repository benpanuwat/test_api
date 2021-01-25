<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeaderController extends Controller
{
    public function get_header(Request $request)
    {
        try {

            $data = array();
            $cart = array();

            $category = Category::select('id', 'name')
                ->where('name', '<>', 'ไม่มีกลุ่มสินค้า')
                ->get();

            $data['category'] = $category;

            $cart = array();
            $cart['count_total'] = 0;
            $cart['price_total'] = 0;
            $cart['products'] = [];
            $data['cart'] = $cart;


            if (!empty($data)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_header_login(Request $request)
    {
        try {

            $login_id = $request->input('login_id');

            $data = array();
            $cart = array();

            $category = Category::select('id', 'name')
                ->where('name', '<>', 'ไม่มีกลุ่มสินค้า')
                ->get();

            $data['category'] = $category;

            $products = DB::table('view_cart')
                ->select('id', 'product_id', 'name', 'count', 'price', 'path')
                ->where('member_id', $login_id)
                ->get();

            $count_total = 0;
            $price_total = 0;
            foreach ($products as &$pro) {
                $count_total++;
                $price_total += $pro->price * $pro->count;
            }

            $cart = array();
            $cart['count_total'] = $count_total;
            $cart['price_total'] = $price_total;
            $cart['products'] = $products;
            $data['cart'] = $cart;


            if (!empty($data)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
