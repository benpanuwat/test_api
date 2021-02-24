<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BlogController extends Controller
{
    public function get_blog_date(Request $request)
    {
        try {

            $blog_date = DB::table('view_blog_date')
                ->select('blog_month', 'blog_year', 'count')
                ->get();

            $monthMap = [
                1 => 'ม.ค.',
                2 => 'ก.พ.',
                3 => 'มี.ค.',
                4 => 'เม.ย.',
                5 => 'พ.ค.',
                6 => 'มิ.ย.',
                7 => 'ก.ค.',
                8 => 'ส.ค.',
                9 => 'ก.ย.',
                10 => 'ต.ค.',
                11 => 'พ.ย.',
                12 => 'ธ.ค.'
            ];

            foreach ($blog_date as $bd) {
                $bd->blog_month_th = $monthMap[$bd->blog_month];
            }


            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $blog_date);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_blog_page(Request $request)
    {
        try {

            $month = $request->input('month');
            $year = $request->input('year');
            $order_by = $request->input('order_by');
            $length = $request->input('length');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id', 'title', 'detail', 'created_at', 'user_id', 'fname', 'lname');

            $db = DB::table('view_blogs')
                ->select($col);

            if ($month != '' && $month != null && $year != '' && $year != null) {
                $db->whereMonth('created_at', $month);
                $db->whereYear('created_at', $year);
            }

            if ($search != '' && $search != null) {
                $db->where(function ($query) use ($search) {
                    $query->orWhere('title', 'LIKE', '%' . $search . '%');
                    $query->orWhere('detail', 'LIKE', '%' . $search . '%');
                });
            }

            $blogs = $db->paginate($length, ['*'], 'page', $page);

            $monthMap = [
                '01' => 'ม.ค.',
                '02' => 'ก.พ.',
                '03' => 'มี.ค.',
                '04' => 'เม.ย.',
                '05' => 'พ.ค.',
                '06' => 'มิ.ย.',
                '07' => 'ก.ค.',
                '08' => 'ส.ค.',
                '09' => 'ก.ย.',
                '10' => 'ต.ค.',
                '11' => 'พ.ย.',
                '12' => 'ธ.ค.'
            ];

            foreach ($blogs as $blog) {
                $blog_image =  BlogImage::select('path')
                    ->where('blog_id', $blog->id)
                    ->first();
                $blog->path = $blog_image->path;
                $blog->detail = explode("\n", $blog->detail)[0];
                $blog->day = date('d', strtotime($blog->created_at));
                $blog->month  = $monthMap[date('m', strtotime($blog->created_at))];
                $blog->year = date('Y', strtotime($blog->created_at));
            }

            return response()->json($blogs);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_blog_detail(Request $request)
    {
        try {

            $blog_id = $request->input('id');

            if ($blog_id == "")
                return $this->returnError('[blog_id] ไม่มีข้อมูล', 400);

            $blog = DB::table('view_blogs')
                ->select('id', 'title', 'detail', 'created_at', 'user_id', 'fname', 'lname')
                ->where('id', $blog_id)
                ->first();

            $blog_images = BlogImage::select('id', 'path')
                ->where('blog_id', $blog_id)
                ->get();

            $monthMap = [
                '01' => 'ม.ค.',
                '02' => 'ก.พ.',
                '03' => 'มี.ค.',
                '04' => 'เม.ย.',
                '05' => 'พ.ค.',
                '06' => 'มิ.ย.',
                '07' => 'ก.ค.',
                '08' => 'ส.ค.',
                '09' => 'ก.ย.',
                '10' => 'ต.ค.',
                '11' => 'พ.ย.',
                '12' => 'ธ.ค.'
            ];


            $blog->day = date('d', strtotime($blog->created_at));
            $blog->month  = $monthMap[date('m', strtotime($blog->created_at))];
            $blog->year = date('Y', strtotime($blog->created_at));

            $user =  User::select('fname', 'lname')
                ->where('id', $blog->user_id)
                ->first();

            $blog->user = $user;
            $blog->blog_images = $blog_images;


            $blog_other = DB::table('view_blogs')
                ->select('id', 'title', 'detail', 'created_at')
                ->where('id','<>',$blog->id)
                ->limit(6)
                ->get();

            foreach ($blog_other as $b) {
                $blog_image =  BlogImage::select('path')
                    ->where('blog_id', $b->id)
                    ->first();
                $b->path = $blog_image->path;
                $b->detail = explode("\n", $b->detail)[0];
                $b->day = date('d', strtotime($b->created_at));
                $b->month  = $monthMap[date('m', strtotime($b->created_at))];
                $b->year = date('Y', strtotime($b->created_at));
            }

            $blog->blog_other = $blog_other;
            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $blog);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function table_blog_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id', 'title', 'created_at');

            $db = DB::table('view_blogs')
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

    public function create_blog_back(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $title = $request->input('title');
            $detail = $request->input('detail');
            $blog_images = $request->input('blog_images');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
            else if ($title == "")
                return $this->returnError('[title] ไม่มีข้อมูล', 400);
            else if ($detail == "")
                return $this->returnError('[detail] ไม่มีข้อมูล', 400);
            else if ($blog_images == "")
                return $this->returnError('[blog_images] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $blog = new Blog();
            $blog->user_id = $login_id;
            $blog->title = $title;
            $blog->detail = $detail;

            if ($blog->save()) {

                $path = 'images/blog/' . $blog->id . '/';

                if (!File::exists($path))
                    File::makeDirectory($path, 0777, true);

                foreach ($blog_images as $img) {

                    $file = $img['image'];

                    $extension = explode('/', mime_content_type($file))[1];
                    $filename = md5($blog->id . rand(0, 999999)) . '.' . $extension;
                    file_put_contents($path . $filename, file_get_contents($file));

                    $blog_image = new BlogImage();
                    $blog_image->blog_id = $blog->id;
                    $blog_image->path = $path . $filename;
                    $blog_image->save();
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

    public function get_blog_detail_back(Request $request)
    {
        try {

            $blog_id = $request->input('id');

            if ($blog_id == "")
                return $this->returnError('[blog_id] ไม่มีข้อมูล', 400);

            $data = [];
            $blog = Blog::select('id', 'title', 'detail', 'created_at', 'user_id')
                ->where('id', $blog_id)
                ->first();

            $blog_images = BlogImage::select('id', 'path')
                ->where('blog_id', $blog_id)
                ->get();

            $blog->blog_images = $blog_images;
            $data['blog'] = $blog;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_blog_back(Request $request)
    {
        try {

            $id = $request->input('id');
            $title = $request->input('title');
            $detail = $request->input('detail');
            $blog_images = $request->input('blog_images');

            if ($title == "")
                return $this->returnError('[title] ไม่มีข้อมูล', 400);
            else if ($detail == "")
                return $this->returnError('[detail] ไม่มีข้อมูล', 400);
            else if ($blog_images == "")
                return $this->returnError('[blog_images] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $blog = Blog::where('id', $id)
                ->first();

            if (empty($blog))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $blog->title = $title;
            $blog->detail = $detail;

            if ($blog->update()) {

                $path = 'images/blog/' . $blog->id . '/';

                if (!File::exists($path))
                    File::makeDirectory($path, 0777, true);

                $blog_image_check = BlogImage::where('blog_id', $blog->id)
                    ->get();

                foreach ($blog_image_check as &$img_check) {
                    $status_image_check = true;
                    foreach ($blog_images as &$img) {

                        if ($img['id'] != '' && $img_check->id == $img['id']) {
                            $status_image_check = false;
                            break;
                        }
                    }

                    if ($status_image_check)
                        $img_check->delete();
                }


                foreach ($blog_images as &$img) {

                    if ($img['id'] == "") {

                        $file = $img['image'];

                        $extension = explode('/', mime_content_type($file))[1];
                        $filename = md5($blog->id . rand(0, 999999)) . '.' . $extension;
                        file_put_contents($path . $filename, file_get_contents($file));

                        $blog_image = new BlogImage();
                        $blog_image->blog_id = $blog->id;
                        $blog_image->path = $path . $filename;
                        $blog_image->save();
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

    public function delete_blog_back(Request $request)
    {
        try {

            $id = $request->input('id');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $blog = Blog::where('id', $id)
                ->first();

            if (empty($blog))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $blog->delete();

            DB::commit();
            return $this->returnSuccess('ลบข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
