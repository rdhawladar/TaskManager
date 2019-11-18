<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id', 'user_id', 'title', 'points', 'is_done', 'edge_path'
    ];

    #each subtask belong to a parent
    public function parent()
    {
        return $this->belongsToOne(static::class, 'parent_id');
    }

    #each tasks might have multiple subtasks/children
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }
    
    # recursive, all ascendants
    public function parentRecursive()
    {
        return $this->parent()->with('parentRecursive');
    }

    # recursive, loads all descendants
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function replies(){
        return $this->children(static::class, 'is_done')->where('is_done', 1);
    }
}
