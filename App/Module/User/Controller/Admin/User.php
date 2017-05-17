<?php
namespace App\Module\User\Controller\Admin;
use Core\Controller;
use Core\View;
use App\Module\User\Model\User as UserModel;

class User extends Controller
{
    protected $userModel;

    public function __construct(
        array $routeParams,
        UserModel $userModel
    ) {
        $this->userModel = $userModel;
        parent::__construct($routeParams);
    }

    public function indexAction()
    {
        $limit = \App\Config::getConfig('pagination_backend');
        $page = $this->routeParams['page'];
        $users = $this->userModel->getAll(false, $limit, $page);
        View::renderTemplate('User::backend/user/index.html', [
            'users' => $users
        ]);
    }
}