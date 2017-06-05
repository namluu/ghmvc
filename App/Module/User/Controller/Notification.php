<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\Session;
use Core\View;
use App\Module\User\Model\Notification as NotificationModel;

class Notification extends Controller
{
    protected $session;
    protected $notification;

    public function __construct(
        array $routeParams,
        Session $session,
        NotificationModel $notification
    ) {
        $this->session = $session;
        $this->notification = $notification;
        parent::__construct($routeParams);
    }

    public function indexAction()
    {
        $userLogin = $this->session->get('login_user');
        $notifications = $this->notification->getNotificationUser($userLogin['id'], 100);
        View::renderTemplate('User::frontend/notification/index.html', [
            'user' => $userLogin,
            'notifications' => $notifications
        ]);
    }
}