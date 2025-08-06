<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;


class Shift extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'id',
        'user_id',
        'start_time',
        'end_time'
    ];

    // Shift.php
    public function user() {
        return $this->belongsTo(User::class);
    }
}
