<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductType;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create_order(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $product_id = $request->input('product_id');
            $product_type_id = $request->input('product_type_id');
            $price = $request->input('price');
            $count = $request->input('count');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
            if ($product_id == "")
                return $this->returnError('[product_id] ไม่มีข้อมูล', 400);

            if ($product_type_id == "") {
                $product_type = ProductType::where('product_id', $product_id)->first();
                if (empty($product_type))
                    return $this->returnError('[product_type] ไม่มีข้อมูล', 400);

                $product_type_id =  $product_type->id;
                $price = $product_type->price;
                $count = 1;
            }

            if ($price == "")
                return $this->returnError('[price] ไม่มีข้อมูล', 400);
            if ($count == "")
                return $this->returnError('[count] ไม่มีข้อมูล', 400);

            $order = new Order();
            $order->member_id = $login_id;
            $order->product_id = $product_id;
            $order->product_type_id = $product_type_id;
            $order->price = $price;
            $order->count = $count;

            if ($order->save()) {
                return $this->returnSuccess('ดำเนินการสั่งซื้อสำเร็จ', []);
            } else
                return $this->returnError('ดำเนินการสั่งซื้อไม่สำเร็จ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
