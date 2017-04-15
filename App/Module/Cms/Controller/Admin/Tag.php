<?php
namespace App\Module\Cms\Controller\Admin;
use Core\Controller;
use Core\View;
use App\Helper;
use Core\Session;
use Core\Url;
use App\Module\Cms\Model\Tag as TagModel;
/**
 * Tag controller
 *
 * PHP version 7.0
 */
class Tag extends Controller
{
    protected $session;
    protected $url;
    protected $tagModel;
    protected $cacheData = [];

    public function __construct(
        array $routeParams,
        Session $session,
        Url $url,
        TagModel $tag
    ) {
        $this->tagModel = $tag;
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
        $tags = $this->tagModel->getAll();
        View::renderTemplate('Cms::backend/tag/index.html', [
            'tags' => $tags
        ]);
    }

    public function addAction()
    {
        $this->editAction();
    }

    public function editAction()
    {
        $id = isset($this->routeParams['id']) ? $this->routeParams['id'] : null;
        $tag = $this->tagModel->load($id);
        $tag = $this->session->getFormData('tag_form_data', $tag);
        $selectActive = [['id' => 0, 'name' => 'False'], ['id' => 1, 'name' => 'True']];
        $colors = $this->tagModel->getColors();
        $selectColor = [];
        foreach ($colors as $key => $color) {
            $selectColor[] = ['id' => $key, 'color' => $color];
        }
        View::renderTemplate('Cms::backend/tag/edit.html', [
            'tag' => $tag,
            'selectActive' => $selectActive,
            'selectColor' => $selectColor
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
                $id ? $this->redirect(Helper::getAdminUrl("cms/tag/{$id}/edit")) :
                    $this->redirect(Helper::getAdminUrl('cms/tag/add'));
            } else {
                $data = $this->sanitizeData($_POST);
                $result = $this->tagModel->save($data, $id);
                if ($result) {
                    $this->session->setMessage('success', 'Save successfully');
                } else {
                    $this->session->setMessage('error', 'Save unsuccessfully');
                }
            }
        }
        $this->redirect(Helper::getAdminUrl('cms/tag'));
    }

    public function deleteAction()
    {
        $id = $this->routeParams['id'];
        $result = $this->tagModel->delete($id);
        if ($result) {
            $this->session->setMessage('success', 'Delete successfully');
        } else {
            $this->session->setMessage('error', 'Delete successfully');
        }
        $this->redirect(Helper::getAdminUrl('cms/tag'));
    }

    protected function validateData($data)
    {
        $msg = array();
        if (empty($data['name'])) {
            $msg[] = 'Missing name';
        } else {
            $this->cacheData['name'] = $data['name'];
        }
        if ($data['color']) {
            $this->cacheData['color'] = $data['color'];
        }
        return $msg;
    }

    protected function sanitizeData($data)
    {
        $name = $this->cleanInput($data['name']);
        if (!$data['alias']) {
            $data['alias'] = $name;
        }
        $alias = $this->cleanInput($data['alias']);
        $alias = $this->url->slug($alias, array('toascii'=>true,'tolower'=>true));
        $escapeData = [
            'name' => $name,
            'alias' => $alias,
            'color' => $this->cleanInput($data['color']),
            'is_active' => $data['is_active']
        ];
        return $escapeData;
    }

    public function active()
    {
        $id = $this->routeParams['id'];
        $result = $this->tagModel->save(['is_active' => 1], $id);
        if ($result) {
            $this->session->setMessage('success', 'Update successfully');
        } else {
            $this->session->setMessage('error', 'Update successfully');
        }
        $this->redirect(Helper::getAdminUrl('cms/tag'));
    }

    public function inactive()
    {
        $id = $this->routeParams['id'];
        $result = $this->tagModel->save(['is_active' => 0], $id);
        if ($result) {
            $this->session->setMessage('success', 'Update successfully');
        } else {
            $this->session->setMessage('error', 'Update successfully');
        }
        $this->redirect(Helper::getAdminUrl('cms/tag'));
    }
}