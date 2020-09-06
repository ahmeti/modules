<?php

namespace Ahmeti\Modules\Core\Traits;

use App\Core;
use Illuminate\Database\Eloquent\Builder;

trait CompanyTrait {

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $query->where('company_id', Core::companyId())
            ->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }
}
