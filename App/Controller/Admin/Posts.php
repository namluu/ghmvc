<?php
namespace App\Controller\Admin;
use Core\Controller;
use Core\View;
use App\Model\Posts as PostModel;
use App\Helper;
/**
 * Post controller
 *
 * PHP version 7.0
 */
class Posts extends Controller
{
    protected $postModel;

    public function __construct(array $routeParams, PostModel $posts)
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
        View::renderTemplate('Admin/Posts/index.html', [
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
        View::renderTemplate('Admin/Posts/edit.html', [
            'post' => $post
        ]);
    }

    public function saveAction()
    {
        if ($_POST) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $errorMsg = $this->validateData($_POST);
            if ($errorMsg) {
                $this->redirect(Helper::getAdminUrl('posts'));
            } else {
                $data = $this->sanitizeData($_POST);
                $result = $this->postModel->save($data, $id);
                if ($result) {

                } else {

                }
            }
        }
        $this->redirect(Helper::getAdminUrl('posts'));
    }

    public function deleteAction()
    {
        $id = $this->routeParams['id'];
        $result = $this->postModel->delete($id);
        if ($result) {

        } else {

        }
        $this->redirect(Helper::getAdminUrl('posts'));
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
        $escapeData = [
            'title' => $this->cleanInput($data['title']),
            'content' => $this->cleanInput($data['content'])
        ];
        return $escapeData;
    }
}