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

class PaymentController extends Controller
{

    public function table_payment_back(Request $request)
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
                ->where('status', 'payment')
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

    public function get_payment_detail_back(Request $request)
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

    public function accept_payment_back(Request $request)
    {
        try {

            $order_id = $request->input('id');

            if ($order_id == "")
                return $this->returnError('[order_id] ไม่มีข้อมูล', 400);

            $order = Order::where('id', $order_id)
                ->first();

            if (!empty($order)) {

                $order_product  = OrderProduct::where('order_id', $order->id)
                    ->get();

                foreach ($order_product as $op) {
                    $product_type  = ProductType::where('id', $op->product_type_id)
                        ->first();

                    if ($op->count > $product_type->stock) {
                        $product  = Product::where('id', $op->product_id)
                            ->first();

                        return $this->returnError($product->name . '(' . $product_type->name . ') มีสินค้าไม่พอ ปัจจุบันคงเหลือ ' . $product_type->stock . ' ชิ้น', 400);
                    }

                    $product_type->stock =  $product_type->stock - $op->count;
                    $product_type->update();
                }

                DB::beginTransaction();

                $order->status = 'packing';
                $order->update();

                DB::commit();
                return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            Db::rollBack();
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function reject_payment_back(Request $request)
    {
        try {

            $order_id = $request->input('id');

            if ($order_id == "")
                return $this->returnError('[order_id] ไม่มีข้อมูล', 400);

            $order = Order::where('id', $order_id)
                ->first();

            if (!empty($order)) {
                $order->status = 'cancel';
                $order->update();

                return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
