<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    public function domains()
    {
        return $this->hasMany(\Stancl\Tenancy\Database\Models\Domain::class);
    }
}
