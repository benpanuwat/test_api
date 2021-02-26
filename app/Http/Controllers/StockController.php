<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class StockController extends Controller
{
    public function table_stock_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id', 'name',  'path', 'category_name', 'active',);

            $db = DB::table('view_products')
                ->select($col)
                ->orderby($col[$order[0]['column']], $order[0]['dir']);

            if ($search['value'] != '' && $search['value'] != null) {
                foreach ($col as &$c) {
                    $db->orWhere($c, 'LIKE', '%' . $search['value'] . '%');
                }
            }

            $product = $db->paginate($length, ['*'], 'page', $page);

            foreach ($product as $row) {
                $row->product_type = ProductType::select('id', 'name', 'stock')
                    ->where('product_id', $row->id)
                    ->get();
            }

            return response()->json($product);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_stock_detail(Request $request)
    {
        try {

            $product_id = $request->input('id');

            if ($product_id == "")
                return $this->returnError('[product_id] ไม่มีข้อมูล', 400);

            $product = Product::select('id', 'name', 'description', 'detail', 'standard_price', 'category_id')
                ->where('id', $product_id)
                ->first();

            $product_type = ProductType::select('id', 'name', 'price', 'stock')
                ->where('product_id', $product_id)
                ->get();

            $product->product_type = $product_type;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $product);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_stock_back(Request $request)
    {
        try {

            $product_type = $request->input('product_type');

            if ($product_type == "")
                return $this->returnError('[product_type] ไม่มีข้อมูล', 400);

            foreach ($product_type as $pt) {
                $product_type = ProductType::where('id', $pt['id'])
                    ->first();

                if (!empty($product_type)) {
                    $product_type->stock = intval($product_type->stock) + intval($pt['receive']);
                    $product_type->update();
                }
            }

            return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {
            Db::rollBack();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
