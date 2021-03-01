<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductType;
use App\Models\Category;
use App\Models\Banner;
use App\Models\BannerCategory;
use App\Models\Partner;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{

    public function get_home(Request $request)
    {
        try {

            $data = [];

            $banners = Banner::select('id', 'path')->get();
            $data['banners'] = $banners;

            $partner = Partner::select('id', 'path')->get();
            $data['partner'] = $partner;

            $product_recommend = DB::table('view_products_recommend_home')
                ->select('product_id', 'name','name_en', 'standard_price', 'category_id', 'category_name','category_name_en', 'path', 'price')
                ->orderBy('product_id', 'DESC')
                ->limit(10)
                ->where('active', '1')
                ->get();

            foreach ($product_recommend as &$pro) {
                $pro->discount = 0;
                if ($pro->standard_price > 0)
                    $pro->discount = 100 - intval(($pro->price / intval($pro->standard_price)) * 100);
            }

            $data['product_recommend'] = $product_recommend;

            $product_new = DB::table('view_products_home')
                ->select('product_id', 'name','name_en', 'standard_price', 'category_id', 'category_name','category_name_en', 'path', 'price')
                ->orderBy('product_id', 'DESC')
                ->limit(10)
                ->where('active', 1)
                ->get();

            foreach ($product_new as &$pro) {
                $pro->discount = 0;
                if ($pro->standard_price > 0)
                    $pro->discount = 100 - intval(($pro->price / intval($pro->standard_price)) * 100);
            }

            $data['product_new'] = $product_new;

            $category = DB::table('view_category')
                ->select('id', 'name','name_en', 'path', 'product_count')
                ->where('name', '<>', 'ไม่มีกลุ่มสินค้า')
                ->get();

            $data['category'] = $category;

            $news = DB::table('view_news')
                ->select('id', 'title','title_en', 'detail','detail_en', 'created_at', 'path')
                ->where('show', 1)
                ->orderBy('id','desc')
                ->limit(6)
                ->get();

            foreach ($news as $n) {
                $n->day = date('d', strtotime($n->created_at));
                $n->month  = date('m', strtotime($n->created_at));
                $n->year = date('Y', strtotime($n->created_at));
            }

            $data['news'] = $news;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_data_page(Request $request)
    {
        try {

            $data = [];

            $category = Category::select('id', 'name','name_en')
                ->where('name', '<>', 'ไม่มีกลุ่มสินค้า')
                ->get();

            $data['category'] = $category;

            $banner_category = BannerCategory::select('id', 'path')->get();
            $data['banner_category'] = $banner_category;

            $product_recommend = DB::table('view_products_recommend_home')
                ->select('product_id', 'name','name_en', 'standard_price', 'category_id', 'category_name','category_name_en', 'path', 'price')
                ->orderBy('product_id', 'DESC')
                ->limit(10)
                ->where('active', '1')
                ->get();

            foreach ($product_recommend as &$pro) {
                $pro->discount = 0;
                if ($pro->standard_price > 0)
                    $pro->discount = 100 - intval(($pro->price / intval($pro->standard_price)) * 100);
            }

            $data['product_recommend'] = $product_recommend;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_product_page(Request $request)
    {
        try {

            $category_id = $request->input('category_id');
            $order_by = $request->input('order_by');
            $length = $request->input('length');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('product_id', 'name','name_en', 'description','description_en', 'detail','detail_en', 'standard_price', 'category_id', 'category_name','category_name_en', 'path', 'price');

            $db = DB::table('view_products_page')
                ->select($col)
                ->where('active', 1);

            if ($category_id != '' && $category_id != null) {
                $db->where('category_id', $category_id);
            }

            if ($order_by == 'price')
                $db->orderBy('price', 'ASC');
            else if ($order_by == 'price-desc')
                $db->orderBy('price', 'DESC');
            else
                $db->orderBy('product_id', 'DESC');

            if ($search != '' && $search != null) {
                $db->where(function ($query) use ($search) {
                    $query->orWhere('name', 'LIKE', '%' . $search . '%');
                    $query->orWhere('name_en', 'LIKE', '%' . $search . '%');
                    $query->orWhere('description', 'LIKE', '%' . $search . '%');
                    $query->orWhere('description_en', 'LIKE', '%' . $search . '%');
                    $query->orWhere('detail', 'LIKE', '%' . $search . '%');
                    $query->orWhere('detail_en', 'LIKE', '%' . $search . '%');
                });
            }

            $product = $db->paginate($length, ['*'], 'page', $page);

            foreach ($product as &$pro) {
                $pro->discount = 0;
                if ($pro->standard_price > 0)
                    $pro->discount = 100 - intval(($pro->price / intval($pro->standard_price)) * 100);
            }

            return response()->json($product);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_product_detail(Request $request)
    {
        try {

            $product_id = $request->input('product_id');

            if ($product_id == "")
                return $this->returnError('[product_id] ไม่มีข้อมูล', 400);

            $product = Product::select('id', 'name','name_en', 'description','description_en', 'detail','detail_en', 'standard_price', 'category_id')
                ->where('id', $product_id)
                ->first();

            $product_image = ProductImage::select('id', 'path', 'main')
                ->where('product_id', $product_id)
                ->get();

            $product->product_image = $product_image;

            $product_type = ProductType::select('id', 'name','name_en', 'price', 'stock')
                ->where('product_id', $product_id)
                ->get();

            $product->product_type = $product_type;

            $category_product = DB::table('view_producy_category')
                ->select('product_id', 'name','name_en', 'standard_price', 'category_id', 'category_name','category_name_en', 'path', 'price')
                ->where('category_id', $product->category_id)
                ->where('product_id', '<>', $product->id)
                ->limit(6)
                ->where('active', 1)
                ->get();

            foreach ($category_product as &$pro) {
                $pro->discount = 0;
                if ($pro->standard_price > 0)
                    $pro->discount = 100 - intval(($pro->price / intval($pro->standard_price)) * 100);
            }

            $product->category_product = $category_product;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $product);
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
            $product_image = $request->input('product_image');
            $product_type = $request->input('product_type');

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
            else if ($product_image == "")
                return $this->returnError('[product_image] ไม่มีข้อมูล', 400);
            else if ($product_type == "")
                return $this->returnError('[product_type] ไม่มีข้อมูล', 400);

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

                foreach ($product_image as &$img) {

                    $file = $img['image'];

                    $extension = explode('/', mime_content_type($file))[1];
                    $filename = md5($product->id . rand(0, 999999)) . '.' . $extension;
                    file_put_contents($path . $filename, file_get_contents($file));

                    $product_image = new ProductImage();
                    $product_image->product_id = $product->id;
                    $product_image->path = $path . $filename;
                    $product_image->main = ($img['main'] == true) ? 1 : 0;
                    $product_image->save();;
                }

                foreach ($product_type as &$type) {

                    $product_type = new ProductType();
                    $product_type->product_id = $product->id;
                    $product_type->name = $type['name'];
                    $product_type->price = $type['price'];
                    $product_type->stock = $type['stock'];
                    $product_type->save();;
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

    public function get_product_detail_back(Request $request)
    {
        try {

            $product_id = $request->input('id');

            if ($product_id == "")
                return $this->returnError('[product_id] ไม่มีข้อมูล', 400);

            $data = [];
            $product = Product::select('id', 'name', 'description', 'detail', 'standard_price', 'category_id')
                ->where('id', $product_id)
                ->first();

            $product_image = ProductImage::select('id', 'path', 'main')
                ->where('product_id', $product_id)
                ->get();

            foreach ($product_image as $img) {
                $img->main = ($img->main == 1) ? true : false;
            }

            $product->product_image = $product_image;

            $product_type = ProductType::select('id', 'name', 'price', 'stock')
                ->where('product_id', $product_id)
                ->get();

            $product->product_type = $product_type;
            $data['product'] = $product;

            $category = Category::select('id', 'name')
                ->get();
            $data['category'] = $category;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_product_back(Request $request)
    {
        try {

            $id = $request->input('id');
            $name = $request->input('name');
            $description = $request->input('description');
            $detail = $request->input('detail');
            $category_id = $request->input('category_id');
            $standard_price = $request->input('standard_price');
            $images = $request->input('product_image');
            $types = $request->input('product_type');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);
            else if ($name == "")
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
                return $this->returnError('[product_image] ไม่มีข้อมูล', 400);
            else if ($types == "")
                return $this->returnError('[product_type] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $product = Product::where('id', $id)
                ->first();

            if (empty($product))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $product->name = $name;
            $product->description = $description;
            $product->detail = $detail;
            $product->category_id = $category_id;
            $product->standard_price = $standard_price;


            if ($product->update()) {

                $path = 'images/product/' . $product->id . '/';

                if (!File::exists($path))
                    File::makeDirectory($path, 0777, true);

                $product_image_check = ProductImage::where('product_id', $product->id)
                    ->get();

                foreach ($product_image_check as &$img_check) {
                    $status_image_check = true;
                    foreach ($images as &$img) {

                        if ($img['id'] != '' && $img_check->id == $img['id']) {
                            $status_image_check = false;
                            break;
                        }
                    }

                    if ($status_image_check)
                        $img_check->delete();
                }


                foreach ($images as &$img) {

                    if ($img['id'] == "") {

                        $file = $img['image'];

                        $extension = explode('/', mime_content_type($file))[1];
                        $filename = md5($product->id . rand(0, 999999)) . '.' . $extension;
                        file_put_contents($path . $filename, file_get_contents($file));

                        $product_image = new ProductImage();
                        $product_image->product_id = $product->id;
                        $product_image->path = $path . $filename;
                        $product_image->main = ($img['main'] == true) ? 1 : 0;
                        $product_image->save();
                    } else {
                        $product_image = ProductImage::where('id', $img['id'])
                            ->first();

                        if (!empty($product_image)) {
                            $product_image->main = ($img['main'] == true) ? 1 : 0;
                            $product_image->update();
                        }
                    }
                }


                $product_type_check = ProductType::where('product_id', $product->id)
                    ->get();

                foreach ($product_type_check as &$type_check) {
                    $status_type_check = true;
                    foreach ($types as &$type) {

                        if ($type['id'] != '' && $type_check->id == $type['id']) {
                            $status_type_check = false;
                            break;
                        }
                    }

                    if ($status_type_check)
                        $type_check->delete();
                }

                foreach ($types as &$type) {

                    if ($type['id'] == "") {

                        $product_type = new ProductType();
                        $product_type->product_id = $product->id;
                        $product_type->name = $type['name'];
                        $product_type->price = $type['price'];
                        $product_type->stock = $type['stock'];
                        $product_type->save();
                    } else {
                        $product_type = ProductType::where('id', $type['id'])
                            ->first();

                        if (!empty($product_type)) {
                            $product_type->name = $type['name'];
                            $product_type->price = $type['price'];
                            $product_type->stock = $type['stock'];
                            $product_type->update();
                        }
                    }
                }

                DB::commit();
                return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('แก้ไขข้อมูลล้มเหลว', 400);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_product_active_back(Request $request)
    {
        try {

            $id = $request->input('id');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);

            $product = Product::where('id', $id)
                ->first();

            if (empty($product))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $product->active = 1;
            $product->update();

            return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {

            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_product_noactive_back(Request $request)
    {
        try {

            $id = $request->input('id');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);

            $product = Product::where('id', $id)
                ->first();

            if (empty($product))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $product->active = 0;
            $product->update();

            return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {

            return $this->returnError($e->getMessage(), 405);
        }
    }
}
