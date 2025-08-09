
<?php

namespace App\Http\Controllers\Water;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WaterController extends Controller
{
    public function index()
    {
        return view('water.index');
    }

    public function connections()
    {
        return view('water.connections');
    }

    public function metering()
    {
        return view('water.metering');
    }

    public function billing()
    {
        return view('water.billing');
    }
}
