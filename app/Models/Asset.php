<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'path', 'task_id'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
