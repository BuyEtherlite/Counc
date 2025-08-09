
<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index()
    {
        return view('finance.index');
    }

    public function budget()
    {
        return view('finance.budget');
    }

    public function revenue()
    {
        return view('finance.revenue');
    }

    public function expenses()
    {
        return view('finance.expenses');
    }

    public function reports()
    {
        return view('finance.reports');
    }
}
