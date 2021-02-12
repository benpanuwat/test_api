<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function create_member(Request $request)
    {
        try {

            $email = $request->input('email');
            $password = $request->input('password');
            $fname = $request->input('fname');
            $lname = $request->input('lname');

            if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($password == "")
                return $this->returnError('[password] ไม่มีข้อมูล', 400);
            else if ($fname == "")
                return $this->returnError('[fname] ไม่มีข้อมูล', 400);
            else if ($lname == "")
                return $this->returnError('[lname] ไม่มีข้อมูล', 400);

            $checkEmail = Member::where('email', $email)->first();

            if (!empty($checkEmail))
                return $this->returnError('อีเมลนี้ลงทะเบียนแล้ว', 400);

            $member = new Member();
            $member->email = $email;
            $member->password = md5($password);
            $member->fname = $fname;
            $member->lname = $lname;

            if ($member->save()) {

                unset($member->password);

                $login = new Login();

                return response()->json([
                    'code' => 200,
                    'status' => true,
                    'massage' => 'ดำเนินการลงทะเบียนสำเร็จ',
                    'data' => $member,
                    'token' =>  $login->genToken($member->id, $member->fname, $member->email, false)
                ], 200);
            } else
                return $this->returnError('ดำเนินการลงทะเบียนล้มเหลว', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_memder_account(Request $request)
    {
        try {

            $login_id = $request->input('login_id');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);

            $member = Member::select('id', 'fname', 'lname', 'email', 'tel')
                ->where('id', $login_id)
                ->first();

            if (!empty($member)) {
                return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $member);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_memder_account(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $fname = $request->input('fname');
            $lname = $request->input('lname');
            $email = $request->input('email');
            $tel = $request->input('tel');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
            else if ($fname == "")
                return $this->returnError('[fname] ไม่มีข้อมูล', 400);
            else if ($lname == "")
                return $this->returnError('[lname] ไม่มีข้อมูล', 400);
            else if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($tel == "")
                return $this->returnError('[tel] ไม่มีข้อมูล', 400);

            $member = Member::where('id', $login_id)
                ->first();

            if (!empty($member)) {

                $member->fname = $fname;
                $member->lname = $lname;
                $member->email = $email;
                $member->tel = $tel;
                $member->update();

                return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function reset_password(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $old_password = $request->input('old_password');
            $new_password = $request->input('new_password');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);
            else if ($old_password == "")
                return $this->returnError('รหัสผ่านเก่าไม่มีข้อมูล', 400);
            else if ($new_password == "")
                return $this->returnError('รหัสผ่านใหม่ไม่มีข้อมูล', 400);


            $member = Member::where('id', $login_id)
                ->first();

            if (!empty($member)) {

                if (md5($old_password) ==  $member->password) {
                    $member->password = md5($new_password);
                    $member->update();

                    return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
                } else
                    return $this->returnError('รหัสผ่านเก่าไม่ถูกต้อง', 400);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function table_member_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id','email', 'fname', 'lname', 'tel');

            $db = DB::table('members')
                ->select($col)
                ->orderby($col[$order[0]['column']], $order[0]['dir']);

            if ($search['value'] != '' && $search['value'] != null) {
                foreach ($col as &$c) {
                    $db->orWhere($c, 'LIKE', '%' . $search['value'] . '%');
                }
            }

            $member = $db->paginate($length, ['*'], 'page', $page);

            return response()->json($member);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function create_member_back(Request $request)
    {
        try {

            $email = $request->input('email');
            $password = $request->input('password');
            $fname = $request->input('fname');
            $lname = $request->input('lname');
            $tel = $request->input('tel');

            if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($password == "")
                return $this->returnError('[password] ไม่มีข้อมูล', 400);
            else if ($fname == "")
                return $this->returnError('[fname] ไม่มีข้อมูล', 400);
            else if ($lname == "")
                return $this->returnError('[lname] ไม่มีข้อมูล', 400);
            else if ($tel == "")
                return $this->returnError('[tel] ไม่มีข้อมูล', 400);

            $checkEmail = Member::where('email', $email)->first();

            if (!empty($checkEmail))
                return $this->returnError('อีเมลนี้ลงทะเบียนแล้ว', 400);

            $member = new Member();
            $member->email = $email;
            $member->password = md5($password);
            $member->fname = $fname;
            $member->lname = $lname;
            $member->tel = $tel;

            if ($member->save()) {
                return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ดำเนินการลงทะเบียนล้มเหลว', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_memder_back(Request $request)
    {
        try {

            $member_id = $request->input('id');
            $fname = $request->input('fname');
            $lname = $request->input('lname');
            $email = $request->input('email');
            $tel = $request->input('tel');

            if ($member_id == "")
                return $this->returnError('[member_id] ไม่มีข้อมูล', 400);
            else if ($fname == "")
                return $this->returnError('[fname] ไม่มีข้อมูล', 400);
            else if ($lname == "")
                return $this->returnError('[lname] ไม่มีข้อมูล', 400);
            else if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($tel == "")
                return $this->returnError('[tel] ไม่มีข้อมูล', 400);

            $member = Member::where('id', $member_id)
                ->first();

            if (!empty($member)) {

                $member->fname = $fname;
                $member->lname = $lname;
                $member->email = $email;
                $member->tel = $tel;
                $member->update();

                return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_password_back(Request $request)
    {
        try {

            $member_id = $request->input('id');
            $password = $request->input('password');

            if ($member_id == "")
                return $this->returnError('[member_id] ไม่มีข้อมูล', 400);
            else if ($password == "")
                return $this->returnError('[password] ไม่มีข้อมูล', 400);

            $member = Member::where('id', $member_id)
                ->first();

            if (!empty($member)) {

                $member->password = md5($password);
                $member->update();

                return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
