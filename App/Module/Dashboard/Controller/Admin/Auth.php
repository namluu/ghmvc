<?php
namespace App\Module\Dashboard\Controller\Admin;
use Core\Controller;
use Core\View;

class Auth extends Controller
{
    public function loginAction()
    {
        View::renderTemplate('Dashboard::backend/auth/login.html');
    }
}