<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Banner;
use App\Models\Partner;
use App\Models\BannerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{

    public function get_setting_banner_back(Request $request)
    {
        try {

            $data = [];
            $banner = Banner::select('id', 'path')
                ->get();
            $data['banner'] = $banner;

            $partner = Partner::select('id', 'path')
                ->get();
            $data['partner'] = $partner;

            $banner_category = BannerCategory::select('id', 'path')
                ->get();

            $data['banner_category'] = $banner_category;


            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_setting_banner_back(Request $request)
    {
        try {

            $setting_banner = $request->input('banner');
            $setting_partner = $request->input('partner');
            $setting_banner_category = $request->input('banner_category');

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

            $partners = Partner::get();
            foreach ($setting_partner as $set_par) {

                if (empty($set_par['id'])) {

                    $partner = new  Partner();
                    if ($partner->save()) {
                        $path = 'images/partner/' . $partner->id . '/';

                        if (!File::exists($path))
                            File::makeDirectory($path, 0777, true);

                        $file = $set_par['image'];

                        $extension = explode('/', mime_content_type($file))[1];
                        $filename = md5($partner->id . rand(0, 999999)) . '.' . $extension;
                        file_put_contents($path . $filename, file_get_contents($file));

                        $partner->path = $path . $filename;
                        $partner->update();
                    }
                }
            }

            foreach ($partners as $par) {
                $statusDelete = true;
                foreach ($setting_partner as $set_par) {
                    if ($par->id == $set_par['id']) {
                        $statusDelete = false;
                        break;
                    }
                }

                if ($statusDelete)
                    $par->delete();
            }

            $banner_categorys = BannerCategory::get();
            foreach ($setting_banner_category as $set_bc) {

                if (empty($set_bc['id'])) {

                    $banner_category = new  BannerCategory();
                    if ($banner_category->save()) {
                        $path = 'images/banner_category/';

                        if (!File::exists($path))
                            File::makeDirectory($path, 0777, true);

                        $file = $set_bc['image'];

                        $extension = explode('/', mime_content_type($file))[1];
                        $filename = md5($banner_category->id . rand(0, 999999)) . '.' . $extension;
                        file_put_contents($path . $filename, file_get_contents($file));

                        $banner_category->path = $path . $filename;
                        $banner_category->update();
                    }
                }
            }

            foreach ($banner_categorys as $bc) {
                $statusDelete = true;
                foreach ($setting_banner_category as $set_bc) {
                    if ($bc->id == $set_bc['id']) {
                        $statusDelete = false;
                        break;
                    }
                }

                if ($statusDelete)
                    $bc->delete();
            }


            DB::commit();
            return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', []);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
