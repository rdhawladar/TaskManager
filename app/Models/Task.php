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
        'parent_id', 'user_id', 'title', 'points', 'is_done',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    //each category might have multiple children
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id')->orderBy('id');
    }
    
    // all ascendants
    public function parentRecursive()
    {
        return $this->parent()->with('parentRecursive');
    }

    // recursive, loads all descendants
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
        // which is equivalent to:
        // return $this->hasMany('Survey', 'parent')->with('childrenRecursive);
    }
}
