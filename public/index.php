<?php
/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Twig
 */
/*require_once dirname(__DIR__) . '/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();*/
require '../vendor/autoload.php';

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

$config = new App\Config();
$config->loadConfig();
$config->init();

/**
 * Routing
 */
$router = new Core\Router();
$adminUri = $config->getConfig('admin_uri');
// Add the routes
$router->add($adminUri.'/*', ['namespace' => 'Admin', 'module' => 'Dashboard', 'controller' => 'Index', 'action' => 'index']);
$router->add($adminUri.'/{module}', ['namespace' => 'Admin', 'controller' => 'Index', 'action' => 'index']);
$router->add($adminUri.'/{module}/{controller}', ['namespace' => 'Admin', 'action' => 'index']);
$router->add($adminUri.'/{module}/{controller}/{action}', ['namespace' => 'Admin']);
$router->add($adminUri.'/{module}/{controller}/{id:\d+}/{action}', ['namespace' => 'Admin']);
$router->add('', ['module' => 'cms', 'controller' => 'Index', 'action' => 'index']);
$router->add('post/*', ['module' => 'cms', 'controller' => 'post', 'action' => 'index']);
$router->add('about-us', ['module' => 'cms', 'controller' => 'page', 'action' => 'view', 'id' => 1]);
$router->add('{module}', ['controller' => 'Index', 'action' => 'index']);
$router->add('page/{url:[\w\-]+}/*', ['module' => 'cms', 'controller' => 'page', 'action' => 'view']);
$router->add('post/{id:\d+}/*', ['module' => 'cms', 'controller' => 'post', 'action' => 'view']);
$router->add('post/{alias:[\w\-]+}/*', ['module' => 'cms', 'controller' => 'post', 'action' => 'view']);
$router->add('user/{username:[\w\-]+}/*', ['module' => 'user', 'controller' => 'account', 'action' => 'view']);
$router->add('notification/*', ['module' => 'user', 'controller' => 'notification', 'action' => 'index']);
$router->add('{module}/{controller}', ['action' => 'index']);
$router->add('{module}/{controller}/{action}');
$router->add('{module}/{controller}/{id:\d+}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);