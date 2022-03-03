<?php

Route::get('access/{token}', '\Rkesa\Client\Http\Controllers\ContactAccessController@page_of_contact');

Route::get('login', function () { return redirect('/'); });
Route::post('login', 'AuthController@login')->name('login');
Route::post('refresh', 'AuthController@refresh');
Route::post('logout', 'AuthController@logout')->name('logout')->middleware('auth:api');

Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::post('password/reset/{token}', 'Auth\ResetPasswordController@reset')->name('password.reset'); // this route is used only for generating reset link
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('/css/colors.css', 'HomeController@colors');
Route::get('/shared_info', 'AuthController@shared_info');

Route::post('/saas/webhook', 'SaasWebhookController@webhook'); // protected inside by ip

Route::get('/zadarma/webhook', 'ZadarmaController@webhook');
Route::post('/zadarma/webhook', 'ZadarmaController@webhook');

// Route::post('/yandex-kassa/webhook', 'YandexKassaController@webhook');

Route::any('{all}', function(){
    return view('spa');
})->where(['all' => '.*']);

// REMOVE ALL BELOW

Route::post('login_with_photo', 'ApiController@login_with_photo');

Route::post('/time_point', 'ApiController@time_point');

Route::post('/checkfront/booking_status', 'ApiController@checkfront_booking_status_webhook');

Route::group(['middleware' => 'auth_verify'], function () {

    Route::get('/start_tour',  'HomeController@start_tour');
    Route::post('/end_tour',  'HomeController@end_tour');

});