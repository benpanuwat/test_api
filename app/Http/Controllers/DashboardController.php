<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function get_dashboard_back(Request $request)
    {
        try {

            $data = [];

            $count = [];
            $count['user'] = User::count();
            $count['member'] = Member::count();
            $count['product'] = Product::count();
            $count['order'] = Order::count();
            $data['count'] = $count;

            $now_date =  date('Y-m');
            $start_date = date('Y-m-d', strtotime($now_date . " -3 month"));
            $end_date  = date('Y-m-d', strtotime($now_date . " +4 month -1 day"));

            $monthMap = [
                1 => 'มกราคม',
                2 => 'กุมภาพันธ์',
                3 => 'มีนาคม',
                4 => 'เมษายน',
                5 => 'พฤษภาคม',
                6 => 'มิถุนายน',
                7 => 'กรกฎาคม',
                8 => 'สิงหาคม',
                9 => 'กันยายน',
                10 => 'ตุลาคม',
                11 => 'พฤศจิกายน',
                12 => 'ธันวาคม',
            ];

            $month = intval(date('m', strtotime($start_date)));

            $new_month = array();
            $chart1 = array();

            for ($i = 0; $i < 6; $i++) {

                $list = array();
                $list['month'] = $month;
                $list['month_text'] = $monthMap[$month];
                $list['member_count'] = 0;
                $list['order_count'] = 0;

                array_push($new_month, $monthMap[$month]);
                array_push($chart1, $list);

                $month++;
                if ($month > 12)
                    $month = 1;
            }

            $members = DB::select("select month(created_at) AS month, count(*) AS count from members where created_at >= '" . $start_date . "' and created_at <= '" . $end_date . " 23:59:59' group by month(created_at)");

            foreach ($chart1 as &$c) {
                foreach ($members as &$mem) {
                    if ($c['month'] == $mem->month) {
                        $c['member_count'] = $mem->count;
                    }
                }
            }

            $orders = DB::select("select month(created_at) AS month, count(*) AS count from orders where created_at >= '" . $start_date . "' and created_at <= '" . $end_date . " 23:59:59' group by month(created_at)");

            foreach ($chart1 as &$c) {
                foreach ($orders as &$ord) {
                    if ($c['month'] == $ord->month) {
                        $c['order_count'] = $ord->count;
                    }
                }
            }

            $data['chart1'] = $chart1;

            $chart2 = DB::table('view_product_order_count')
            ->select('name AS label','count AS value')
            ->get();

            $data['chart2'] = $chart2;

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
