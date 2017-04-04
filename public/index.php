<?php
/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Twig
 */
require_once dirname(__DIR__) . '/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

/**
 * Autoloader
 */
spl_autoload_register(function ($class) {
    $root = dirname(__DIR__); // get the parent
    $file = $root . '/' .str_replace('\\', '/', $class) . '.php';
    if (is_readable($file)) {
        require $root . '/' . str_replace('\\', '/', $class) . '.php';
    }
});

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Routing
 */
$router = new Core\Router();
$adminUri = App\Config::ADMIN_URI;
// Add the routes
$router->add($adminUri, ['namespace' => 'Admin', 'controller' => 'Home', 'action' => 'index']);
$router->add($adminUri.'/{controller}', ['namespace' => 'Admin', 'action' => 'index']);
$router->add($adminUri.'/{controller}/{action}', ['namespace' => 'Admin']);
$router->add($adminUri.'/{controller}/{id:\d+}/{action}', ['namespace' => 'Admin']);
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}', ['action' => 'index']);
$router->add('{controller}/{action}');
$router->add('{controller}/{id:\d+}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);