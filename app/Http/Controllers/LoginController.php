<?php

namespace App\Http\Controllers;

use App\Models\Login;
use App\Models\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function member_login(Request $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($password == "")
                return $this->returnError('[password] ไม่มีข้อมูล', 400);

            $member = Member::where('email', $email)
                ->where('password', md5($password))
                ->first();

            if (!empty($member)) {

                unset($member->password);

                $login = new Login();

                return response()->json([
                    'code' => 200,
                    'status' => true,
                    'massage' => 'เข้าสู่ระบบสำเร็จ',
                    'data' => $member,
                    'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                ], 200);
            } else
                return $this->returnError('ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง', 404);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function member_login_social(Request $request)
    {
        try {
            $provider_id = $request->input('provider_id');
            $fname = $request->input('fname');
            $lname = $request->input('lname');
            $email = $request->input('email');
            $provider = $request->input('provider');

            if ($provider_id == "")
                return $this->returnError('[provider_id] ไม่มีข้อมูล', 400);
            else if ($fname == "")
                return $this->returnError('[fname] ไม่มีข้อมูล', 400);
            else if ($lname == "")
                return $this->returnError('[lname] ไม่มีข้อมูล', 400);
            else if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($provider == "")
                return $this->returnError('[provider] ไม่มีข้อมูล', 400);

            if ($provider == 'FACEBOOK') {
                $member = Member::where('email', $email)
                    ->first();

                $login = new Login();

                if (!empty($member)) {
                    if ($member->facebook_id == $provider_id) {
                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'massage' => 'เข้าสู่ระบบสำเร็จ',
                            'data' => $member,
                            'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                        ], 200);
                    } else {
                        $member->facebook_id = $provider_id;
                        if ($member->update()) {
                            return response()->json([
                                'code' => 200,
                                'status' => true,
                                'massage' => 'เข้าสู่ระบบสำเร็จ',
                                'data' => $member,
                                'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                            ], 200);
                        }
                    }
                } else {
                    $member = new Member();
                    $member->facebook_id = $provider_id;
                    $member->email = $email;
                    $member->fname = $fname;
                    $member->lname = $lname;

                    if ($member->save()) {
                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'massage' => 'เข้าสู่ระบบสำเร็จ',
                            'data' => $member,
                            'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                        ], 200);
                    }
                }
            } else if ($provider == 'GOOGLE') {
                $member = Member::where('email', $email)
                    ->first();

                $login = new Login();

                if (!empty($member)) {
                    if ($member->google_id == $provider_id) {
                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'massage' => 'เข้าสู่ระบบสำเร็จ',
                            'data' => $member,
                            'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                        ], 200);
                    } else {
                        $member->google_id = $provider_id;
                        if ($member->update()) {
                            return response()->json([
                                'code' => 200,
                                'status' => true,
                                'massage' => 'เข้าสู่ระบบสำเร็จ',
                                'data' => $member,
                                'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                            ], 200);
                        }
                    }
                } else {
                    $member = new Member();
                    $member->google_id = $provider_id;
                    $member->email = $email;
                    $member->fname = $fname;
                    $member->lname = $lname;

                    if ($member->save()) {
                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'massage' => 'เข้าสู่ระบบสำเร็จ',
                            'data' => $member,
                            'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                        ], 200);
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

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
