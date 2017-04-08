<?php
namespace App\Module\Catalog\Controller;
use Core\Controller;
use Core\View;
/**
 * Product controller
 *
 * PHP version 7.0
 */
class Product extends Controller
{
    public function __construct(array $routeParams)
    {
        parent::__construct($routeParams);
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {

        echo 'product';
    }
}