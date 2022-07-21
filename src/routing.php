<?php

$routeParts = explode('/', ltrim($_SERVER['REQUEST_URI'], '/') ?: HOME_PAGE);

$route = preg_replace("/(^\/)|(\/$)/", "", $_SERVER['REQUEST_URI']);
$parameters = explode('&', $_SERVER['QUERY_STRING']);


$controller = 'App\Controller\\' . ucfirst(explode('?', $route ?? '')[0]) . 'Controller';
$method = 'getPowerHouse';

if (class_exists($controller) && method_exists(new $controller(), $method)) {
    echo call_user_func_array([new $controller(), $method], $parameters);
} else {
    header("HTTP/1.0 404 Not Found");
    echo '404 - Page not found';
    exit();
}
