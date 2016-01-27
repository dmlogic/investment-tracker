<?php

namespace App\Models;

use App\Brokers\Fidelity;
use App\Brokers\HargreavesLandsdown;
use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id','name','cost_price','units_held','sale_price','broker','currency','url','price_units','chart_code','purchased_at','disposed_at'
    ];

    protected $dates = ['created_at','updated_at','purchased_at', 'disposed_at'];

    public function group()
    {
        return $this->belongsTo('\App\Models\Group');
    }

    public function getLiveDataAttribute()
    {
        switch($this->attributes['broker']) {
            case 'hl':
                $class = new HargreavesLandsdown($this->attributes);
                break;
            case 'fid':
                $class = new Fidelity($this->attributes);
                break;
            default:
                throw new \Exception('Invalid provider');
        }
        return $class;
    }

}
