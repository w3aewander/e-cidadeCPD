<?php

use Illuminate\Support\Facades\Route;
use \Illuminate\Support\Facades\Request;

Route::get('alterar-dotacao', function (Request $request) {
    return view("patrimonial.contratos.alterar-dotacao");
});
