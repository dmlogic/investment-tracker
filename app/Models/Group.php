<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','sort_order',
    ];

    public function funds($orderBy = 'name')
    {
        return $this->hasMany('App\Models\Fund')->whereNull('disposed_at')->orderBy($orderBy);
    }
}
