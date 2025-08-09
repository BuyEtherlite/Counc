
<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function index()
    {
        return view('administration.crm.index');
    }

    public function customers()
    {
        return view('administration.crm.customers');
    }

    public function serviceRequests()
    {
        return view('administration.crm.service-requests');
    }

    public function communications()
    {
        return view('administration.crm.communications');
    }
}
