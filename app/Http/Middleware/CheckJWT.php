<?php

namespace App\Http\Middleware;

use \Firebase\JWT\JWT;
use Exception;
use Closure;

class CheckJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public $key = "vsf_key";

    public function handle($request, Closure $next)
    {
        try {
            $header = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $header);

            if (!$token) {
                return response()->json([
                    'code' => '401',
                    'status' => false,
                    'massage' => 'ไม่พบ Token',
                    'data' => []
                ], 200);
            }

            $payload = JWT::decode($token, $this->key, array('HS256'));
            $request->request->add(['login_id' => $payload->aud]);
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'code' => '401',
                'status' => false,
                'massage' => 'Token หมดอายุ',
                'data' => []
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'code' => '401',
                'status' => false,
                'massage' => 'ยืนยันตัวตนไม่ผ่าน',
                'data' => []
            ], 200);
        }

        return $next($request);
    }
}
