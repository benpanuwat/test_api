<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class AddressController extends Controller
{
    public function get_address(Request $request)
    {
        try {

            $login_id = $request->input('login_id');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);

            $address = Address::select('id', 'name', 'tel', 'others', 'province_code', 'province', 'amphoe_code', 'amphoe', 'district_code', 'district', 'zipcode')
                ->where('member_id', $login_id)
                ->get();

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $address);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 405);
        }
    }

    public function update_address(Request $request)
    {
        try {

            $login_id = $request->input('login_id');
            $address = $request->input('address');

            if ($login_id == "")
                return $this->returnError('[login_id] ไม่มีข้อมูล', 400);

            DB::beginTransaction();

            $addressCheck = Address::where('member_id', $login_id)->get();

            foreach ($addressCheck as $add_c) {
                $statusDelete = true;
                foreach ($address as $add) {
                    if ($add['id'] == $add_c->id) {
                        $statusDelete = false;
                        break;
                    }
                }

                if ($statusDelete) {
                    $add_c->delete();
                }
            }

            foreach ($address as $add) {

                if ($add['id'] == "") {
                    $address = new Address();
                    $address->member_id = $login_id;
                    $address->name = $add['name'];
                    $address->tel = $add['tel'];
                    $address->others = $add['others'];
                    $address->province_code = $add['province_code'];
                    $address->province = $add['province'];
                    $address->amphoe_code = $add['amphoe_code'];
                    $address->amphoe = $add['amphoe'];
                    $address->district_code = $add['district_code'];
                    $address->district = $add['district'];
                    $address->zipcode = $add['zipcode'];
                    $address->save();
                } else {

                    $address = Address::where('id', $add['id'])->first();
                    if (!empty($address)) {
                        $address->name = $add['name'];
                        $address->tel = $add['tel'];
                        $address->others = $add['others'];
                        $address->province_code = $add['province_code'];
                        $address->province = $add['province'];
                        $address->amphoe_code = $add['amphoe_code'];
                        $address->amphoe = $add['amphoe'];
                        $address->district_code = $add['district_code'];
                        $address->district = $add['district'];
                        $address->zipcode = $add['zipcode'];
                        $address->update();
                    }
                }
            }

            DB::commit();
            return $this->returnSuccess('อัพเดตที่อยู่สำเร็จ', []);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError($e->getMessage(), 405);
        }
    }
}
