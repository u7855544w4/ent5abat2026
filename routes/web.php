<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('voters', App\Http\Controllers\VoterController::class)->except(['create', 'edit']);

Route::get('/families', [App\Http\Controllers\FamilyController::class, 'index'])->name('families.index');
Route::post('/families', [App\Http\Controllers\FamilyController::class, 'store'])->name('families.store');
Route::put('/families/{id}', [App\Http\Controllers\FamilyController::class, 'update'])->name('families.update');
Route::delete('/families/{id}', [App\Http\Controllers\FamilyController::class, 'destroy'])->name('families.destroy');

Route::resource('committees', App\Http\Controllers\CommitteeController::class)->except(['create', 'edit']);

Route::get('/tasks', [App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks/assign', [App\Http\Controllers\TaskController::class, 'assign'])->name('tasks.assign');

Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');

Route::get('/import', [App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
Route::post('/import', [App\Http\Controllers\ImportController::class, 'import'])->name('import.import');
Route::get('/template', [App\Http\Controllers\ImportController::class, 'template'])->name('import.template');