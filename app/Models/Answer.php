<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Eloquent
{
    protected $dates = ['created_at', 'updated_at'];
    protected $collection = 'answers';
    protected $primaryKey = '_id';
    protected $fillable = [
        'question_id',
        'user_id',
        'answer',
        'created_at',
        'updated_at'
    ];
}
