<?php
namespace App\Controller;
use Core\Controller;
use Core\View;
/**
 * Post controller
 *
 * PHP version 7.0
 */
class Posts extends Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Posts/index.html', [

        ]);
    }

    public function addAction()
    {
        View::renderTemplate('Posts/add.html', [

        ]);
    }
}