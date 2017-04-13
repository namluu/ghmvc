<?php
namespace App\Module\Cms\Controller;
use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Post;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Index extends Controller
{
    protected $postModel;

    public function __construct(array $routeParams, Post $post)
    {
        $this->postModel = $post;
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
        View::renderTemplate('Cms::frontend/index/index.html', [
            'posts' => $posts
        ]);
    }
}