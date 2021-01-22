<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ProductType;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add_cart(Request $request)
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

            $cart = new Cart();
            $cart->member_id = $login_id;
            $cart->product_id = $product_id;
            $cart->product_type_id = $product_type_id;
            $cart->price = $price;
            $cart->count = $count;

            if ($cart->save()) {
                return $this->returnSuccess('ดำเนินการเพิ่มสินค้าลงตะกร้าสำเร็จ', []);
            } else
                return $this->returnError('ดำเนินการเพิ่มสินค้าลงตะกร้าไม่สำเร็จ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
