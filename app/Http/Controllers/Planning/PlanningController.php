
<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index()
    {
        return view('planning.index');
    }

    public function applications()
    {
        return view('planning.applications');
    }

    public function approvals()
    {
        return view('planning.approvals');
    }

    public function zoning()
    {
        return view('planning.zoning');
    }
}
