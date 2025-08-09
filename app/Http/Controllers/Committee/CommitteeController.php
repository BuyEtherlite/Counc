
<?php

namespace App\Http\Controllers\Committee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function index()
    {
        return view('committee.index');
    }

    public function members()
    {
        return view('committee.members');
    }

    public function meetings()
    {
        return view('committee.meetings');
    }

    public function minutes()
    {
        return view('committee.minutes');
    }
}
