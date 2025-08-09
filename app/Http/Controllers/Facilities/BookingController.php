
<?php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return view('facilities.bookings.index');
    }

    public function pools()
    {
        return view('facilities.bookings.pools');
    }

    public function halls()
    {
        return view('facilities.bookings.halls');
    }

    public function sports()
    {
        return view('facilities.bookings.sports');
    }
}
