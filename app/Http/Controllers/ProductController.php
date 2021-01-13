<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{

    public function get_product_home(Request $request)
    {
        try {

            $orders = DB::table('view_products')
                ->select('priduct_id', 'name', 'standard_price', 'category_id', 'category_name')
                ->where('active', '1')
                ->get();


            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $orders);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function table_product_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id', 'name');

            $db = DB::table('products')
                ->select($col)
                ->orderby($col[$order[0]['column']], $order[0]['dir']);

            if ($search['value'] != '' && $search['value'] != null) {
                foreach ($col as &$c) {
                    $db->orWhere($c, 'LIKE', '%' . $search['value'] . '%');
                }
            }

            $product = $db->paginate($length, ['*'], 'page', $page);

            return response()->json($product);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function create_product_back(Request $request)
    {
        try {

            $name = $request->input('name');
            $description = $request->input('description');
            $detail = $request->input('detail');
            $category_id = $request->input('category_id');
            $standard_price = $request->input('standard_price');
            $images = $request->input('images');
            $types = $request->input('types');

            if ($name == "")
                return $this->returnError('[name] ไม่มีข้อมูล', 400);
            else if ($description == "")
                return $this->returnError('[description] ไม่มีข้อมูล', 400);
            else if ($detail == "")
                return $this->returnError('[detail] ไม่มีข้อมูล', 400);
            else if ($category_id == "")
                return $this->returnError('[category_id] ไม่มีข้อมูล', 400);
            else if ($standard_price == "")
                return $this->returnError('[standard_price] ไม่มีข้อมูล', 400);
            else if ($images == "")
                return $this->returnError('[images] ไม่มีข้อมูล', 400);
            else if ($types == "")
                return $this->returnError('[types] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $product = new Product();
            $product->name = $name;
            $product->description = $description;
            $product->detail = $detail;
            $product->category_id = $category_id;
            $product->standard_price = $standard_price;


            if ($product->save()) {

                $path = 'images/product/' . $product->id . '/';

                if (!File::exists($path))
                    File::makeDirectory($path, 0777, true);

                foreach ($images as &$img) {

                    $file = $img['image'];

                    $extension = explode('/', mime_content_type($file))[1];
                    $filename = md5($product->id . rand(0, 999999)) . '.' . $extension;
                    file_put_contents($path . $filename, file_get_contents($file));

                    $product_image = new ProductImage();
                    $product_image->id = $product->id;
                    $product_image->path = $path . $filename;
                    $product_image->main = ($img['main'] == true) ? 1 : 0;
                    $product_image->save();;
                }

                foreach ($types as &$type) {

                    $product_type = new ProductType();
                    $product_type->product_id = $product->id;
                    $product_type->name = $type['name'];
                    $product_type->price = $type['price'];
                    $product_type->stock = $type['stock'];
                    $product_type->save();;
                }

                DB::commit();
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ดำเนินการเพิ่มสินค้าล้มเหลว', 400);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
