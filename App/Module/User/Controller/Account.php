<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\View;
use App\Helper;
use App\Module\User\Model\User;
use Core\Session;
use App\Config;

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

    public function loginSubmitAction()
    {
        if( !$_POST ) {
            $this->redirect(Helper::getUrl('user/account/login'));
        }

        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = $this->cleanInput($_POST['email']);
            $password = $this->cleanInput($_POST['password']);
            if ( filter_var($email,FILTER_VALIDATE_EMAIL) ) {
                $user = $this->userModel->getBy('email', $email);
                $hash = md5(Config::SALT . $password);
                if ($user && $user->is_active && $hash === $user->password) {
                    $this->session->set('username', $user->username);
                    $this->session->set('role', 'user');
                    $this->session->setMessage('success', 'Login successfully');
                    $this->redirect(Helper::getUrl('user/account'));
                } else {
                    $this->session->setMessage('error', 'Wrong account');
                }
            }
        } else {
            $this->session->setMessage('error', 'Please enter your account');
            $this->redirect(Helper::getUrl('user/account/login'));
        }
    }

    /**
     * Show the register page
     *
     * @return void
     */
    public function registerAction()
    {
        $user = $this->getUserFormData();
        View::renderTemplate('User::frontend/account/register.html', [
            'user' => $user
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
        $passwordConfirm = $this->cleanInput($_POST['confirm-password']);

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

        // basic email validation
        if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
            $errorMsg[] = 'Please enter valid email address.';
        } else {
            // check email exist or not
            $count = $this->userModel->countBy('email', $email);
            if ($count) {
                $errorMsg[] = 'Provided Email is already in use.';
            }
            $dataCache['email'] = $email;
        }

        // password validation
        if (empty($password)){
            $errorMsg[] = "Please enter password.";
        } else if(strlen($password) < 6) {
            $errorMsg[] = 'Password must have at least 6 characters.';
        } else if ($password != $passwordConfirm) {
            $errorMsg[] = 'Please make sure your passwords match.';
        }

        if (!$errorMsg) {
            $hash = md5(Config::SALT.$password);
            $data = array(
                'username' => $username,
                'password' => $hash,
                'email' => $email
            );
            $this->userModel->save($data);
            $this->session->setMessage('success', 'Register successfully');
            $this->redirect(Helper::getUrl('user/account/login'));
        }
        $this->setUserFormData($dataCache);
        $this->session->setMessage('error', join('<br>', $errorMsg));
        $this->redirect(Helper::getUrl('user/account/register'));
    }

    public function setUserFormData($dataCache)
    {
        $this->session->set('user_form_data', $dataCache);
    }

    public function getUserFormData()
    {
        $user = $this->session->get('user_form_data');
        if (!$user) {
            $user = $this->userModel->load();
        }
        $this->session->delete('user_form_data');
        return $user;
    }

    public function logout()
    {
        $this->session->destroy();
        $this->redirect(Helper::getUrl());
    }

    public function indexAction()
    {
        View::renderTemplate('User::frontend/account/index.html', [
        ]);
    }
}