<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Login;
use Illuminate\Http\Request;

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

    public function table_member_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id', 'fname', 'lname', 'tel');

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
}
