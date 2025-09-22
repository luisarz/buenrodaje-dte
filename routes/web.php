<?php

use App\Http\Controllers\AbonoCompraController;
use App\Http\Controllers\AdjustementInventory;
use App\Http\Controllers\ContingencyController;
use App\Http\Controllers\DTEController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\hoja;
use App\Http\Controllers\InventoryReport;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SenEmailDTEController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
})->name('home');


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/ejecutar', [hoja::class, 'ejecutar']);
//Route::get('/migrar-branch', [hoja::class, 'replicarInventarioAndBranch']);
Route::get('/generarDTE/{idVenta}', [DTEController::class, 'generarDTE'])->middleware(['auth'])->name('generarDTE');
Route::get('/sendAnularDTE/{idVenta}', [DTEController::class, 'anularDTE'])->middleware(['auth'])->name('sendAnularDTE');
Route::get('/printDTETicket/{idVenta}', [DTEController::class, 'printDTETicket'])->middleware(['auth'])->name('printDTETicket');
Route::get('/printDTEPdf/{idVenta}', [DTEController::class, 'printDTEPdf'])->middleware(['auth'])->name('printDTEPdf');
Route::get('/sendDTE/{idVenta}', [SenEmailDTEController::class, 'SenEmailDTEController'])->middleware(['auth'])->name('sendDTE');
Route::get('/ordenPrint/{idVenta}', [OrdenController::class, 'generarPdf'])->middleware(['auth'])->name('ordenGenerarPdf');
Route::get('/ordenPrintTicket/{idVenta}', [OrdenController::class, 'ordenGenerarTicket'])->middleware(['auth'])->name('ordenGenerarTicket');
Route::get('/closeCashboxPrint/{idCasboxClose}', [OrdenController::class, 'closeClashBoxPrint'])->middleware(['auth'])->name('closeClashBoxPrint');
Route::get('/admin/sales/{idVenta}/edit', [OrdenController::class, 'billingOrder'])->middleware(['auth'])->name('billingOrder');
Route::get('/printQuote/{idVenta}', [QuoteController::class, 'printQuote'])->name('printQuote');
//Traslados
Route::get('/printTransfer/{idTransfer}', [TransferController::class, 'printTransfer'])->middleware(['auth'])->name('printTransfer');
Route::get('/employee/sales/{id_employee}/{star_date}/{end_date}', [EmployeesController::class, 'sales'])->middleware(['auth'])->name('employee.sales');
Route::get('/employee/sales-work/{id_employee}/{star_date}/{end_date}', [EmployeesController::class, 'salesWork'])->middleware(['auth'])->name('employee.sales-work');
Route::get('/employee/test/{id_employee}/{star_date}/{end_date}', [EmployeesController::class, 'dataEmployee'])->middleware(['auth'])->name('employee.sales-test');

//Libros de excel
Route::get('/sale/iva/{doctype}/{starDate}/{endDate}',[ReportsController::class,'saleReportFact']);
Route::get('/sale/iva/libro/fact/{starDate}/{endDate}',[ReportsController::class,'saleReportFact']);
Route::get('/sale/iva/libro/ccf/{starDate}/{endDate}',[ReportsController::class,'saleReportCCF']);
Route::get('/sale/iva/libro/ccf/{startDate}/{endDate}', [ReportsController::class, 'saleReportCCF'])->name('sale.iva.libro.ccf');
Route::get('/contingency/{description}',[ContingencyController::class,'contingencyDTE'])->middleware(['auth'])->name('contingency');
Route::get('/contingency_close/{uuid_contingence}',[ContingencyController::class,'contingencyCloseDTE'])->middleware(['auth'])->name('contingencyClose');
//ZIP
Route::get('/sale/json/{starDate}/{endDate}',[ReportsController::class,'downloadJson']);
Route::get('/sale/pdf/{starDate}/{endDate}',[ReportsController::class,'downloadPdf']);
//Entrada Salia
//Route::get('/printSalida/{idsalida}', [DTEController::class, 'printDTETicket'])->middleware(['auth'])->name('printSalida');
Route::get('/salidaPrintTicket/{id}', [AdjustementInventory::class, 'salidaPrintTicket'])->middleware(['auth'])->name('salidaPrintTicket');

//Abonos
Route::get('/abono/print/{id_abono}', [AbonoCompraController::class, 'printAbono'])->middleware(['auth'])->name('abono.print');
Route::get('/payment/print/{id_payment}', [AbonoCompraController::class, 'printPayment'])->middleware(['auth'])->name('payment.print');


//Inventory
Route::get('/inventory/report/{starDate}/{endDate}',[InventoryReport::class,'inventoryReportExport'])->name('inventor.report');
Route::get('/inventory/report/{code}/{starDate}/{endDate}',[InventoryReport::class,'inventoryMovimentReportExport'])->name('inventor.moviment.report');


require __DIR__ . '/auth.php';
