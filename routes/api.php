<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloWorldController;

Route::apiResource('hello', HelloWorldController::class);
