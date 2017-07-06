<?php
namespace Core;
/**
 * Base controller
 *
 * PHP version 7.0
 */
abstract class Controller
{
    /**
     * Parameters from the matched route
     * @var array
     */
    protected $routeParams = [];

    protected $session;

    /**
     * Class constructor
     *
     * @param array $routeParams  Parameters from the route
     *
     */
    public function __construct($routeParams)
    {
        $this->initAuthentication($routeParams);
        $this->initPagination($routeParams);
    }
    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param string $name  Method name
     * @param array $args Arguments passed to the method
     *
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        $method = $name . 'Action';
        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }
    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
    }
    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }

    public function cleanInput($input)
    {
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input);

        return $input;
    }

    public function redirect($location)
    {
        header('Location: '.$location);
        die;
    }

    public function getPreviousUrl()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    public function getSession()
    {
        if (!$this->session) {
            $this->session = new Session();
        }
        return $this->session;
    }

    protected function initAuthentication($routeParams)
    {
        if (isset($routeParams['namespace']) && $routeParams['namespace'] == 'Admin') {
            if (!$this->getSession()->get('admin_user') && $routeParams['action'] != 'login') {
                $this->redirect(\App\Helper::getAdminUrl('dashboard/auth/login'));
            }
        }
    }

    protected function initPagination($routeParams)
    {
        if (isset($_GET['page'])) {
            $routeParams['page'] = $_GET['page'];
        } else {
            $routeParams['page'] = 1;
        }
        $this->routeParams = $routeParams;
    }
}