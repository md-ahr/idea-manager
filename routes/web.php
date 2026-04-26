<?php

declare(strict_types=1);

use App\Http\Controllers\IdeaController;
use App\Http\Controllers\IdeaImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\StepController;
use Illuminate\Support\Facades\Route;

$ideasRoute = 'ideas';

Route::redirect('/', "/$ideasRoute");

Route::get("/$ideasRoute", [IdeaController::class, 'index'])->name('idea.index')->middleware('auth');
Route::get("/$ideasRoute/{idea}", [IdeaController::class, 'show'])->name('idea.show')->middleware('auth');
Route::post("/$ideasRoute", [IdeaController::class, 'store'])->name('idea.store')->middleware('auth');
Route::patch("/$ideasRoute/{idea}", [IdeaController::class, 'update'])->name('idea.update')->middleware('auth');
Route::delete("/$ideasRoute/{idea}", [IdeaController::class, 'destroy'])->name('idea.destroy')->middleware('auth');

Route::delete("/$ideasRoute/{idea}/image", [IdeaImageController::class, 'destroy'])->name('idea.image.destroy')->middleware('auth');

Route::patch('/steps/{step}', [StepController::class, 'update'])->name('step.update')->middleware('auth');

Route::get('/register', [RegisterUserController::class, 'create'])->middleware('guest');
Route::post('/register', [RegisterUserController::class, 'store'])->middleware('guest');

Route::get('/login', [SessionsController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [SessionsController::class, 'store'])->middleware('guest');

Route::post('/logout', [SessionsController::class, 'destroy'])->middleware('auth');

Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
