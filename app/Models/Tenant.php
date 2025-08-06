<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Tenant extends BaseTenant
{
       use HasUuids;
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
