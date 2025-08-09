
<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyManagementController extends Controller
{
    public function index()
    {
        return view('property.index');
    }

    public function valuations()
    {
        return view('property.valuations');
    }

    public function leases()
    {
        return view('property.leases');
    }

    public function landRecords()
    {
        return view('property.land-records');
    }
}
