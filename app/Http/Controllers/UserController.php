<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function table_user_back(Request $request)
    {
        try {

            $columns = $request->input('columns');
            $length = $request->input('length');
            $order = $request->input('order');
            $search = $request->input('search');
            $start = $request->input('start');
            $page = $start / $length + 1;

            $col = array('id', 'username', 'fname', 'lname', 'email');

            $db = DB::table('users')
                ->select($col)
                ->orderby($col[$order[0]['column']], $order[0]['dir']);

            if ($search['value'] != '' && $search['value'] != null) {
                foreach ($col as &$c) {
                    $db->orWhere($c, 'LIKE', '%' . $search['value'] . '%');
                }
            }

            $user = $db->paginate($length, ['*'], 'page', $page);

            return response()->json($user);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function create_user_back(Request $request)
    {
        try {

            $username = $request->input('username');
            $password = $request->input('password');
            $fname = $request->input('fname');
            $lname = $request->input('lname');
            $email = $request->input('email');
            $tel = $request->input('tel');
            $user_permission = $request->input('permission');

            if ($username == "")
                return $this->returnError('[username] ไม่มีข้อมูล', 400);
            else if ($password == "")
                return $this->returnError('[password] ไม่มีข้อมูล', 400);
            else if ($fname == "")
                return $this->returnError('[fname] ไม่มีข้อมูล', 400);
            else if ($lname == "")
                return $this->returnError('[lname] ไม่มีข้อมูล', 400);
            else if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($tel == "")
                return $this->returnError('[tel] ไม่มีข้อมูล', 400);
            else if ($user_permission == "")
                return $this->returnError('[permission] ไม่มีข้อมูล', 400);

            $checkUser = User::where('username', $username)->first();

            if (!empty($checkUser))
                return $this->returnError('ชื่อเข้าใช้งานนี้มีอยู่ในระบบแล้ว', 400);

            DB::beginTransaction();

            $user = new User();
            $user->username = $username;
            $user->password = md5($password);
            $user->fname = $fname;
            $user->lname = $lname;
            $user->email = $email;
            $user->tel = $tel;

            if ($user->save()) {
                $permission = new Permission();
                $permission->user_id = $user->id;
                $permission->user = ($user_permission['user'] == true) ? 1 : 0;
                $permission->member = ($user_permission['member'] == true) ? 1 : 0;
                $permission->category = ($user_permission['category'] == true) ? 1 : 0;
                $permission->product = ($user_permission['product'] == true) ? 1 : 0;
                $permission->order = ($user_permission['order'] == true) ? 1 : 0;
                $permission->payment = ($user_permission['payment'] == true) ? 1 : 0;
                $permission->packing = ($user_permission['packing'] == true) ? 1 : 0;
                $permission->delivery = ($user_permission['delivery'] == true) ? 1 : 0;
                $permission->cancel = ($user_permission['cancel'] == true) ? 1 : 0;
                $permission->stock = ($user_permission['stock'] == true) ? 1 : 0;
                $permission->blog = ($user_permission['blog'] == true) ? 1 : 0;
                $permission->new = ($user_permission['new'] == true) ? 1 : 0;
                $permission->setting = ($user_permission['setting'] == true) ? 1 : 0;
                $permission->save();

                DB::commit();
                return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
            } else
                return $this->returnError('ดำเนินการลงทะเบียนล้มเหลว', 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_user_detail_back(Request $request)
    {
        try {

            $user_id = $request->input('id');

            if ($user_id == "")
                return $this->returnError('[user_id] ไม่มีข้อมูล', 400);

            $user = User::select('id', 'username', 'fname', 'lname', 'email', 'tel')
                ->where('id', $user_id)
                ->first();

            $permission = Permission::select('id', 'user', 'member', 'category', 'product', 'order', 'payment', 'packing', 'delivery', 'cancel', 'stock', 'blog', 'new', 'setting')
                ->where('user_id', $user_id)
                ->first();

            if (!empty($permission)) {

                $permission->user = ($permission->user == 1) ? true : false;
                $permission->member = ($permission->member == 1) ? true : false;
                $permission->category = ($permission->category == 1) ? true : false;
                $permission->product = ($permission->product == 1) ? true : false;
                $permission->order = ($permission->order == 1) ? true : false;
                $permission->payment = ($permission->payment == 1) ? true : false;
                $permission->packing = ($permission->packing == 1) ? true : false;
                $permission->delivery = ($permission->delivery == 1) ? true : false;
                $permission->cancel = ($permission->cancel == 1) ? true : false;
                $permission->stock = ($permission->stock == 1) ? true : false;
                $permission->blog = ($permission->blog == 1) ? true : false;
                $permission->new = ($permission->new == 1) ? true : false;
                $permission->setting = ($permission->setting == 1) ? true : false;
            }

            $user['permission'] = $permission;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $user);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_user_back(Request $request)
    {
        try {

            $user_id = $request->input('id');
            $fname = $request->input('fname');
            $lname = $request->input('lname');
            $email = $request->input('email');
            $tel = $request->input('tel');
            $user_permission = $request->input('permission');

            if ($fname == "")
                return $this->returnError('[fname] ไม่มีข้อมูล', 400);
            else if ($lname == "")
                return $this->returnError('[lname] ไม่มีข้อมูล', 400);
            else if ($email == "")
                return $this->returnError('[email] ไม่มีข้อมูล', 400);
            else if ($tel == "")
                return $this->returnError('[tel] ไม่มีข้อมูล', 400);
            else if ($user_permission == "")
                return $this->returnError('[permission] ไม่มีข้อมูล', 400);

            $user = User::where('id', $user_id)
                ->first();

            if (!empty($user)) {

                DB::beginTransaction();

                $user->fname = $fname;
                $user->lname = $lname;
                $user->email = $email;
                $user->tel = $tel;
                $user->update();

                $permission = Permission::where('user_id', $user_id)
                    ->first();

                if (!empty($permission)) {

                    $permission->user = ($user_permission['user'] == true) ? 1 : 0;
                    $permission->member = ($user_permission['member'] == true) ? 1 : 0;
                    $permission->category = ($user_permission['category'] == true) ? 1 : 0;
                    $permission->product = ($user_permission['product'] == true) ? 1 : 0;
                    $permission->order = ($user_permission['order'] == true) ? 1 : 0;
                    $permission->payment = ($user_permission['payment'] == true) ? 1 : 0;
                    $permission->packing = ($user_permission['packing'] == true) ? 1 : 0;
                    $permission->delivery = ($user_permission['delivery'] == true) ? 1 : 0;
                    $permission->cancel = ($user_permission['cancel'] == true) ? 1 : 0;
                    $permission->stock = ($user_permission['stock'] == true) ? 1 : 0;
                    $permission->blog = ($user_permission['blog'] == true) ? 1 : 0;
                    $permission->new = ($user_permission['new'] == true) ? 1 : 0;
                    $permission->setting = ($user_permission['setting'] == true) ? 1 : 0;
                    $permission->update();

                    DB::commit();
                    return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', []);
                } else
                    return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
            } else
                return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_user_permission_back(Request $request)
    {
        try {

            $user_id = $request->input('login_id');

            if ($user_id == "")
                return $this->returnError('[user_id] ไม่มีข้อมูล', 400);

            $permission = Permission::select('user', 'member', 'category', 'product', 'order', 'payment', 'packing', 'delivery', 'cancel', 'stock', 'blog', 'new', 'setting')
                ->where('user_id', $user_id)
                ->first();

            $permission->user = ($permission->user == 1) ? true : false;
            $permission->member = ($permission->member == 1) ? true : false;
            $permission->category = ($permission->category == 1) ? true : false;
            $permission->product = ($permission->product == 1) ? true : false;
            $permission->order = ($permission->order == 1) ? true : false;
            $permission->payment = ($permission->payment == 1) ? true : false;
            $permission->packing = ($permission->packing == 1) ? true : false;
            $permission->delivery = ($permission->delivery == 1) ? true : false;
            $permission->cancel = ($permission->cancel == 1) ? true : false;
            $permission->stock = ($permission->stock == 1) ? true : false;
            $permission->blog = ($permission->blog == 1) ? true : false;
            $permission->new = ($permission->new == 1) ? true : false;
            $permission->setting = ($permission->setting == 1) ? true : false;


            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $permission);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function get_alert_back(Request $request)
    {
        try {

            $data = [];

            $data['order_payment'] = Order::where('status', 'payment')
                ->count();

            $data['order_packing'] = Order::where('status', 'packing')
                ->count();

            $data['order_delivery'] = Order::where('status', 'delivery')
                ->count();

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
