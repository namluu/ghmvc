<?php
namespace App\Controller;
use Core\Controller;
use Core\View;
use App\Model\Posts;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Home extends Controller
{
    protected $postModel;

    public function __construct(array $routeParams, Posts $posts)
    {
        $this->postModel = $posts;
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