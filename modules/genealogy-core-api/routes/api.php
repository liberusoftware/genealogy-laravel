<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Liberu\Genealogy\GenealogyCore\Api\Http\Controllers\TreeController;

Route::get('/', [TreeController::class, 'index'])->name('genealogy.core.index');
Route::get('/{tree}', [TreeController::class, 'show'])->name('genealogy.core.show');
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/', [TreeController::class, 'store'])->name('genealogy.core.store');
    Route::patch('/{tree}/visibility', [TreeController::class, 'visibility'])->name('genealogy.core.visibility');
    Route::patch('/{tree}', [TreeController::class, 'update'])->name('genealogy.core.update');
    Route::delete('/{tree}', [TreeController::class, 'destroy'])->name('genealogy.core.destroy');
});
