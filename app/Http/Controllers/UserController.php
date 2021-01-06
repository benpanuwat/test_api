<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function table_user_back(Request $request)
    {
        try {

            // $columns = $request->input('columns');
            // $length = $request->input('length');
            // $order = $request->input('order');
            // $search = $request->input('search');
            // $start = $request->input('start');
            // $page = $start / $length + 1;

            // $col = array('use_id');

            // $u = DB::table('users')
            //     ->select($col)
            //     ->orderby($col[$order[0]['column']], $order[0]['dir']);

            // if ($search['value'] != '' && $search['value'] != null) {
            //     foreach ($col as &$c) {
            //         $u->orWhere($c, 'LIKE', '%' . $search['value'] . '%');
            //     }
            // }

            // $user = $u->paginate($length, ['*'], 'page', $page);

            $user = [];
            $user = DB::table('users')->get();
            return response()->json($user);

        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
