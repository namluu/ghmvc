<?php
namespace App\Module\Cms\Controller\Admin;
use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Post as PostModel;
use App\Helper;
use Core\Session;
use Core\Url;
use Core\Paginator;
use App\Module\User\Model\User as UserModel;
use App\Module\Cms\Model\Tag as TagModel;
use Core\Ckeditor;
use App\Module\Cms\Model\PostTag;
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
    protected $tagModel;
    protected $cacheData = [];
    protected $ckeditor;
    protected $paginator;
    protected $postTagModel;

    public function __construct(
        array $routeParams,
        PostModel $post,
        UserModel $user,
        Session $session,
        Url $url,
        TagModel $tag,
        PostTag $postTag,
        Ckeditor $ckeditor,
        Paginator $paginator
    ) {
        $this->paginator = $paginator;
        $this->ckeditor = $ckeditor;
        $this->postModel = $post;
        $this->userModel = $user;
        $this->tagModel = $tag;
        $this->postTagModel = $postTag;
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
        $limit = \App\Config::getConfig('pagination_backend');
        $page = $this->routeParams['page'];
        $posts = $this->postModel->getAll(false, $limit, $page);
        $totalRows = $this->postModel->count();
        $paginator = $this->paginator->init($totalRows, $limit, $page, $this->routeParams);
        View::renderTemplate('Cms::backend/post/index.html', [
            'posts' => $posts,
            'paginator' => $paginator
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
        $tags = $this->tagModel->getAll();
        $selectTags = [];
        foreach ($tags as $tag) {
            $selectTags[] = ['id' => $tag->id, 'name' => $tag->name];
        }
        // edit form post back - has cache
        if (isset($post->tag_ids)) {
            $postTagIds = $post->tag_ids;
        } else {
            $postTagIds = $this->postTagModel->getPostTagIds($id);
        }

        View::renderTemplate('Cms::backend/post/edit.html', [
            'post' => $post,
            'selectActive' => $selectActive,
            'selectUsers' => $selectUsers,
            'selectTags' => $selectTags,
            'postTagIds' => $postTagIds,
            'ckeditor' => $this->ckeditor
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
                $resultPostId = $this->postModel->save($data, $id);
                $resultTag = true;
                if (isset($_POST['tag_ids'])) {
                    $resultTag = $this->postTagModel->updatePostTag($resultPostId, $_POST['tag_ids']);
                }

                if ($resultPostId && $resultTag) {
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
            $this->postTagModel->delete($id);
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
        if (isset($data['tag_ids'])) {
            $this->cacheData['tag_ids'] = $data['tag_ids'];
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
            'content' => html_entity_decode($data['content']),
            'is_active' => $data['is_active'],
            'user_id' => $data['user_id']
        ];
        return $escapeData;
    }

    public function active()
    {
        $id = $this->routeParams['id'];
        $result = $this->postModel->save(['is_active' => 1], $id);
        if ($result) {
            $this->session->setMessage('success', 'Update successfully');
        } else {
            $this->session->setMessage('error', 'Update successfully');
        }
        $this->redirect(Helper::getAdminUrl('cms/post'));
    }

    public function inactive()
    {
        $id = $this->routeParams['id'];
        $result = $this->postModel->save(['is_active' => 0], $id);
        if ($result) {
            $this->session->setMessage('success', 'Update successfully');
        } else {
            $this->session->setMessage('error', 'Update successfully');
        }
        $this->redirect(Helper::getAdminUrl('cms/post'));
    }
}