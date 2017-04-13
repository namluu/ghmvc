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

    public static function init($module = null)
    {
        if (self::$twig === null) {
            $paths[] = dirname(__DIR__) . '/App/Layout';
            if ($module) {
                $paths[] = dirname(__DIR__) . '/App/Module/'.$module.'/View';
            }
            $loader = new \Twig_Loader_Filesystem($paths);
            self::$twig = new \Twig_Environment($loader);
            self::$twig->addExtension(new TwigExtension());
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
        $args['messages'] = Session::getMessage();

        if (sizeof(explode('::', $view)) == 2) {
            list($module, $template) = explode('::', $view);
            self::init($module);
            echo self::$twig->render($template, $args);
        } else {
            self::init();
            echo self::$twig->render($view, $args);
        }
    }
}