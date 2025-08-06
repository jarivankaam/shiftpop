<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Illuminate\Support\Str;
class Tenant extends BaseTenant
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id','slug', 'id'];
    protected $casts = [
    'tenant_id' => 'string',
];

      public function domains()
    {
        return $this->hasMany(\Stancl\Tenancy\Database\Models\Domain::class);
    }
}
