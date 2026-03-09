<?php

use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::post('/company', [CompanyController::class, 'upsert']);

Route::get('/company/{edrpou}/versions', [CompanyController::class, 'versions'])
    ->where('edrpou', '[0-9]{1,10}');
