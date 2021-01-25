<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function get_category(Request $request)
    {
        try {

            $category = Category::select('id','name')
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

    public function get_category_back(Request $request)
    {
        try {

            $category = Category::select('id','name')
            ->get();

            if (!empty($category)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $category);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
