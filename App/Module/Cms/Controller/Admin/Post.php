<?php
namespace App\Module\Cms\Controller\Admin;
use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Post as PostModel;
use App\Helper;
use Core\Session;
use Core\Url;
/**
 * Post controller
 *
 * PHP version 7.0
 */
class Post extends Controller
{
    protected $postModel;
    protected $session;
    protected $url;

    public function __construct(
        array $routeParams,
        PostModel $post,
        Session $session,
        Url $url
    ) {
        $this->postModel = $post;
        $this->session = $session;
        $this->url = $url;
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
        View::renderTemplate('Cms::backend/post/index.html', [
            'posts' => $posts
        ]);
    }

    public function addAction()
    {
        $this->editAction();
    }

    public function editAction()
    {
        $id = isset($this->routeParams['id']) ? $this->routeParams['id'] : null;
        $post = $this->postModel->load($id);
        View::renderTemplate('Cms::backend/post/edit.html', [
            'post' => $post
        ]);
    }

    public function saveAction()
    {
        if ($_POST) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $errorMsg = $this->validateData($_POST);
            if ($errorMsg) {
                $this->redirect(Helper::getAdminUrl('cms/post'));
            } else {
                $data = $this->sanitizeData($_POST);
                $result = $this->postModel->save($data, $id);
                if ($result) {
                    $this->session->setMessage('success', 'Save successfully');
                } else {
                    $this->session->setMessage('error', 'Save successfully');
                }
            }
        }
        $this->redirect(Helper::getAdminUrl('cms/post'));
    }

    public function deleteAction()
    {
        $id = $this->routeParams['id'];
        $result = $this->postModel->delete($id);
        if ($result) {
            $this->session->setMessage('success', 'Delete successfully');
        } else {
            $this->session->setMessage('error', 'Delete successfully');
        }
        $this->redirect(Helper::getAdminUrl('cms/post'));
    }

    protected function validateData($data)
    {
        $msg = array();
        if (empty($data['title'])) {
            $msg[] = 'Missing title';
        }
        if (empty($data['content'])) {
            $msg[] = 'Missing content';
        }
        return $msg;
    }

    protected function sanitizeData($data)
    {
        $title = $this->cleanInput($data['title']);
        if (!$data['alias']) {
            $data['alias'] = $title;
        }
        $alias = $this->cleanInput($data['alias']);
        $alias = $this->url->slug($alias, array('toascii'=>true,'tolower'=>true));
        $escapeData = [
            'title' => $title,
            'alias' => $alias,
            'content' => $this->cleanInput($data['content'])
        ];
        return $escapeData;
    }
}