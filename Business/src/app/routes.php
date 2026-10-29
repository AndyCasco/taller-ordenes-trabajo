<?php namespace App\controllers;
use Slim\Routing\RouteCollectorProxy;


$app->group('/api', function(RouteCollectorProxy $api) {
 $api->group('/ordenes_trabajo',function(RouteCollectorProxy $endpoint){
 $endpoint->get('[/{id}]',OrdenesTrabajo::class . ':read');
 $endpoint->post('',OrdenesTrabajo::class . ':create');
 $endpoint->put('/{id}',OrdenesTrabajo::class . ':update');
 $endpoint->delete('/{id}',OrdenesTrabajo::class . ':delete');
 });

});
