<?php
namespace App\Controller;
use Core\Controller;
use Core\View;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Products extends Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        echo 'product index';
    }
}