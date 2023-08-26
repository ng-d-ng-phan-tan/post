<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Eloquent
{
    use SoftDeletes;

    protected $dates = ['approved_at', 'created_at', 'updated_at'];
    protected $collection = 'questions';
    protected $primaryKey = '_id';
    protected $attributes = [
        'num_of_likes' => 0,
        'num_of_dislikes' => 0,
        'num_of_answers' => 0,
        'is_approved' => false,
        'isReported' => false,
        'num_of_reported' => 0,
        'user_approved_id' => null,
        'interaction' => [],
    ];
    protected $fillable = [
        'title',
        'body',
        'num_of_likes',
        'num_of_dislikes',
        'num_of_answers',
        'questioner_id',
        'is_approved',
        'isReported',
        'num_of_reported',
        'user_approved_id',
        'interaction',
        'tags',
        'created_by',
        'approved_at',
        'created_at',
        'updated_at'
    ];
}
