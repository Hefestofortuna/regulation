<?php

/** @var Router $router */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Router;

$router->group(['prefix' => 'devices'], function() use ($router) {
    $router->get('', 'DevicesController@getDevices');
    $router->put('', 'DevicesController@putDevices');
    $router->get('station_list', 'DeliveryController@getStationsList');
});

$router->group(['prefix' => 'troubleshooting'], function() use ($router) {
    $router->put('', 'TroubleshootingController@putData');
    $router->get('', 'TroubleshootingController@getData');
    $router->get('history', 'TroubleshootingController@getHistory');
    $router->get('station_list', 'DeliveryController@getStationsList');
});
$router->group(['prefix' => 'delivery'], function () use ($router) {
    $router->get('', 'DeliveryController@getTimes');
    $router->put('', 'DeliveryController@putTimes');
    $router->get('station_list', 'DeliveryController@getStationsList');
    $router->get('history','DeliveryController@getHistory');
});
$router->group(['prefix' => 'employee_specialization'], function() use ($router){
    $router->get('user', 'SpecializationController@getUser');
    $router->get('brigade', 'SpecializationController@getBrigade');
});
