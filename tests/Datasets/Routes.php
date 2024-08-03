<?php

dataset('routes', function () {
    return array_values(collect(Route::getRoutes())
        ->filter(fn ($route) => in_array('GET', $route->methods()))
        ->reject(fn ($route) => in_array('auth', $route->gatherMiddleware()))
        ->reject(fn ($route) => in_array($route->uri(), ['sanctum/csrf-cookie']))
        ->map(function ($route) {
            return $route->uri();
        })->toArray());
});
