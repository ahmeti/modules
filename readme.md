#### 1. Install Laravel 
```
composer create-project --prefer-dist laravel/laravel new_project_name
```

#### 2. Install Laravel Ui
```
composer require laravel/ui
```

```
php artisan ui bootstrap --auth
npm install && npm run dev
```

#### 3. Install Ahmeti Modules
```
composer require ahmeti/modules
```

#### 4. Update Exceptions Handler Render Method
```php
# app/Exceptions/Handler.php

public function render($request, Throwable $exception)
{
    $result = (new \Ahmeti\Modules\Core\Exceptions\Handler)->render($request, $exception);
    if( $result ){ return $result; }

    return parent::render($request, $exception);
}
```

#### 5. Create App Facades
```
app/Core.php
app/Form.php
app/Response.php
```

#### 5. Change Default Route Service Provider Namespace
app/Providers/RouteServiceProvider.php
```php
protected $namespace = 'App\Modules';
```

#### 6. Move Auth Controllers to Modules
```
app/Modules/Auth/ConfirmPasswordController.php
app/Modules/AuthForgotPasswordController.php
app/Modules/Auth/LoginController.php
app/Modules/Auth/RegisterController.php
app/Modules/Auth/ResetPasswordController.php
app/Modules/Auth/VerificationController.php
```

#### 7. Update Login Controller
```php
public function showLoginForm()
{
    view()->addNamespace('Auth', app_path('Modules/Auth/Views'));
    return view('Auth::Login');
}

protected function authenticated(Request $request, $user)
{
    if( auth()->user() ) {
        session()->put('company_id', auth()->user()->company_id);
        session()->put('user_id', auth()->user()->id);
        session()->save();
    }

    if( cookie()->has('redirect_url') ){
        $redirectUrl = cookie()->get('redirect_url');
        return redirect($redirectUrl)->cookie(cookie()->forget('redirect_url'));
    }

    return redirect($this->redirectTo)->cookie(cookie()->forget('redirect_url'));
}
```

#### 8. Delete Defaults & Create Blades
```
Delete
resources/views/auth/login.blade.php
resources/views/auth/passwords/confirm.blade.php
resources/views/auth/passwords/email.blade.php
resources/views/auth/passwords/reset.blade.php
resources/views/auth/register.blade.php
resources/views/auth/verify.blade.php
resources/views/welcome.blade.php
```
```
Create
resources/views/layouts/auth.blade.php
resources/views/layouts/front.blade.php
app/Modules/Auth/Views/Login.blade.php
```

#### 9. Update Web Auth Routes
```php
Route::redirect('/', '/login', 301);

Route::group(['namespace' => 'Auth'], function () {

    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::post('logout', 'LoginController@logout')->name('logout');

});

Route::group(['middleware' => ['auth', 'auth.ajax']], function () {

    Route::get('/home', 'Home\HomeController@index')->name('home');

});
```
#### 10. Install ahmeti-core-js
```
npm i ahmeti-core-js --save-dev
```

#### 11. Delete Defaults Js Sass & Create Core Js Css
```
Delete
resources/js/app.js
resources/js/bootstrap.js
resources/sass/_variables.scss
resources/sass/app.scss
```
```
Create
resources/js/core.js
resources/css/core.css
```

#### 11. Update webpack.mix.js
```js
mix.copy('node_modules/ahmeti-core-js/dist/images', 'public/images');
mix.copy('node_modules/ahmeti-core-js/dist/fonts', 'public/fonts');

// ########################################################################

mix.scripts([
    // FRONT - JS
    'node_modules/ahmeti-core-js/dist/js/front.js',
], 'public/js/front.js').version();

mix.styles([
    // FRONT - CSS
    'node_modules/ahmeti-core-js/dist/css/front.css',
], 'public/css/front.css').version();


// ########################################################################

mix.scripts([
    // AUTH - JS
    'node_modules/ahmeti-core-js/dist/js/auth.js',
    'resources/js/core.js',
], 'public/js/auth.js').version();

mix.styles([
    // AUTH - CSS
    'node_modules/ahmeti-core-js/dist/css/auth.css',
    'resources/css/core.css',
], 'public/css/auth.css').version();
```

#### 12. Update package.json
```json
"devDependencies": {
    "ahmeti-core-js": "xxx",
    "cross-env": "xxx",
    "laravel-mix": "xxx"
}
```

#### 13. Set Auth Ajax Middleware
```php
protected $routeMiddleware = [
    // ...
    'auth.ajax' => \Ahmeti\Modules\Core\Middlewares\AuthAjaxRequest::class,
];
```