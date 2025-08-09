
<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        return view('inventory.index');
    }

    public function items()
    {
        return view('inventory.items');
    }

    public function stock()
    {
        return view('inventory.stock');
    }

    public function suppliers()
    {
        return view('inventory.suppliers');
    }
}
