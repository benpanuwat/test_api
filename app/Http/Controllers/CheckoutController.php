<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ProductType;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
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

    public function get_checkout(Request $request)
    {
        try {

            $login_id = $request->input('login_id');

            $data = array();
            $cart = array();


            $products = DB::table('view_cart')
                ->select('id', 'product_id', 'name', 'product_type_id', 'type_name', 'count', 'price', 'path')
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

            $address = Address::where('member_id', $login_id)
                ->select('id', 'name', 'tel', 'others', 'province', 'amphoe', 'district', 'zipcode')
                ->get();

            $data['address'] = $address;

            $payment = array();
            array_push($payment, array("name" => "พร้อมเพย์", "image" => "assets/images/PromptPay-logo.jpg"));
            $data['payment'] = $payment;

            if (!empty($data)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_qrcode(Request $request)
    {
        try {

            $amount = $request->input('price_total');

            if ($amount == "")
                return $this->returnError('[amount] ไม่มีข้อมูล', 400);

            $pp = new \KS\PromptPay();
            $target = '090-913-4753';
            $data = $pp->generatePayload($target, $amount);

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function create_order(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $address_id = $request->input('address_id');
            $price_total = $request->input('price_total');
            $products = $request->input('products');
            $slip = $request->input('slip');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
            if ($address_id == "")
                return $this->returnError('[address_id] ไม่มีข้อมูล', 400);
            if ($price_total == "")
                return $this->returnError('[price_total] ไม่มีข้อมูล', 400);
            if ($products == "")
                return $this->returnError('[products] ไม่มีข้อมูล', 400);
            if ($slip == "")
                return $this->returnError('[slip] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $order = new Order();
            $order->member_id = $login_id;
            $order->address_id = $address_id;
            $order->price_total = $price_total;
            $order->save();

            $order->code = 'TLM' . str_pad($order->id, 10, "0", STR_PAD_LEFT);
            $order->update();

            foreach ($products as &$pro) {
                $order_product = new OrderProduct();
                $order_product->order_id =  $order->id;
                $order_product->product_id =  $pro['product_id'];
                $order_product->product_type_id =  $pro['product_type_id'];
                $order_product->price = $pro['price'];
                $order_product->count = $pro['count'];
                $order_product->save();

                $cart = Cart::where('id', $pro['id'])->first();
                $cart->delete();
            }

            if (isset($slip)) { //อัพเดตสลิป
                $pathPayment =  'images/order/' . $order->id . '/slip/';

                if (!File::exists($slip))
                    File::makeDirectory($pathPayment, 0777, true);

                $file = $slip;

                $extension = explode('/', mime_content_type($file))[1];
                $filename = md5($login_id . rand(0, 999999)) . '.' . $extension;
                file_put_contents($pathPayment . $filename, file_get_contents($file));

                $order->slip =  $pathPayment . $filename;
                $order->update();
            }

            DB::commit();
            return $this->returnSuccess('ดำเนินการสั่งซื้อสำเร็จ', []);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
