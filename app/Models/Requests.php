<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    use HasFactory;

    protected $fillable = [
      'id',
      'user_id',
      'type',
      'requested_date',
      'Reason',
      'status',
      'shift_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function shift() {
        return $this->belongsTo(Shift::class);
    }
}
