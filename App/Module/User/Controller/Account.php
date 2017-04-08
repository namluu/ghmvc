<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\View;
use App\Helper;
use App\Module\User\Model\User;
use Core\Session;

/**
 * PHP version 7.0
 */
class Account extends Controller
{
    protected $userModel;
    protected $session;

    public function __construct(array $routeParams, User $user, Session $session)
    {
        parent::__construct($routeParams);
        $this->userModel = $user;
        $this->session = $session;
    }

    /**
     * Show the login page
     *
     * @return void
     */
    public function loginAction()
    {
        View::renderTemplate('User::frontend/account/login.html', [
        ]);
    }

    /**
     * Show the register page
     *
     * @return void
     */
    public function registerAction()
    {
        View::renderTemplate('User::frontend/account/register.html', [
        ]);
    }

    public function registerSubmitAction()
    {
        if( !$_POST ) {
            $this->redirect(Helper::getUrl('user/account/register'));
        }

        $errorMsg = array();
        $dataCache = array();
        $username = $this->cleanInput($_POST['username']);
        $email = $this->cleanInput($_POST['email']);
        $password = $this->cleanInput($_POST['password']);

        // basic name validation
        if (empty($username)) {
            $errorMsg[] = 'Please enter your full name.';
        } elseif (strlen($username) < 3) {
            $errorMsg[] = 'Name must have at least 3 characters.';
        } elseif (!preg_match("/^[a-zA-Z0-9]+$/",$username)) {
            $errorMsg[] = 'Name must contain alphabets and numbers.';
        } else {
            // check fullname exist or not
            $count = $this->userModel->countBy('username', $username);
            if ($count) {
                $errorMsg[] = 'Provided Username is already in use.';
            }
            $dataCache['username'] = $username;
        }

        if (!$errorMsg) {
            $this->session->setMessage('success', 'Register successfully');
            $this->redirect(Helper::getUrl('user/account/login'));
        }
        $this->session->setMessage('error', join('<br>', $errorMsg));
        $this->redirect(Helper::getUrl('user/account/register'));
    }
}