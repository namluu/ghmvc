<?php
namespace App\Controller;
use Core\Controller;
use Core\View;
use App\Model\Users as UserModel;
/**
 * User controller
 *
 * PHP version 7.0
 */
class Users extends Controller
{
    protected $userModel;

    public function __construct(array $routeParams, UserModel $users)
    {
        $this->userModel = $users;
        parent::__construct($routeParams);
    }

    /**
     * Show the login page
     *
     * @return void
     */
    public function loginAction()
    {
        View::renderTemplate('Users/login.html', [
        ]);
    }

    /**
     * Show the register page
     *
     * @return void
     */
    public function registerAction()
    {
        View::renderTemplate('Users/register.html', [
        ]);
    }
}