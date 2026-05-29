<?php

use App\Http\Controllers\Upload\UploadController;
use Illuminate\Support\Facades\Route;



Route::post('/upload_data',[UploadController::class,'upload'])->name('upload_data');


