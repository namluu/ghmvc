<?php
namespace Core;

use App\Helper;
/**
 * View
 *
 * PHP version 7.0
 */
class View
{
    static $twig;

    public static function init()
    {
        if (self::$twig === null) {
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/View');
            self::$twig = new \Twig_Environment($loader);

            // create functions
            $function = new \Twig_SimpleFunction('path', function($string = '') {
                return Helper::getUrl($string);
            });
            self::$twig->addFunction($function);

            $function = new \Twig_SimpleFunction('admin_path', function($string = '') {
                return Helper::getAdminUrl($string);
            });
            self::$twig->addFunction($function);
        }
    }

    /**
     * Render a view file
     *
     * @param string $view  The view file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @throws \Exception
     */
    public static function render($view, $args = [])
    {
        extract($args, EXTR_SKIP);
        $file = dirname(__DIR__) . "/App/View/$view";  // relative to Core directory
        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $view  The view file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @throws \Exception
     */
    public static function renderTemplate($view, $args = [])
    {
        self::init();
        echo self::$twig->render($view, $args);
    }
}