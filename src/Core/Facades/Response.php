<?php

namespace Ahmeti\Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Response extends Facade {
    protected static function getFacadeAccessor() { return 'responseservice'; }
}
