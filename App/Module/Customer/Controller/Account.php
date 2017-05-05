<?php
namespace App\Module\Customer\Controller;
use Core\Controller;
use Core\View;
use App\Helper;
use App\Module\Customer\Model\Customer;

/**
 * Product controller
 *
 * PHP version 7.0
 */
class Account extends Controller
{
    protected $customerModel;

    public function __construct(array $routeParams, Customer $customer)
    {
        parent::__construct($routeParams);
        $this->customerModel = $customer;
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

    public function registerSubmitAction()
    {
        if( !$_POST ) {
            $this->redirect(Helper::getUrl('customer/account/register'));
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
            $count = $this->customerModel->countBy(['username' => $username]);
            if ($count) {
                $errorMsg[] = 'Provided FullName is already in use.';
            }
            $dataCache['username'] = $username;
        }

        if (!$errorMsg) {

        }
        $this->redirect(Helper::getUrl('customer/account/register'));
    }
}