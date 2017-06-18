<?php
namespace App\Module\Cms\Controller;

use Core\Controller;
use Core\View;
use App\Module\Cms\Model\Tag as TagModel;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Tag extends Controller
{
    protected $tagModel;

    public function __construct(
        array $routeParams,
        TagModel $tag
    ) {
        $this->tagModel = $tag;
        parent::__construct($routeParams);
    }

    public function viewAction()
    {
        if (isset($this->routeParams['alias'])) {
            $alias = $this->routeParams['alias'];
            $tag = $this->tagModel->getOneBy('alias', $alias);
        } elseif (isset($this->routeParams['id'])) {
            $id = $this->routeParams['id'];
            $tag = $this->tagModel->getOneBy('id', $id);
        } else {
            throw new \Exception('Tag not found.', 404);
        }

        if (!$tag) {
            throw new \Exception('Tag not found.', 404);
        }

        View::renderTemplate('Cms::frontend/tag/view.html', [
            'tag' => $tag
        ]);
    }
}