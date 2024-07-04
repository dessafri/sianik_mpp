<?php
use App\Http\Controllers\Apidata;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('/endpoint', [ApiData::class, 'index']);
    // tambahkan route lainnya sesuai kebutuhan
});

?>