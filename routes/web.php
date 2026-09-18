<?php

use Illuminate\Support\Facades\Route;

//use Illuminate\Support\Facades\Artisan;
//Artisan::call('cache:clear');
//Artisan::call('route:clear');
//Artisan::call('config:clear');
//Artisan::call('view:clear');
//
Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
Route::get('/', function () {
    return view('welcome');
});
Route::get('/atmosphera/success', function () {
    return view('payment.success');
})->name('payment.success');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/atmosphera', [App\Http\Controllers\HomeController::class, 'atmosphera'])->name('atmosphera');
Route::get('/liodovii', [App\Http\Controllers\HomeController::class, 'liodovii'])->name('liodovii');

Route::get('/atmosphera/rules-and-conditions', [App\Http\Controllers\HomeController::class, 'rules_and_conditions'])->name('rules-and-conditions');
Route::get('/atmosphera/rules-for-turning-penny-money', [App\Http\Controllers\HomeController::class, 'rules_for_turning'])->name('rules-for-turning-penny-money');



Route::post('sendPhone',[\App\Http\Controllers\PhoneController::class,'sendPhone']);
Route::post('sendCode',[\App\Http\Controllers\PhoneController::class,'sendCode']);

Route::post('/pdf_donvald', [App\Http\Controllers\PdfController::class, 'pdf_donvald'])->name('pdf_donvald');
Route::post('/way', [App\Http\Controllers\WayPayController::class, 'way'])->name('way');



Route::get('/way_test', [App\Http\Controllers\TestController::class, 'way_test'])->name('qr');

/*
 *
 */
Route::get('/api/getSettingsClub', [App\Http\Controllers\ApiController::class, 'getSettingsClub']);
Route::get('/api/getTablesBookings', [App\Http\Controllers\ApiController::class, 'getTablesBookings']);
Route::get('/api/getBokingsDops', [App\Http\Controllers\ApiController::class, 'getBokingsDops']);
Route::post('/api/getTotalBookings', [App\Http\Controllers\ApiController::class, 'getTotalBookings']);
Route::post('/api/addBooking', [App\Http\Controllers\ApiController::class, 'addBooking']);
Route::post('/api/payBooking', [App\Http\Controllers\ApiController::class, 'payBooking']);
Route::get('/api/bookings', [App\Http\Controllers\ApiController::class, 'bookings']);
Route::post('/api/removeBooking', [App\Http\Controllers\ApiController::class, 'removeBooking']);
Route::post('/api/updateClearOrder', [App\Http\Controllers\ApiController::class, 'updateClearOrder']);
Route::post('/api/getCheck', [App\Http\Controllers\ApiController::class, 'getCheck']);
