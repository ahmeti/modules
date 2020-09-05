<?php

namespace Ahmeti\Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Core extends Facade {
    protected static function getFacadeAccessor() { return 'coreservice'; }
}
