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

            $orders = DB::table('view_cart')
                ->select('id', 'product_id', 'name', 'count', 'price', 'path')
                ->get();

            $count_total = 0;
            $price_total = 0;
            foreach ($orders as &$ord) {
                $count_total++;
                $price_total += $ord->price * $ord->count;
            }

            $cart = array();
            $cart['count_total'] = $count_total;
            $cart['price_total'] = $price_total;
            $cart['orders'] = $orders;
            $data['cart'] = $cart;


            if (!empty($category)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
