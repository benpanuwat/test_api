<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function get_footer(Request $request)
    {
        try {

            $data = array();
            $category = Category::select('id', 'name')
                ->where('name', '<>', 'ไม่มีกลุ่มสินค้า')
                ->get();

            $data['category'] = $category;

            if (!empty($data)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

}
