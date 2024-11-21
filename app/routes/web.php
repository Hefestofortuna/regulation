<?php

/** @var Router $router */

use Laravel\Lumen\Routing\Router;


$router->get('station','StationController@list');

$router->group(['prefix' => 'devices'], function() use ($router) {
    $router->get('',  'DevicesController@list');
    $router->put('', 'DevicesController@update');
    $router->get('station', 'StationController@list');
});

$router->group(['prefix' => 'troubleshooting'], function() use ($router) {
    $router->put('', 'TroubleshootingController@update');
    $router->get('', 'TroubleshootingController@list');
    $router->get('history', 'TroubleshootingController@history');
});
$router->group(['prefix' => 'delivery'], function () use ($router) {
    $router->get('', 'DeliveryController@getTimes');
    $router->put('', 'DeliveryController@putTimes');
    $router->get('history','DeliveryController@getHistory');
});
$router->group(['prefix' => 'employee_specialization'], function() use ($router){
    $router->get('user', 'SpecializationController@user');
    $router->get('brigade', 'SpecializationController@brigade');
});
