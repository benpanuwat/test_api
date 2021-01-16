<?php

namespace App\Http\Controllers;

use App\Models\Login;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function user_login_back(Request $request)
    {
        try {
            $username = $request->input('username');
            $password = $request->input('password');

            if ($username == "")
                return $this->returnError('[username] ไม่มีข้อมูล', 400);
            else if ($password == "")
                return $this->returnError('[password] ไม่มีข้อมูล', 400);

            $user = DB::table('users')
                ->select('id', 'username')
                ->where('username', $username)
                ->where('password', md5($password))
                ->first();

            // $permission = Permission::select('id', 'name')->where('id', $user->permission_id)->where('active', 1)->first();
            // $menus = $permission->menus;
            // $menu_list = [];
            // foreach ($menus as $menu) {
            //     $menu_list[] = $menu->name;
            // }
            // $permission->menu_list = $menu_list;

            // $user->permission = $permission->makeHidden('menus');

            if (!empty($user)) {

                $login = new Login();

                return response()->json([
                    'code' => 200,
                    'status' => true,
                    'massage' => 'เข้าสู่ระบบสำเร็จ',
                    'data' => $user,
                    'token' =>  $login->genToken($user->id, $user->username, '', true)
                ], 200);
            } else
                return $this->returnError('ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
