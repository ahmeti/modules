<?php

namespace Ahmeti\Modules\Core\Exceptions;

use App\Core;
use App\Response;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class Handler
{
    public function render($request, Exception $exception)
    {
        if( $exception->getMessage() == 'Unauthenticated.' ){

            if( $request->method() == 'GET' ){
                # Giriş yaqptıktan sonra yönlendirmek için url adresini cookie olarak kaydediyoruz.
                Cookie::queue('redirect_url', url()->full(), config('session.lifetime'));
            }

            // Oturum süresi dolmuş.
            session()->flush();

            if( $request->wantsJson() ){
                return Response::status(false)
                    ->message(__('Oturum süreniz dolmuş.'))
                    ->title(__('Oturum Süreniz Dolmuş'))
                    ->body(Core::alert(false, __('Oturum süreniz dolmuş. Lütfen tekrar giriş yapın.')))
                    ->jsCode('setTimeout(function(){ window.location.href = "'.url('/login?error=1').'"; }, 2000)')
                    ->page();
            }

            return redirect('/login?error=1');

        }else if( $exception->getMessage() == 'Permission-Error.' ){
            // Bu sayfayı görüntüleme yetkisi yok
            if( $request->wantsJson() ){
                return Response::status(false)
                    ->message(__('Bu işlem için yetkiniz bulunmuyor.'))
                    ->title(__('Erişiminiz Engellendi'))
                    ->body(Core::alert(false, __('Bu işlem için yetkiniz bulunmuyor. Yetki almak için sistem yöneticinize başvurun.')))
                    ->page();
            }

            return redirect()->route('page.error-403');

        }else if ( $this->isHttpException($exception) ) {
            Log::info(print_r([
                'url' => request()->url(),
                'params' => request()->all(),
                'company_id' => session()->get('company_id'),
                'user_id' => session()->get('user_id'),
            ], true));

        }else if( config('app.env') === 'production' && $exception instanceof QueryException){

            if( $request->wantsJson() && $request->isMethod('post') ){
                return Response::status(false)
                    ->message(__('Bir hata oluştu ve raporlandı. En kısa sürede bu hatanın tekrar oluşmasını engelleyeceğiz.'))
                    ->errorName('submit')
                    ->page();

            }elseif( $request->wantsJson() && $request->isMethod('get') ){
                return Response::status(false)
                    ->message(__('Bir hata oluştu ve raporlandı. En kısa sürede bu hatanın tekrar oluşmasını engelleyeceğiz.'))
                    ->title(__('Bir Hata Oluştu'))
                    ->body(Core::alert(false, __('Bir hata oluştu ve raporlandı. En kısa sürede bu hatanın tekrar oluşmasını engelleyeceğiz.')))
                    ->page();
            }
        }
    }
}
