<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\View;
use App\Module\User\Model\Action;

class Notification extends Controller
{
    protected $session;
    protected $action;

    public function __construct(
        array $routeParams,
        Action $action
    ) {
        $this->session = $this->getSession();
        $this->action = $action;
        parent::__construct($routeParams);
    }

    public function indexAction()
    {
        $userLogin = $this->session->get('login_user');
        $notifications = $this->action->getNotificationUser($userLogin['id'], 100);
        View::renderTemplate('User::frontend/notification/index.html', [
            'user' => $userLogin,
            'notifications' => $notifications
        ]);
    }
}