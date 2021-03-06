<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
        'owner_id',
        'task_id',
        'dashboard_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'owner_id' => 'integer',
    ];


    /**
     *
     */
    public function user(){
        return $this->hasOne(User::class, 'id', 'owner_id');
    }
}
