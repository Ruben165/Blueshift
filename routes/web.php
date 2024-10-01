<?php

use App\Http\Controllers\BuyOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OtherController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellOrderController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ZoneController;
use App\Models\SellOrder;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function(){
    return redirect('/admin/dashboard');
});

Route::middleware(['auth', 'role:Superadmin'])->prefix('admin')->group(function () {
        Route::prefix('dashboard')->group(function() {
          Route::get('/', [DashboardController::class, 'newIndex'])->name('dashboard');
          Route::get('/assetGudang', [DashboardController::class, 'assetGudang'])->name('dashboard.asset-gudang');
          Route::get('/penjualan', [DashboardController::class, 'penjualan'])->name('dashboard.penjualan');
          Route::get('/penjualanPembelian', [DashboardController::class, 'penjualanPembelian'])->name('dashboard.penjualan-pembelian');
          
        });
  		
        Route::prefix('item')->group(function() {
          	Route::prefix('stock')->group(function() {
              Route::get('/view/{type?}/{stock?}', [ItemController::class, 'indexStock'])->name('item.stock.index');
        	});
        });
  		
});

Route::middleware(['auth', 'role:Superadmin'])->prefix('admin')->group(function () {
        
        Route::get('/getAllSupplier', [DashboardController::class, 'getAllSupplier'])->name('get-all-supplier');
        Route::get('/getAllType', [DashboardController::class, 'getAllType'])->name('get-all-type');

        Route::resource('note', NoteController::class)->except(['index', 'show', 'edit', 'update', 'destroy']);
        Route::prefix('note')->group(function () {
            Route::post('/update', [NoteController::class, 'update'])->name('note.update');
            Route::post('/destroy', [NoteController::class, 'destroy'])->name('note.destroy');
        });

        Route::resource('supplier', SupplierController::class)->except(['show', 'destroy']);
        Route::prefix('supplier')->group(function () {
            Route::get('/{supplier}/destroy', [SupplierController::class, 'destroy'])->name('supplier.destroy');
        });
        
        Route::resource('item', ItemController::class)->except(['create', 'show', 'destroy', 'edit',' update']);
        Route::prefix('item')->group(function () {
            Route::get('/{item}/destroy', [ItemController::class, 'destroy'])->name('item.destroy');
            Route::get('/export', [ItemController::class, 'exportSKU'])->name('item.sku.export');
            Route::prefix('stock')->group(function () {
                Route::get('/create', [ItemController::class, 'createStock'])->name('item.stock.create');
                Route::post('/', [ItemController::class, 'storeStock'])->name('item.stock.store');
                Route::get('/getItemBatch', [ItemController::class, 'getItemBatch'])->name('item.stock.get-item-batch');
                Route::get('/getItemShelf', [ItemController::class, 'getShelfs'])->name('item.stock.get-item-shelf');
                Route::post('/import', [ItemController::class, 'importStock'])->name('item.stock.import');
                Route::get('/export', [ItemController::class, 'exportStock'])->name('item.stock.export');
                Route::post('/exportMitra', [ItemController::class, 'exportStockMitra'])->name('item.stock.export-mitra');
                Route::get('/{stock}/edit', [ItemController::class, 'editStock'])->name('item.stock.edit');
                Route::patch('/{stock}/update', [ItemController::class, 'updateStock'])->name('item.stock.update');
                Route::get('/{stock}/destroy', [ItemController::class, 'destroyStock'])->name('item.stock.destroy');
                Route::get('/printBarcode', [ItemController::class, 'printBarcode'])->name('item.stock.print-barcode');
                Route::post('/printEachBarcode', [ItemController::class, 'printEachBarcode'])->name('item.stock.print-barcode-each');
                Route::post('/printAddBarcode', [ItemController::class, 'printAddBarcode'])->name('item.stock.print-barcode-add');
                Route::get('/getPartner', [ItemController::class, 'getPartner'])->name('item.stock.get-partner');
            });
        });

        Route::resource('buy', BuyOrderController::class)->except(['destroy']);
        Route::prefix('buy')->group(function () {
            Route::get('/{buy}/{status}/changeStatus', [BuyOrderController::class, 'changeStatus'])->name('buy.change-status');
            Route::get('/{buy}/exportExcel', [BuyOrderController::class, 'exportExcel'])->name('buy.export-excel');
            Route::get('/{buy}/exportPDF', [BuyOrderController::class, 'exportPDF'])->name('buy.export-pdf');
            Route::post('/{buy}/terimaPesanan', [BuyOrderController::class, 'terimaPesanan'])->name('buy.terima-pesanan');
            Route::get('/{buy}/printBarcode', [BuyOrderController::class, 'printBarcode'])->name('buy.print-barcode');
            Route::post('/uploadItems', [BuyOrderController::class, 'uploadItems'])->name('buy.upload-items');
            Route::post('/exportListDetail', [BuyOrderController::class, 'exportListDetail'])->name('buy.export-list-detail');
        });

        Route::resource('sell', SellOrderController::class)->except(['index', 'destroy']);
        Route::get('/getPartnerItems', [SellOrderController::class, 'getPartnerItems'])->name('sell.all-partner-items');
        Route::get('/getRequestConsignments', [SellOrderController::class, 'getRequestConsignment'])->name('sell.get-request-consignment');
        Route::get('/getFirstPartner', [SellOrderController::class, 'getFirstPartner'])->name('sell.get-first-partner');
        Route::get('/getConsignCode', [SellOrderController::class, 'getConsignCode'])->name('sell.get-consign-code');
        Route::prefix('sell')->group(function () {
            Route::post('/create/{type?}', [SellOrderController::class, 'create'])->name('sell.create.post');
            Route::get('/view/{type?}', [SellOrderController::class, 'index'])->name('sell.index');
            Route::post('/konsinyasi/exportHarga', [SellOrderController::class, 'exportHarga'])->name('sell.konsinyasi.export-harga');
            Route::prefix('permintaan')->group(function () {
                Route::get('/{type}', [SellOrderController::class, 'indexPermintaan'])->name('sell.permintaan.index');
                Route::get('/create/{type}', [SellOrderController::class, 'createPermintaan'])->name('sell.permintaan.create');
                Route::post('/store/{type}', [SellOrderController::class, 'storePermintaan'])->name('sell.permintaan.store');
                Route::get('/{consignmentRequest}/show/{type}', [SellOrderController::class, 'showPermintaan'])->name('sell.permintaan.show');
                Route::get('/{consignmentRequest}/edit/{type}', [SellOrderController::class, 'editPermintaan'])->name('sell.permintaan.edit');
                Route::post('/{consignmentRequest}/update', [SellOrderController::class, 'updatePermintaan'])->name('sell.permintaan.update');
                Route::post('/{consignmentRequest}/cancel', [SellOrderController::class, 'cancelPermintaan'])->name('sell.permintaan.cancel');
                Route::get('/{consignmentRequest}/exportExcel/{type}', [SellOrderController::class, 'exportExcelPermintaan'])->name('sell.permintaan.export-excel');
                Route::get('/{consignmentRequest}/exportPDF/{type}', [SellOrderController::class, 'exportPDFPermintaan'])->name('sell.permintaan.export-pdf');
                Route::post('/{consignmentRequest}/complete', [SellOrderController::class, 'completePermintaan'])->name('sell.permintaan.complete');
            });
            Route::get('/konsinyasi/checkout', [SellOrderController::class, 'indexCheckout'])->name('sell.konsinyasi.checkout.index');
            Route::post('/{sell}/{status}/changeStatus', [SellOrderController::class, 'changeStatus'])->name('sell.change-status');
            Route::get('/{sell}/exportExcel', [SellOrderController::class, 'exportExcel'])->name('sell.export-excel');
            Route::get('/{sell}/exportPDF', [SellOrderController::class, 'exportPDF'])->name('sell.export-pdf');
            Route::post('/{sell}/exportPDFInvoiceReguler', [SellOrderController::class, 'exportPDFInvoiceReguler'])->name('sell.export-pdf-reguler');
            Route::post('/{sell}/terimaPesanan', [SellOrderController::class, 'terimaPesanan'])->name('sell.terima-pesanan');
            Route::get('/{sell}/changeDue/{date}', [SellOrderController::class, 'changeDue'])->name('sell.change-due');
            Route::post('/so', [SellOrderController::class, 'so'])->name('sell.so');
            Route::post('/exportSisaStock', [SellOrderController::class, 'exportSisaStock'])->name('sell.so.export-sisa-stock');
            Route::post('/importSO', [SellOrderController::class, 'importSO'])->name('sell.so.import');
            Route::post('/printHasilSO', [SellOrderController::class, 'exportHasilSO'])->name('sell.print-hasil-so');
            Route::get('/{sell}/autofill', [SellOrderController::class, 'autofill'])->name('sell.autofill');
            Route::post('/{partner}/{sell}/storeSoAutofill', [SellOrderController::class, 'storeSoAutofill'])->name('sell.store-so-autofill');
            Route::post('/{sell}/uploadButirPembayaran', [SellOrderController::class, 'uploadButirPembayaran'])->name('sell.upload-bukti-bayar');
            Route::post('/exportStockRetur', [SellOrderController::class, 'exportStockRetur'])->name('sell.export-stock-retur');
            Route::post('/exportListDetail', [SellOrderController::class, 'exportListDetail'])->name('sell.export-list-detail');
        });
        
        Route::get('/getMitra', [PartnerController::class, 'getMitra'])->name('mitra.getMitra');
        Route::resource('mitra', PartnerController::class)->except(['destroy']);
        Route::prefix('/mitra')->group(function () {
            Route::get('/export/all', [PartnerController::class, 'exportAll'])->name('mitra.export-all');
            Route::get('/{mitra}/destroy', [PartnerController::class, 'destroy'])->name('mitra.destroy');
            Route::patch('/{mitra}/changeLogo', [PartnerController::class, 'changeLogo'])->name('mitra.changeLogo');
            Route::get('/{mitra}/items', [PartnerController::class, 'indexItems'])->name('mitra.items');
        });

        Route::prefix('master')->group(function () {
            Route::resource('/batch-mitra', GroupController::class)->except(['create', 'show', 'destroy']);
            Route::prefix('batch-mitra')->group(function () {
                Route::get('/{batch_mitra}/destroy', [GroupController::class, 'destroy'])->name('batch-mitra.destroy');
                Route::get('/{batch_mitra}/get-partners', [GroupController::class, 'getPartners'])->name('batch-mitra.get-partners');
                Route::get('/get-all-partners', [GroupController::class, 'getAllPartners'])->name('batch-mitra.get-all-partners');
                Route::post('/{batch_mitra}/add-partner', [GroupController::class, 'addPartner'])->name('batch-mitra.partner.store');
                Route::get('/{batch_mitra}/{mitra}/destroy', [GroupController::class, 'destroyPartner'])->name('batch-mitra.partner.destroy');
            });
            Route::resource('/wilayah', ZoneController::class)->except(['create', 'show', 'destroy']);
            Route::prefix('wilayah')->group(function () {
                Route::get('/{wilayah}/destroy', [ZoneController::class, 'destroy'])->name('wilayah.destroy');
                Route::get('/{wilayah}/get-partners', [ZoneController::class, 'getPartners'])->name('wilayah.get-partners');
                Route::get('/get-all-partners', [ZoneController::class, 'getAllPartners'])->name('wilayah.get-all-partners');
                Route::post('/{wilayah}/add-partner', [ZoneController::class, 'addPartner'])->name('wilayah.partner.store');
                Route::get('/{wilayah}/{mitra}/destroy', [ZoneController::class, 'destroyPartner'])->name('wilayah.partner.destroy');
            });

            Route::resource('/rak', ShelfController::class)->except(['create', 'show', 'destroy']);
            Route::prefix('rak')->group(function () {
                Route::get('/{rak}/destroy', [ShelfController::class, 'destroy'])->name('rak.destroy');
            });
        });

        // Route::prefix('others')->group(function () {
        //     Route::prefix('price')->group(function () {
        //         Route::get('/', [OtherController::class, 'indexPrice'])->name('other.price');
        //         Route::post('/', [OtherController::class, 'storePrice'])->name('other.price.store');
        //     });

        //     Route::prefix('stock')->group(function () {
        //         Route::get('/', [OtherController::class, 'indexStock'])->name('other.stock');
        //         Route::post('/', [OtherController::class, 'storeStock'])->name('other.stock.store');
        //     });
        // });

        Route::resource('role', RoleController::class)->except(['show', 'destroy']);
        Route::get('/role/{role}/destroy', [RoleController::class, 'destroy'])->name('role.destroy');
        
        Route::get('/user', [ProfileController::class, 'index'])->name('user.index');
        Route::get('/user/{user}/edit', [ProfileController::class, 'edit'])->name('user.edit');
        Route::patch('/user/{user}', [ProfileController::class, 'update'])->name('user.update');
});

require __DIR__.'/auth.php';
