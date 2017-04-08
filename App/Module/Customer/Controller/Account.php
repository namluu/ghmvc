<?php
namespace App\Module\Customer\Controller;
use Core\Controller;
use Core\View;
/**
 * Product controller
 *
 * PHP version 7.0
 */
class Account extends Controller
{
    public function __construct(array $routeParams)
    {
        parent::__construct($routeParams);
    }

    /**
     * Show the login page
     *
     * @return void
     */
    public function loginAction()
    {
        View::renderTemplate('Customer::frontend/account/login.html', [
        ]);
    }

    /**
     * Show the register page
     *
     * @return void
     */
    public function registerAction()
    {
        View::renderTemplate('Customer::frontend/account/register.html', [
        ]);
    }
}