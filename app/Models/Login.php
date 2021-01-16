<?php

namespace App\Models;

use Carbon\Carbon;
use \Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    public $key = "key";

    public function genToken($id, $name, $email, $admin)
    {
        $payload = array(
            "iss" => "truelinemed",
            "aud" => $id,
            "name" => $name,
            "email" => $email,
            "admin" => $admin,
            "iat" => Carbon::now()->timestamp,
            "exp" => Carbon::now()->timestamp + 86400,
            "nbf" => Carbon::now()->timestamp
        );

        $token = JWT::encode($payload, $this->key);
        return $token;
    }
}
