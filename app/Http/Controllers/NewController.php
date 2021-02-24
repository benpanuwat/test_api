<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsImage;
use App\Models\User;
use Dotenv\Parser\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class NewController extends Controller
{
    public function get_news_date(Request $request)
    {
        try {

            $news_date = DB::table('view_news_date')
                ->select('news_month', 'news_year', 'count')
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

            foreach ($news_date as $nd) {
                $nd->news_month_th = $monthMap[$nd->news_month];
            }


            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $news_date);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_news_page(Request $request)
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

            $db = DB::table('view_news')
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

            $news = $db->paginate($length, ['*'], 'page', $page);

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

            foreach ($news as $n) {
                $news_image =  NewsImage::select('path')
                    ->where('new_id', $n->id)
                    ->first();
                $n->path = $news_image->path;
                $n->detail = explode("\n", $n->detail)[0];
                $n->day = date('d', strtotime($n->created_at));
                $n->month  = $monthMap[date('m', strtotime($n->created_at))];
                $n->year = date('Y', strtotime($n->created_at));

                $user =  User::select('fname', 'lname')
                    ->where('id', $n->user_id)
                    ->first();

                $news->user = $user;
            }

            return response()->json($news);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_news_detail(Request $request)
    {
        try {

            $news_id = $request->input('id');

            if ($news_id == "")
                return $this->returnError('[news_id] ไม่มีข้อมูล', 400);

            $news = DB::table('view_news')
                ->select('id', 'title', 'detail', 'created_at', 'user_id', 'fname', 'lname')
                ->where('id', $news_id)
                ->first();

            $news_images = NewsImage::select('id', 'path')
                ->where('new_id', $news_id)
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


            $news->day = date('d', strtotime($news->created_at));
            $news->month  = $monthMap[date('m', strtotime($news->created_at))];
            $news->year = date('Y', strtotime($news->created_at));

            $user =  User::select('fname', 'lname')
                ->where('id', $news->user_id)
                ->first();

            $news->user = $user;
            $news->news_images = $news_images;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $news);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function table_news_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id','path', 'title', 'created_at','show');

            $db = DB::table('view_news')
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

    public function create_news_back(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $title = $request->input('title');
            $detail = $request->input('detail');
            $image = $request->input('image');
            $news_images = $request->input('news_images');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
            else if ($title == "")
                return $this->returnError('[title] ไม่มีข้อมูล', 400);
            else if ($detail == "")
                return $this->returnError('[detail] ไม่มีข้อมูล', 400);
            else if ($image == "")
                return $this->returnError('[image] ไม่มีข้อมูล', 400);
            else if ($news_images == "")
                return $this->returnError('[news_images] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $new = new News();
            $new->user_id = $login_id;
            $new->title = $title;
            $new->detail = $detail;

            if ($new->save()) {

                $path = 'images/news/' . $new->id . '/';

                if (!File::exists($path))
                    File::makeDirectory($path, 0777, true);

                $file = $image;

                $extension = explode('/', mime_content_type($file))[1];
                $filename = md5($new->id . rand(0, 999999)) . '.' . $extension;
                file_put_contents($path . $filename, file_get_contents($file));

                $new->path = $path . $filename;
                $new->update();


                foreach ($news_images as $img) {

                    $file = $img['image'];

                    $extension = explode('/', mime_content_type($file))[1];
                    $filename = md5($new->id . rand(0, 999999)) . '.' . $extension;
                    file_put_contents($path . $filename, file_get_contents($file));

                    $new_image = new NewsImage();
                    $new_image->new_id = $new->id;
                    $new_image->path = $path . $filename;
                    $new_image->save();
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

    public function get_news_detail_back(Request $request)
    {
        try {

            $new_id = $request->input('id');

            if ($new_id == "")
                return $this->returnError('[new_id] ไม่มีข้อมูล', 400);

            $data = [];
            $news = News::select('id', 'title', 'detail', 'path', 'created_at', 'user_id')
                ->where('id', $new_id)
                ->first();

            $news_images = NewsImage::select('id', 'path')
                ->where('new_id', $new_id)
                ->get();

            $news->news_images = $news_images;
            $data['news'] = $news;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_news_back(Request $request)
    {
        try {

            $id = $request->input('id');
            $title = $request->input('title');
            $detail = $request->input('detail');
            $image = $request->input('image');
            $news_images = $request->input('news_images');

            if ($title == "")
                return $this->returnError('[title] ไม่มีข้อมูล', 400);
            else if ($detail == "")
                return $this->returnError('[detail] ไม่มีข้อมูล', 400);
            else if ($news_images == "")
                return $this->returnError('[news_images] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $news = News::where('id', $id)
                ->first();

            if (empty($news))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $news->title = $title;
            $news->detail = $detail;

            if ($news->update()) {

                $path = 'images/news/' . $news->id . '/';

                if (!File::exists($path))
                    File::makeDirectory($path, 0777, true);

                if (!empty($image)) {
                    $file = $image;
                    $extension = explode('/', mime_content_type($file))[1];
                    $filename = md5($news->id . rand(0, 999999)) . '.' . $extension;
                    file_put_contents($path . $filename, file_get_contents($file));

                    $news->path = $path . $filename;
                    $news->update();
                }

                $news_image_check = NewsImage::where('new_id', $news->id)
                    ->get();

                foreach ($news_image_check as &$img_check) {
                    $status_image_check = true;
                    foreach ($news_images as &$img) {

                        if ($img['id'] != '' && $img_check->id == $img['id']) {
                            $status_image_check = false;
                            break;
                        }
                    }

                    if ($status_image_check)
                        $img_check->delete();
                }


                foreach ($news_images as &$img) {

                    if ($img['id'] == "") {

                        $file = $img['image'];

                        $extension = explode('/', mime_content_type($file))[1];
                        $filename = md5($news->id . rand(0, 999999)) . '.' . $extension;
                        file_put_contents($path . $filename, file_get_contents($file));

                        $news_image = new NewsImage();
                        $news_image->new_id = $news->id;
                        $news_image->path = $path . $filename;
                        $news_image->save();
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

    public function delete_news_back(Request $request)
    {
        try {

            $id = $request->input('id');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $news = News::where('id', $id)
                ->first();

            if (empty($news))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $news->delete();

            DB::commit();
            return $this->returnSuccess('ลบข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_news_show_back(Request $request)
    {
        try {

            $id = $request->input('id');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);

            $news = News::where('id', $id)
                ->first();

            if (empty($news))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $news->show = 1;
            $news->update();

            return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {

            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_news_noshow_back(Request $request)
    {
        try {

            $id = $request->input('id');

            if ($id == "")
                return $this->returnError('[id] ไม่มีข้อมูล', 400);

            $news = News::where('id', $id)
                ->first();

            if (empty($news))
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);

            $news->show = 0;
            $news->update();

            return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {

            return $this->returnError($e->getMessage(), 405);
        }
    }

}
