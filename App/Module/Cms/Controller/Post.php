<?php
namespace App\Module\Cms\Controller;
use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Post as PostModel;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Post extends Controller
{
    protected $postModel;

    public function __construct(array $routeParams, PostModel $post)
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
        View::renderTemplate('Cms::frontend/post/index.html', [
            'posts' => $posts
        ]);
    }

    public function addAction()
    {
        echo 'post add';
    }
}