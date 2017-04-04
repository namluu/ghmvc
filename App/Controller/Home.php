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
    protected $postModel;

    public function __construct(array $routeParams)
    {
        $this->postModel = new \App\Model\Posts;
        parent::__construct($routeParams);
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        $posts = $this->postModel->getAll();
        View::renderTemplate('Home/index.html', [
            'name' => 'Nam',
            'colors' => ['red', 'blue', 'yellow'],
            'posts' => $posts
        ]);
    }
}