<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentDiscussion extends Model
{
    public $timestamps = false;

    protected $table = 'parent_discussion';
    protected $primaryKey = 'parent_id';

    protected $fillable = [
        'parent_id',
    ];

    public function discussion()
    {
        return $this->hasMany(Discussion::class, 'parent_id', 'parent_id');
    }
}
