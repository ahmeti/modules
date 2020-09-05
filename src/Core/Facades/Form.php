<?php

namespace Ahmeti\Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Form extends Facade {
    protected static function getFacadeAccessor() { return 'formservice'; }
}
