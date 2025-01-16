<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use ContraInteractive\WpLaravelLogin\Controllers\WpSyncPasswordApiController;


Route::post(
    config('wp-login.sync_password_route'), WpSyncPasswordApiController::class)
->middleware(config('wp-login.sync_password_middleware'));
