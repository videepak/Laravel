<?php

Route::pattern('id', '[0-9]+');
Route::pattern('index', '[0-9]+');
Route::pattern('url', '/^[a-z0-9]+$');

Route::get('auth/logout', 'Auth\AuthController@logout');

Route::get('email/view', function () {
    return view('email.welcome');
});

/*----------------------------------------------------------------------------------------------------------*
 * WEB 
/*----------------------------------------------------------------------------------------------------------*/

Route::group(['namespace' => 'Web'], function () {
    /*--------------------------*
     * Frontend Controllers
     **** General
     *--------------------------*/
    Route::group(['middleware' => 'web', 'namespace' => 'Frontend'], function () {
        require_once(__DIR__ . '/Routes/Web/Frontend/Guest.php');
    });

    /*--------------------------*
     * Frontend Controllers
     **** User
     *--------------------------*/
    Route::group(['middleware' => ['web', 'requester'], 'namespace' => 'Frontend\Requester'], function () {
        require_once(__DIR__ . '/Routes/Web/Frontend/Requester.php');
    });

    /*--------------------------*
     * Backend Controllers
     **** Admin
     *--------------------------*/
    Route::group(['middleware' => ['web', 'admin'], 'namespace' => 'Backend\Admin',
                  'prefix'     => 'admin'], function () {
        require_once(__DIR__ . '/Routes/Web/Backend/Admin.php');
    });

    /*--------------------------*
     * Backend Controllers
     **** Provider
     *--------------------------*/
    Route::group(['middleware' => ['web', 'provider'], 'namespace' => 'Backend\Provider',
                  'prefix'     => 'provider'], function () {
        require_once(__DIR__ . '/Routes/Web/Backend/Provider.php');
    });
});


/*----------------------------------------------------------------------------------------------------------*
 * API
/*----------------------------------------------------------------------------------------------------------*/

/*--------------------------*
 **** Requester
 *--------------------------*/

Route::group(['namespace' => 'Api', 'prefix' => 'api/v1'], function () {
    Route::post('register', 'Ouath2\AuthController@register');
    Route::post('password/reset', 'Ouath2\PasswordController@sendResetLinkEmail');
    Route::post('listings/search', 'Ouath2\AuthController@search');
    
    /**
    * Send & Validate OTP
    */

    Route::post('sendsms', 'Ouath2\AuthController@sendSms');
    Route::post('verifyotp', 'Ouath2\AuthController@verifyotp');
    Route::post('verifyregistration', 'Ouath2\AuthController@verifyRegistration');
    Route::post('revalidate', 'Ouath2\AuthController@revalidate');
    
    Route::get('timings/{id}', 'Ouath2\AuthController@timings');
    
});

Route::group(['namespace' => 'Api\Requester', 'prefix' => 'api/v1'], function () {
    Route::get('addresses/areas', 'AddressesController@getAreas');
    Route::get('addresses/governorates', 'AddressesController@getGovernorates');
    Route::get('addresses/governorates-areas', 'AddressesController@getGovernoratesWithAreas');
    require_once(__DIR__ . '/Routes/Api/Requester.php');
    
});

/*--------------------------*
 **** Version 2 22nd March 2021
 *--------------------------*/

Route::group(['namespace' => 'Api\Requester', 'prefix' => 'api/v2'], function () {
    Route::get('addresses/areas', 'AddressesController@getAreas');
    Route::get('addresses/governorates', 'AddressesController@getGovernorates');
    Route::get('addresses/governorates-areas', 'AddressesController@getGovernoratesWithAreas');
    require_once(__DIR__ . '/Routes/Api/Requesterv2.php');
    
});

/*--------------------------*
 **** Provider
 *--------------------------*/
Route::group(['namespace' => 'Api\Provider', 'prefix' => 'api/v1/provider'], function () {
    require_once(__DIR__ . '/Routes/Api/Provider.php');
});

/*--------------------------*
 **** Provider Version 2 24th March 2021
 *--------------------------*/

Route::group(['namespace' => 'Api\Provider', 'prefix' => 'api/v2/provider'], function () {
    require_once(__DIR__ . '/Routes/Api/Providerv2.php');
});



//Route::get('update', function () {
//    $providers = \App\Models\Provider::all();
//    $i = 1;
//    foreach ($providers as $provider) {
//        $provider->email = "provider+$i@h2monline.com";
//        $provider->save();
//        $user = $provider->users->first();
//        $user->email = "provider+$i@h2monline.com";
//        $user->save();
//        $i++;
//    }
//});
