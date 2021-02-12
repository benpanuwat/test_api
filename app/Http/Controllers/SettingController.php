<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{

    public function get_banner_back(Request $request)
    {
        try {

            $banner = Banner::select('id', 'path')
                ->get();

            if (!empty($banner)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $banner);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_banner_back(Request $request)
    {
        try {

            $setting_banner = $request->input('banner');

            DB::beginTransaction();

            $banners = Banner::get();

            foreach ($setting_banner as $set_ban) {


                if (empty($set_ban['id'])) {

                    $banner = new  Banner();
                    if ($banner->save()) {
                        $path = 'images/banner/' . $banner->id . '/';

                        if (!File::exists($path))
                            File::makeDirectory($path, 0777, true);

                        $file = $set_ban['image'];

                        $extension = explode('/', mime_content_type($file))[1];
                        $filename = md5($banner->id . rand(0, 999999)) . '.' . $extension;
                        file_put_contents($path . $filename, file_get_contents($file));

                        $banner->path = $path . $filename;
                        $banner->update();
                    }
                }
            }

            foreach ($banners as $ban) {
                $statusDelete = true;
                foreach ($setting_banner as $set_ban) {
                    if ($ban->id == $set_ban['id']) {
                        $statusDelete = false;
                        break;
                    }
                }

                if ($statusDelete)
                $ban->delete();
            }


            DB::commit();
            return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
