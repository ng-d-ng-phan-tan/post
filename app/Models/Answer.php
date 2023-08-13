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
        'short_answer',
        'num_of_comments',
        'num_of_likes',
        'num_of_dislikes', 
        'answer_id',
        'answer_type',
        'reply_answer_id',
        'full_answer',
        'attached_image',
        'created_at',
        'updated_at'
    ];
}
