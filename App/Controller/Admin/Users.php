<?php
namespace App\Controller\Admin;
use Core\Controller;
/**
 * User controller
 *
 * PHP version 7.0
 */
class Users extends Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function index()
    {
        var_dump($_GET);
        echo 'home index';
    }

    public function before()
    {
        // make sure admin user is logged in
        echo 'before';
    }

    public function after()
    {
        echo 'after';
    }

    public function editAction()
    {
        var_dump($_GET);
        var_dump($this->routeParams);
        echo 'home edit';
    }
}