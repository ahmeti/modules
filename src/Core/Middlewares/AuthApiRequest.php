<?php

namespace Ahmeti\Modules\Core\Middlewares;

use Closure;
use Illuminate\Support\Facades\DB;

class AuthApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if( empty($request->api_key) ){
            return response()->json([
                'status' => false,
                'message' => 'Api Key alanını boş bırakmayınız.'
            ]);
        }

        if( empty($request->api_secret) ){
            return response()->json([
                'status' => false,
                'message' => 'Api Secret alanını boş bırakmayınız.'
            ]);
        }

        if( empty($request->api_user_id) || (int)$request->api_user_id < 0 ){
            return response()->json([
                'status' => false,
                'message' => 'Api User Id alanını boş bırakmayınız.'
            ]);
        }

        $api = DB::table('companies')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->where('api_key', $request->api_key)
            ->where('api_secret', $request->api_secret)
            ->select(['id'])
            ->first();

        if( empty($api->id) ){
            return response()->json([
                'status' => false,
                'message' => 'Api Key veya Api Secret bilgileri geçersiz.'
            ]);
        }

        session()->put('company_id', $api->id);
        session()->put('user_id', $request->api_user_id);

        return $next($request);
    }
}
