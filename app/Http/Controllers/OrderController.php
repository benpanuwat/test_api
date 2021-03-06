<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductType;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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

            $orders = Order::select('id', 'code', 'address_id', 'price_total', 'status', 'created_at')
                ->where('member_id', $login_id)
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

    public function table_order_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('code', 'fname', 'lname', 'price_total', 'created_at', 'status');

            $db = DB::table('view_order_list')
                ->select($col)
                ->orderby($col[$order[0]['column']], $order[0]['dir']);

            if ($search['value'] != '' && $search['value'] != null) {
                foreach ($col as &$c) {
                    $db->orWhere($c, 'LIKE', '%' . $search['value'] . '%');
                }
            }

            $member = $db->paginate($length, ['*'], 'page', $page);

            return response()->json($member);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_order_detail_back(Request $request)
    {
        try {

            $code = $request->input('code');

            if ($code == "")
                return $this->returnError('[code] ไม่มีข้อมูล', 400);

            $order = Order::select('id', 'code', 'member_id', 'address_id', 'price_total', 'slip', 'status', 'created_at')
                ->where('code', $code)
                ->first();

            $member = Member::select('fname', 'lname', 'email', 'tel')
                ->where('id', $order->member_id)
                ->first();

            $order->member = $member;

            $address = Address::select('name', 'tel', 'others', 'province', 'amphoe', 'district', 'zipcode')
                ->where('id', $order->address_id)
                ->first();

            $order->address = $address;

            $order_product = OrderProduct::select('product_id', 'product_type_id', 'price', 'count')
                ->where('order_id', $order->id)
                ->get();

            foreach ($order_product as $op) {
                $product = Product::select('name')
                    ->where('id', $op->product_id)
                    ->first();

                $op->name = $product->name;

                $product_type = ProductType::select('name')
                    ->where('id', $op->product_type_id)
                    ->first();

                $op->type_name = $product_type->name;

                $product_image = ProductImage::select('path')
                    ->where('product_id', $op->product_id)
                    ->where('main', 1)
                    ->first();

                $op->path = $product_image->path;
            }

            $order->order_product = $order_product;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $order);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
