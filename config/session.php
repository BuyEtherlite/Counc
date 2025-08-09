<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\SubMenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\PaymentTermController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckInstallation;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Role as RoleMiddleware;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Installation Routes
Route::get('/install', [InstallationController::class, 'showForm'])->middleware(CheckInstallation::class)->name('install.form');
Route::post('/install', [InstallationController::class, 'install'])->middleware(CheckInstallation::class)->name('install.process');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware([Authenticate::class])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::get('/{setting}/edit', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/{setting}', [SettingController::class, 'update'])->name('settings.update');
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Roles
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permissions
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // Menu Management
    Route::prefix('menu-management')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('menu.index');
        Route::get('/create', [MenuController::class, 'create'])->name('menu.create');
        Route::post('/', [MenuController::class, 'store'])->name('menu.store');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('menu.edit');
        Route::put('/{menu}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');

        // SubMenus
        Route::prefix('/{menu}/submenus')->group(function () {
            Route::get('/', [SubMenuController::class, 'index'])->name('submenu.index');
            Route::get('/create', [SubMenuController::class, 'create'])->name('submenu.create');
            Route::post('/', [SubMenuController::class, 'store'])->name('submenu.store');
            Route::get('/{submenu}/edit', [SubMenuController::class, 'edit'])->name('submenu.edit');
            Route::put('/{submenu}', [SubMenuController::class, 'update'])->name('submenu.update');
            Route::delete('/{submenu}', [SubMenuController::class, 'destroy'])->name('submenu.destroy');

            // Menu Items
            Route::prefix('/{submenu}/items')->group(function () {
                Route::get('/', [MenuItemController::class, 'index'])->name('menuitem.index');
                Route::get('/create', [MenuItemController::class, 'create'])->name('menuitem.create');
                Route::post('/', [MenuItemController::class, 'store'])->name('menuitem.store');
                Route::get('/{menuitem}/edit', [MenuItemController::class, 'edit'])->name('menuitem.edit');
                Route::put('/{menuitem}', [MenuItemController::class, 'update'])->name('menuitem.update');
                Route::delete('/{menuitem}', [MenuItemController::class, 'destroy'])->name('menuitem.destroy');
            });
        });
    });

    // Departments
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    });

    // Designations
    Route::prefix('designations')->group(function () {
        Route::get('/', [DesignationController::class, 'index'])->name('designations.index');
        Route::get('/create', [DesignationController::class, 'create'])->name('designations.create');
        Route::post('/', [DesignationController::class, 'store'])->name('designations.store');
        Route::get('/{designation}/edit', [DesignationController::class, 'edit'])->name('designations.edit');
        Route::put('/{designation}', [DesignationController::class, 'update'])->name('designations.update');
        Route::delete('/{designation}', [DesignationController::class, 'destroy'])->name('designations.destroy');
    });

    // Branches
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Companies
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });

    // Currencies
    Route::prefix('currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('/create', [CurrencyController::class, 'create'])->name('currencies.create');
        Route::post('/', [CurrencyController::class, 'store'])->name('currencies.store');
        Route::get('/{currency}/edit', [CurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');
    });

    // Taxes
    Route::prefix('taxes')->group(function () {
        Route::get('/', [TaxController::class, 'index'])->name('taxes.index');
        Route::get('/create', [TaxController::class, 'create'])->name('taxes.create');
        Route::post('/', [TaxController::class, 'store'])->name('taxes.store');
        Route::get('/{tax}/edit', [TaxController::class, 'edit'])->name('taxes.edit');
        Route::put('/{tax}', [TaxController::class, 'update'])->name('taxes.update');
        Route::delete('/{tax}', [TaxController::class, 'destroy'])->name('taxes.destroy');
    });

    // Payment Terms
    Route::prefix('payment-terms')->group(function () {
        Route::get('/', [PaymentTermController::class, 'index'])->name('payment-terms.index');
        Route::get('/create', [PaymentTermController::class, 'create'])->name('payment-terms.create');
        Route::post('/', [PaymentTermController::class, 'store'])->name('payment-terms.store');
        Route::get('/{paymentTerm}/edit', [PaymentTermController::class, 'edit'])->name('payment-terms.edit');
        Route::put('/{paymentTerm}', [PaymentTermController::class, 'update'])->name('payment-terms.update');
        Route::delete('/{paymentTerm}', [PaymentTermController::class, 'destroy'])->name('payment-terms.destroy');
    });

    // Items
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('items.index');
        Route::get('/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/', [ItemController::class, 'store'])->name('items.store');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    });

    // Suppliers
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // Customers
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Purchase Orders
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
        Route::get('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::get('/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    });

    // Sale Orders
    Route::prefix('sale-orders')->group(function () {
        Route::get('/', [SaleOrderController::class, 'index'])->name('sale-orders.index');
        Route::get('/create', [SaleOrderController::class, 'create'])->name('sale-orders.create');
        Route::post('/', [SaleOrderController::class, 'store'])->name('sale-orders.store');
        Route::get('/{saleOrder}/edit', [SaleOrderController::class, 'edit'])->name('sale-orders.edit');
        Route::put('/{saleOrder}', [SaleOrderController::class, 'update'])->name('sale-orders.update');
        Route::delete('/{saleOrder}', [SaleOrderController::class, 'destroy'])->name('sale-orders.destroy');
        Route::get('/{saleOrder}/approve', [SaleOrderController::class, 'approve'])->name('sale-orders.approve');
        Route::get('/{saleOrder}/reject', [SaleOrderController::class, 'reject'])->name('sale-orders.reject');
    });

    // Invoices
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/{invoice}/view', [InvoiceController::class, 'view'])->name('invoices.view');
        Route::get('/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.add-payment');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'storePayment'])->name('invoices.store-payment');
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/sales-summary', [ReportController::class, 'salesSummary'])->name('reports.sales-summary');
        Route::get('/purchase-summary', [ReportController::class, 'purchaseSummary'])->name('reports.purchase-summary');
        Route::get('/customer-statement', [ReportController::class, 'customerStatement'])->name('reports.customer-statement');
        Route::get('/supplier-statement', [ReportController::class, 'supplierStatement'])->name('reports.supplier-statement');
        Route::get('/item-ledger', [ReportController::class, 'itemLedger'])->name('reports.item-ledger');
        Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('reports.user-activity');
    });

    // Route group for only admin users
    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        // Add any admin-specific routes here
    });
});
</original>
The session configuration has been updated to use the file driver and the session file path has been set.
<replit_final_file>
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\SubMenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\PaymentTermController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckInstallation;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Role as RoleMiddleware;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Installation Routes
Route::get('/install', [InstallationController::class, 'showForm'])->middleware(CheckInstallation::class)->name('install.form');
Route::post('/install', [InstallationController::class, 'install'])->middleware(CheckInstallation::class)->name('install.process');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware([Authenticate::class])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::get('/{setting}/edit', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/{setting}', [SettingController::class, 'update'])->name('settings.update');
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Roles
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permissions
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // Menu Management
    Route::prefix('menu-management')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('menu.index');
        Route::get('/create', [MenuController::class, 'create'])->name('menu.create');
        Route::post('/', [MenuController::class, 'store'])->name('menu.store');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('menu.edit');
        Route::put('/{menu}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');

        // SubMenus
        Route::prefix('/{menu}/submenus')->group(function () {
            Route::get('/', [SubMenuController::class, 'index'])->name('submenu.index');
            Route::get('/create', [SubMenuController::class, 'create'])->name('submenu.create');
            Route::post('/', [SubMenuController::class, 'store'])->name('submenu.store');
            Route::get('/{submenu}/edit', [SubMenuController::class, 'edit'])->name('submenu.edit');
            Route::put('/{submenu}', [SubMenuController::class, 'update'])->name('submenu.update');
            Route::delete('/{submenu}', [SubMenuController::class, 'destroy'])->name('submenu.destroy');

            // Menu Items
            Route::prefix('/{submenu}/items')->group(function () {
                Route::get('/', [MenuItemController::class, 'index'])->name('menuitem.index');
                Route::get('/create', [MenuItemController::class, 'create'])->name('menuitem.create');
                Route::post('/', [MenuItemController::class, 'store'])->name('menuitem.store');
                Route::get('/{menuitem}/edit', [MenuItemController::class, 'edit'])->name('menuitem.edit');
                Route::put('/{menuitem}', [MenuItemController::class, 'update'])->name('menuitem.update');
                Route::delete('/{menuitem}', [MenuItemController::class, 'destroy'])->name('menuitem.destroy');
            });
        });
    });

    // Departments
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    });

    // Designations
    Route::prefix('designations')->group(function () {
        Route::get('/', [DesignationController::class, 'index'])->name('designations.index');
        Route::get('/create', [DesignationController::class, 'create'])->name('designations.create');
        Route::post('/', [DesignationController::class, 'store'])->name('designations.store');
        Route::get('/{designation}/edit', [DesignationController::class, 'edit'])->name('designations.edit');
        Route::put('/{designation}', [DesignationController::class, 'update'])->name('designations.update');
        Route::delete('/{designation}', [DesignationController::class, 'destroy'])->name('designations.destroy');
    });

    // Branches
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Companies
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });

    // Currencies
    Route::prefix('currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('/create', [CurrencyController::class, 'create'])->name('currencies.create');
        Route::post('/', [CurrencyController::class, 'store'])->name('currencies.store');
        Route::get('/{currency}/edit', [CurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');
    });

    // Taxes
    Route::prefix('taxes')->group(function () {
        Route::get('/', [TaxController::class, 'index'])->name('taxes.index');
        Route::get('/create', [TaxController::class, 'create'])->name('taxes.create');
        Route::post('/', [TaxController::class, 'store'])->name('taxes.store');
        Route::get('/{tax}/edit', [TaxController::class, 'edit'])->name('taxes.edit');
        Route::put('/{tax}', [TaxController::class, 'update'])->name('taxes.update');
        Route::delete('/{tax}', [TaxController::class, 'destroy'])->name('taxes.destroy');
    });

    // Payment Terms
    Route::prefix('payment-terms')->group(function () {
        Route::get('/', [PaymentTermController::class, 'index'])->name('payment-terms.index');
        Route::get('/create', [PaymentTermController::class, 'create'])->name('payment-terms.create');
        Route::post('/', [PaymentTermController::class, 'store'])->name('payment-terms.store');
        Route::get('/{paymentTerm}/edit', [PaymentTermController::class, 'edit'])->name('payment-terms.edit');
        Route::put('/{paymentTerm}', [PaymentTermController::class, 'update'])->name('payment-terms.update');
        Route::delete('/{paymentTerm}', [PaymentTermController::class, 'destroy'])->name('payment-terms.destroy');
    });

    // Items
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('items.index');
        Route::get('/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/', [ItemController::class, 'store'])->name('items.store');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    });

    // Suppliers
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // Customers
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Purchase Orders
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
        Route::get('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::get('/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    });

    // Sale Orders
    Route::prefix('sale-orders')->group(function () {
        Route::get('/', [SaleOrderController::class, 'index'])->name('sale-orders.index');
        Route::get('/create', [SaleOrderController::class, 'create'])->name('sale-orders.create');
        Route::post('/', [SaleOrderController::class, 'store'])->name('sale-orders.store');
        Route::get('/{saleOrder}/edit', [SaleOrderController::class, 'edit'])->name('sale-orders.edit');
        Route::put('/{saleOrder}', [SaleOrderController::class, 'update'])->name('sale-orders.update');
        Route::delete('/{saleOrder}', [SaleOrderController::class, 'destroy'])->name('sale-orders.destroy');
        Route::get('/{saleOrder}/approve', [SaleOrderController::class, 'approve'])->name('sale-orders.approve');
        Route::get('/{saleOrder}/reject', [SaleOrderController::class, 'reject'])->name('sale-orders.reject');
    });

    // Invoices
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/{invoice}/view', [InvoiceController::class, 'view'])->name('invoices.view');
        Route::get('/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.add-payment');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'storePayment'])->name('invoices.store-payment');
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/sales-summary', [ReportController::class, 'salesSummary'])->name('reports.sales-summary');
        Route::get('/purchase-summary', [ReportController::class, 'purchaseSummary'])->name('reports.purchase-summary');
        Route::get('/customer-statement', [ReportController::class, 'customerStatement'])->name('reports.customer-statement');
        Route::get('/supplier-statement', [ReportController::class, 'supplierStatement'])->name('reports.supplier-statement');
        Route::get('/item-ledger', [ReportController::class, 'itemLedger'])->name('reports.item-ledger');
        Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('reports.user-activity');
    });

    // Route group for only admin users
    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        // Add any admin-specific routes here
    });
});