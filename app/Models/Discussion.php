<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    public $timestamps = false;

    protected $table = 'discussion';
    protected $primaryKey = 'discussion_id';

    protected $fillable = [
        'user_id',
        'parent_id',
        'quiz_id',
        'context'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function quizzs()
    {
        return $this->belongsTo(Quizz::class, 'quiz_id', 'quiz_id');
    }

    public function parent()
    {
        return $this->belongsTo(Discussion::class, 'parent_id', 'discussion_id');
    }

    public function children()
    {
        return $this->hasMany(Discussion::class, 'parent_id', 'discussion_id')->with('children');;
    }
}
