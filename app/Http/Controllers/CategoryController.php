<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function get_category(Request $request)
    {
        try {

            $category = Category::select('id', 'name')
                ->where('name', '<>', 'ไม่มีกลุ่มสินค้า')
                ->get();

            if (!empty($category)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $category);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function table_category_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id', 'path', 'name');

            $db = DB::table('categories')
                ->select($col)
                ->where('id', '<>', 1)
                ->orderby($col[$order[0]['column']], $order[0]['dir']);

            if ($search['value'] != '' && $search['value'] != null) {
                foreach ($col as &$c) {
                    $db->orWhere($c, 'LIKE', '%' . $search['value'] . '%');
                }
            }

            $category = $db->paginate($length, ['*'], 'page', $page);

            return response()->json($category);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_category_back(Request $request)
    {
        try {

            $category = Category::select('id', 'name')
                ->get();

            if (!empty($category)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $category);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function create_category_back(Request $request)
    {
        try {

            $name = $request->input('name');
            $image = $request->input('image');

            if ($name == "")
                return $this->returnError('[name] ไม่มีข้อมูล', 400);
            else if ($image == "")
                return $this->returnError('[image] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $category = new Category();
            $category->name = $name;

            if ($category->save()) {

                $path = 'images/category/' . $category->id . '/';

                if (!File::exists($path))
                    File::makeDirectory($path, 0777, true);

                $file = $image;

                $extension = explode('/', mime_content_type($file))[1];
                $filename = md5($category->id . rand(0, 999999)) . '.' . $extension;
                file_put_contents($path . $filename, file_get_contents($file));

                $category->path = $path . $filename;
                $category->update();

                DB::commit();
                return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('เพิ่มข้อมูลล้มเหลว', 400);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_category_back(Request $request)
    {
        try {

            $id = $request->input('id');
            $name = $request->input('name');
            $image = $request->input('image');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);
            else if ($name == "")
                return $this->returnError('[name] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $category = Category::where('id', $id)
                ->first();

            if (empty($category))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $category->name = $name;

            if ($category->update()) {

                if ($image != '') {

                    $path = 'images/category/' . $category->id . '/';

                    if (!File::exists($path))
                        File::makeDirectory($path, 0777, true);

                    $file = $image;

                    $extension = explode('/', mime_content_type($file))[1];
                    $filename = md5($category->id . rand(0, 999999)) . '.' . $extension;
                    file_put_contents($path . $filename, file_get_contents($file));

                    $category->path = $path . $filename;
                    $category->update();
                }

                DB::commit();
                return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('เพิ่มข้อมูลล้มเหลว', 400);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function delete_category_back(Request $request)
    {
        try {

            $id = $request->input('id');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $category = Category::where('id', $id)
                ->first();

            if (empty($category))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $product = Product::where('category_id',  $category->id)
                ->get();

            foreach ($product as $pro) {
                $pro->category_id = 1;
                $pro->update();
            }

            $category->delete();

            DB::commit();
            return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
