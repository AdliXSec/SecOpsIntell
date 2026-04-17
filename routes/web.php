<?php
use App\Http\Controllers\IPGeo;
use Illuminate\Support\Facades\Route;

Route::get('/', [IPGeo::class, 'index'])->name('home');
Route::get('/scan', [IPGeo::class, 'index'])->name('scan');
Route::post('/scan', [IPGeo::class, 'scan'])->name('scanpost');
Route::get('/ipgeo', [IPGeo::class, 'index'])->name('ipgeo');
Route::post('/ipgeo', [IPGeo::class, 'ipgeo'])->name('ipgeopost');
Route::get('/cve', [IPGeo::class, 'index'])->name('cve');
Route::post('/cve', [IPGeo::class, 'cve'])->name('cvepost');
Route::get('/ask-ai', [IPGeo::class, 'index'])->name('getaskai');
Route::post('/ask-ai', [IPGeo::class, 'askAI'])->name('askai');

Route::get('/dump', [IPGeo::class, 'dumptest'])->name('dumptest');