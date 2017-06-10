<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\Session;
use Core\View;
use App\Module\User\Model\Action;

class Notification extends Controller
{
    protected $session;
    protected $action;

    public function __construct(
        array $routeParams,
        Session $session,
        Action $action
    ) {
        $this->session = $session;
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