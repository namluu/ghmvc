<?php
namespace App\Module\Dashboard\Controller\Admin;
use Core\Controller;
use Core\View;
/**
 * Dashboard Admin Index controller
 *
 * PHP version 7.0
 */
class Index extends Controller
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
    public function indexAction()
    {
        View::renderTemplate('Dashboard::backend/index.html', [
        ]);
    }
}