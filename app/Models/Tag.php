<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Eloquent 
{
    use HasUuids, SoftDeletes;

    protected $dates = ['created_at', 'updated_at'];
    protected $attributes = [
        'is_system' => true,
    ];
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'is_system' => 'boolean'
    ];
    protected $collection = 'tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'description',
        'is_system',
        'created_at',
        'updated_at'
    ];
}
