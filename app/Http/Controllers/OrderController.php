<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use App\Models\OrderProduct;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // public function create_order(Request $request)
    // {
    //     try {

    //         $login_id = $request->input('login_id');
    //         $product_id = $request->input('product_id');
    //         $product_type_id = $request->input('product_type_id');
    //         $price = $request->input('price');
    //         $count = $request->input('count');

    //         if ($login_id == "")
    //             return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
    //         if ($product_id == "")
    //             return $this->returnError('[product_id] ไม่มีข้อมูล', 400);

    //         if ($product_type_id == "") {
    //             $product_type = ProductType::where('product_id', $product_id)->first();
    //             if (empty($product_type))
    //                 return $this->returnError('[product_type] ไม่มีข้อมูล', 400);

    //             $product_type_id =  $product_type->id;
    //             $price = $product_type->price;
    //             $count = 1;
    //         }

    //         if ($price == "")
    //             return $this->returnError('[price] ไม่มีข้อมูล', 400);
    //         if ($count == "")
    //             return $this->returnError('[count] ไม่มีข้อมูล', 400);

    //         $order = new Order();
    //         $order->member_id = $login_id;
    //         $order->product_id = $product_id;
    //         $order->product_type_id = $product_type_id;
    //         $order->price = $price;
    //         $order->count = $count;

    //         if ($order->save()) {
    //             return $this->returnSuccess('ดำเนินการสั่งซื้อสำเร็จ', []);
    //         } else
    //             return $this->returnError('ดำเนินการสั่งซื้อไม่สำเร็จ', 400);
    //     } catch (\Exception $e) {
    //         return $this->returnError($e->getMessage(), 405);
    //     }
    // }

    public function get_order_list(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $status = $request->input('status');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
            if ($status == "")
                return $this->returnError('[status] ไม่มีข้อมูล', 400);

            $data = array();

            $status_count = array(
                'payment' => 0,
                'packing' => 0,
                'delivery' => 0,
                'finish' => 0,
                'cancel' => 0
            );

            $order_status_list = DB::table('view_order_status')
                ->select('status', 'count')
                ->where('member_id', $login_id)
                ->get();

            foreach ($order_status_list as $order_status) {
                if ($order_status->status == 'payment')
                    $status_count['payment'] = $order_status->count;
                else if ($order_status->status == 'packing')
                    $status_count['packing'] = $order_status->count;
                else if ($order_status->status == 'delivery')
                    $status_count['delivery'] = $order_status->count;
                else if ($order_status->status == 'finish')
                    $status_count['finish'] = $order_status->count;
                else if ($order_status->status == 'cancel')
                    $status_count['cancel'] = $order_status->count;
            }

            $data['status_count'] = $status_count;

            $orders = Order::select('id','code', 'address_id', 'price_total', 'status', 'created_at')
                ->where(function ($query) use ($status) {
                    if ($status != 'all')
                        $query->where('status', $status);
                })
                ->limit(50)
                ->get();

            foreach ($orders as $order) {
                $address = Address::select('id', 'name', 'tel', 'others', 'province', 'amphoe', 'district', 'zipcode')
                    ->where('id', $order->address_id)
                    ->first();
                $order['address'] = $address;

                $products = DB::table('view_order_product')
                    ->where('order_id', $order->id)
                    ->get();

                $order['products'] = $products;
            }

            $data['orders'] = $orders;

            if (!empty($status_count)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
