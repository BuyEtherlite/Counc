
<?php

namespace App\Http\Controllers\Cemeteries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CemeteryController extends Controller
{
    public function index()
    {
        return view('cemeteries.index');
    }

    public function graveRegister()
    {
        return view('cemeteries.grave-register');
    }

    public function burials()
    {
        return view('cemeteries.burials');
    }

    public function maintenance()
    {
        return view('cemeteries.maintenance');
    }
}
