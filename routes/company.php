<?php

use App\Http\Controllers\AI\ChatController;
use App\Http\Controllers\Upload\UploadController;
use Illuminate\Support\Facades\Route;

Route::post('/upload_data',[UploadController::class,'upload'])->name('upload_data');


Route::get('/dashboard', function () {
    return view('company.pages.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('chat')->name('chat.')->group(function(){
    Route::post('/',[ChatController::class,'store'])->name('store');
    Route::post('/{chat}/message',[ChatController::class,'message'])->name('message');
    Route::get('/{chat}',[ChatController::class,'show'])->name('show');
});
