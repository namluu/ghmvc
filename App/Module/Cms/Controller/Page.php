<?php
namespace App\Module\Cms\Controller;
use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Page as PageModel;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Page extends Controller
{
    protected $pageModel;

    public function __construct(array $routeParams, PageModel $page)
    {
        $this->pageModel = $page;
        parent::__construct($routeParams);
    }

    public function viewAction()
    {
        if (isset($this->routeParams['url'])) {
            $url = $this->routeParams['url'];
            $page = $this->pageModel->getOneBy('url', $url);
        } elseif (isset($this->routeParams['id'])) {
            $id = $this->routeParams['id'];
            $page = $this->pageModel->getOneBy('id', $id);
        } else {
            throw new \Exception('Page not found.', 404);
        }

        if (!$page) {
            throw new \Exception('Page not found.', 404);
        }
        View::renderTemplate('Cms::frontend/page/view.html', [
            'page' => $page
        ]);
    }
}