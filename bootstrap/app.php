<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {  
      
    })
    ->withExceptions(function (Exceptions $exceptions) {
      $exceptions->render(function (RouteNotFoundException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Route not found'
            ], 404);
        }
      }); 
      
      
    })->create();
