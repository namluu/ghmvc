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
        $posts = $this->postModel->getAll(true);
        View::renderTemplate('Cms::frontend/post/index.html', [
            'posts' => $posts
        ]);
    }

    public function viewAction()
    {
        if (isset($this->routeParams['alias'])) {
            $alias = $this->routeParams['alias'];
            $post = $this->postModel->getBy('alias', $alias);
        } elseif (isset($this->routeParams['id'])) {
            $id = $this->routeParams['id'];
            $post = $this->postModel->getBy('id', $id);
        } else {
            throw new \Exception('Post not found.', 404);
        }

        if (!$post) {
            throw new \Exception('Post not found.', 404);
        }
        View::renderTemplate('Cms::frontend/post/view.html', [
            'post' => $post
        ]);
    }

    public function addAction()
    {
        echo 'post add';
    }
}