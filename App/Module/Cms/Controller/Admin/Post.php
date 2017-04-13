<?php
namespace App\Module\Cms\Controller\Admin;
use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Post as PostModel;
use App\Helper;
use Core\Session;
use Core\Url;
use App\Module\User\Model\User as UserModel;
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
    protected $userModel;
    protected $cacheData = [];

    public function __construct(
        array $routeParams,
        PostModel $post,
        UserModel $user,
        Session $session,
        Url $url
    ) {
        $this->postModel = $post;
        $this->userModel = $user;
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
        $post = $this->session->getFormData('post_form_data', $post);
        $selectActive = [['id' => 0, 'name' => 'False'], ['id' => 1, 'name' => 'True']];
        $users = $this->userModel->getAll();
        $selectUsers = [];
        $selectUsers[] = ['id' => 0, 'name' => 'Please select ...'];
        foreach ($users as $user) {
            $selectUsers[] = ['id' => $user->id, 'name' => $user->display_name];
        }
        View::renderTemplate('Cms::backend/post/edit.html', [
            'post' => $post,
            'selectActive' => $selectActive,
            'selectUsers' => $selectUsers
        ]);
    }

    public function saveAction()
    {
        if ($_POST) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $errorMsg = $this->validateData($_POST);
            if ($errorMsg) {
                $this->session->setFormData('post_form_data', $this->cacheData);
                $this->session->setMessage('error', implode(', ', $errorMsg));
                $id ? $this->redirect(Helper::getAdminUrl("cms/post/{$id}/edit")) :
                    $this->redirect(Helper::getAdminUrl('cms/post/add'));
            } else {
                $data = $this->sanitizeData($_POST);
                $result = $this->postModel->save($data, $id);
                if ($result) {
                    $this->session->setMessage('success', 'Save successfully');
                } else {
                    $this->session->setMessage('error', 'Save unsuccessfully');
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
        } else {
            $this->cacheData['title'] = $data['title'];
        }
        if (empty($data['content'])) {
            $msg[] = 'Missing content';
        } else {
            $this->cacheData['content'] = $data['content'];
        }
        if ($data['user_id'] == 0) {
            $msg[] = 'Select user';
        } else {
            $this->cacheData['user_id'] = $data['user_id'];
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
            'content' => $this->cleanInput($data['content']),
            'is_active' => $data['is_active'],
            'user_id' => $data['user_id']
        ];
        return $escapeData;
    }
}