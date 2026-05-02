<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    protected $fillable = ['image_url', 'title', 'subtitle', 'order', 'is_active'];
}
