<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'IndexController@index')->name('index');

Route::get('/offline', 'ErrorController@offline')->name('error/offline');

Route::get('/legal', 'PagesController@legal')->name('legal');

Route::get('/sso', 'SsoController@index')->name('sso');

Route::get('/signatures', 'SignaturesController@index')->name('signatures');
Route::put('/signatures/analyze', 'SignaturesController@analyze')->name('signatures.analyze');

Route::get('/ajax/wormholes', 'AjaxController@wormholes')->name('ajax.wormholes');
Route::get('/ajax/systems', 'AjaxController@systems')->name('ajax.systems');
Route::put('/ajax/signature', 'AjaxController@signature')->name('ajax.signature');
Route::delete('/ajax/signature', 'AjaxController@signatureDelete')->name('ajax.signature.delete');
Route::post('/ajax/signature', 'AjaxController@signatureLike')->name('ajax.signature.like');
