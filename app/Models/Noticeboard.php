<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticeboard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'notice',
    ];
}
