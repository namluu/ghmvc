<?php
namespace App\Controller;
use Core\Controller;
use Core\View;
/**
 * Home controller
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
        View::renderTemplate('Home/index.html', [
            'name' => 'Nam',
            'colors' => ['red', 'blue', 'yellow']
        ]);
    }
}