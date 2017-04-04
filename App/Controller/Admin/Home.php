<?php
namespace App\Controller\Admin;
use Core\Controller;
use Core\View;
/**
 * User controller
 *
 * PHP version 7.0
 */
class Home extends Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Admin/Home/index.html', [
        ]);
    }
}