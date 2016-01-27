<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\Group;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function homepage()
    {
        return View::make('funds',['groups' => Group::with('funds')->get()]);
    }

    public function fund($groupId,$fundId)
    {
        $fund = Fund::where('group_id','=',$groupId)->findOrFail($fundId);
        return response()->json( $fund->live_data->getData() );
    }
}
