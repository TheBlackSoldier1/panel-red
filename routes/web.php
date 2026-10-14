<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedController;

Route::get('/', [RedController::class, 'mostrarDashboard']);
