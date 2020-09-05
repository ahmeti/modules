<?php

namespace Ahmeti\Modules\Core\Middlewares;

use App\Core;
use Closure;
use Illuminate\Support\Facades\App;

class AuthAjaxRequest
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
        $firstSegments = ['home'];

        if( auth()->check() ){

            if( auth()->user()->status != 1 ){
                // If Blocked User
                abort(403, 'Unauthenticated.');
            }

            if( isset(auth()->user()->lang) && in_array(auth()->user()->lang, ['tr', 'en']) ){
                App::setLocale(auth()->user()->lang);
            }
        }

        if ( Core::companyId() < 1 && $request->segment(1) === 'home'){
            return redirect('login');
        }

        if( ! $request->ajax() && $request->input('ajax') !== 'no' &&  ! in_array($request->segment(1), $firstSegments) ){
            return redirect('home')->with('ajax-request', url()->full());
        }

        return $next($request);
    }
}
