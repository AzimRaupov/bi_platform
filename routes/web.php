<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Orhanerday\OpenAi\OpenAi;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('company.pages.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/test', function () {
    $open_ai = new OpenAi(env('OPENAI_API_KEY'));

    $chat = $open_ai->chat([
        'model' => env('GPT_MODEL'),
        'messages' => [
            [
                "role" => 'system',
                "content" => 'Ты учител'
            ],
            ['role' => 'user', 'content' => 'helo'],
        ],
        'temperature' => 1.0,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
    ]);
    dd($chat);

});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
