<?php
namespace App\Module\Cms\Controller;
use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Post as PostModel;
use App\Module\Cms\Model\Tag as TagModel;
use App\Module\User\Model\User as UserModel;
use App\Module\Cms\Model\Comment as CommentModel;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Post extends Controller
{
    protected $postModel;
    protected $tagModel;
    protected $userModel;
    protected $commentModel;

    public function __construct(
        array $routeParams,
        PostModel $post,
        TagModel $tag,
        UserModel $user,
        CommentModel $comment
    ) {
        $this->postModel = $post;
        $this->tagModel = $tag;
        $this->userModel = $user;
        $this->commentModel = $comment;
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
        foreach ($posts as $post) {
            $tagIds = $this->postModel->getPostTagIds($post->id);
            $post->tags = $this->tagModel->getAllBy('id', $tagIds);
        }
        View::renderTemplate('Cms::frontend/post/index.html', [
            'posts' => $posts
        ]);
    }

    public function viewAction()
    {
        if (isset($this->routeParams['alias'])) {
            $alias = $this->routeParams['alias'];
            $post = $this->postModel->getOneBy('alias', $alias);
        } elseif (isset($this->routeParams['id'])) {
            $id = $this->routeParams['id'];
            $post = $this->postModel->getOneBy('id', $id);
        } else {
            throw new \Exception('Post not found.', 404);
        }

        if (!$post) {
            throw new \Exception('Post not found.', 404);
        }
        $tagIds = $this->postModel->getPostTagIds($post->id);
        $post->tags = $this->tagModel->getAllBy('id', $tagIds);
        $post->user = $this->userModel->getOneBy('id', $post->user_id);
        $post->comments = $this->commentModel->getAllBy('post_id', [$post->id]);
        View::renderTemplate('Cms::frontend/post/view.html', [
            'post' => $post
        ]);
    }

    public function addAction()
    {
        echo 'post add';
    }
}