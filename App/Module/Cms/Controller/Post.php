<?php
namespace App\Module\Cms\Controller;
use App\Helper;
use Core\Controller;
use Core\Session;
use Core\Url;
use Core\View;
use Core\UploadHandler;
use Core\Paginator;
use App\Module\Cms\Model\Post as PostModel;
use App\Module\Cms\Model\Tag as TagModel;
use App\Module\User\Model\User as UserModel;
use App\Module\User\Model\Action as UserActionModel;
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
    protected $userActionModel;
    protected $commentModel;
    protected $session;
    protected $url;
    protected $paginator;
    protected $cacheData = [];

    public function __construct(
        array $routeParams,
        PostModel $post,
        TagModel $tag,
        UserModel $user,
        UserActionModel $action,
        CommentModel $comment,
        Session $session,
        Url $url,
        Paginator $paginator
    ) {
        $this->paginator = $paginator;
        $this->url = $url;
        $this->session = $session;
        $this->postModel = $post;
        $this->tagModel = $tag;
        $this->userModel = $user;
        $this->userActionModel = $action;
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
        $limit = \App\Config::getConfig('pagination_frontend');
        $page = $this->routeParams['page'];
        $isHot = isset($_GET['hottest']) && $_GET['hottest'] ? 1 : 0;
        $posts = $this->postModel->getAll(true, $limit, $page, $isHot);
        foreach ($posts as $post) {
            $tagIds = $this->postModel->getPostTagIds($post->id);
            $post->tags = $this->tagModel->getAllBy('id', $tagIds);
        }
        $totalRows = $this->postModel->countBy(['is_active' => 1, 'is_hot' => $isHot]);
        $paginator = $this->paginator->init($totalRows, $limit, $page, $this->routeParams);
        $userLogin = $this->session->get('login_user');

        $countPost = $userLogin ? $this->postModel->countBy(['user_id' => $userLogin['id']]) : 0;
        $countFollow = $userLogin ? count($this->userModel->getFollowedIds($userLogin['id'])) : 0;

        View::renderTemplate('Cms::frontend/post/index.html', [
            'posts' => $posts,
            'paginator' => $paginator,
            'countPost' => $countPost,
            'countFollow' => $countFollow
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

        $countPost = $this->postModel->countBy(['user_id' => $post->user_id]);
        $countFollow = count($this->userModel->getFollowedIds($post->user_id));

        View::renderTemplate('Cms::frontend/post/view.html', [
            'post' => $post,
            'countPost' => $countPost,
            'countFollow' => $countFollow
        ]);
    }

    public function addAction()
    {
        View::renderTemplate('Cms::frontend/post/add.html', [
        ]);
    }

    public function postSubmitAction()
    {
        if ($_POST) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $errorMsg = $this->validateData($_POST);
            if ($errorMsg) {
                //$this->session->setFormData('post_form_data_from', $this->cacheData);
                $this->session->setMessage('error', implode(', ', $errorMsg));
            } else {
                $data = $this->sanitizeData($_POST);
                $resultPostId = $this->postModel->save($data);
                /*$resultTag = true;
                if (isset($_POST['tag_ids'])) {
                    $resultTag = $this->postModel->updatePostTag($resultPostId, $_POST['tag_ids']);
                }*/

                if ($resultPostId) {
                    $current = date('Y-m-d H:i:s');
                    $this->userActionModel->save([
                        'user_id' => $data['user_id'],
                        'action_type' => 'post_add',
                        'action_detail' => $resultPostId,
                        'created_at' => $current
                    ]);
                    $this->userModel->updateRelationVersion($data['user_id'], $current);

                    $this->session->setMessage('success', 'Save successfully');
                } else {
                    $this->session->setMessage('error', 'Save unsuccessfully');
                }
                $post = $post = $this->postModel->getOneBy('id', $resultPostId);

                $this->redirect(Helper::getUrl('post/' . $post->alias));
            }
        }
        $this->redirect($this->getPreviousUrl());
    }

    protected function sanitizeData($data)
    {
        $user = $this->session->get('login_user');
        $title = $this->cleanInput($data['title']);
        $alias = $this->url->slug($title, array('toascii'=>true,'tolower'=>true));
        $escapeData = [
            'title' => $title,
            'alias' => $alias,
            'content' => html_entity_decode($data['content']),
            'is_active' => 1,
            'user_id' => $user['id']
        ];
        return $escapeData;
    }

    protected function validateData($data)
    {
        $msg = array();
        if (empty($data['title'])) {
            $msg[] = 'Missing title';
        } else {
            $this->cacheData['title'] = $data['title'];
        }
        if (empty($this->cleanInput($data['content']))) {
            $msg[] = 'Missing content';
        } else {
            $this->cacheData['content'] = $data['content'];
        }
        return $msg;
    }

    public function upload()
    {
        $upload_handler = new UploadHandler(array(
            'upload_dir' => Helper::getPath('public/uploads/cms/'),
            'upload_url' => Helper::getUrl('uploads/cms/'),
        ));
    }
}