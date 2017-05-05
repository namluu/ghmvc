<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\Social\Facebook;
use Core\Session;
use Core\Url;
use App\Module\User\Model\User;

class Auth extends Controller
{
    protected $facebook;
    protected $session;
    protected $url;
    protected $userModel;

    public function __construct(
        array $routeParams,
        Facebook $facebook,
        Session $session,
        Url $url,
        User $user
    ) {
        $this->userModel = $user;
        $this->url = $url;
        $this->session = $session;
        $this->facebook = $facebook;
        parent::__construct($routeParams);
    }

    public function facebookAction()
    {
        $helper = $this->facebook->fb->getRedirectLoginHelper();
        $permissions = ['email'];
        $loginUrl = $helper->getLoginUrl(\App\Helper::getUrl('user/auth/fbcallback'), $permissions);
        $this->redirect($loginUrl);
    }

    public function fbcallback()
    {
        try {
            $helper = $this->facebook->fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
            $response = $this->facebook->fb->get('/me?fields=id,name,email,picture.width(160)', $accessToken);
            $me = $response->getGraphUser();
            $user = $this->userModel->getOneBy('email', $me->getEmail());
            if (!$user) {
                $username = $this->url->slug($me->getName(), array('toascii'=>true,'tolower'=>true));
                $data = [
                    'fb_id' => $me->getId(),
                    'display_name' => $me->getName(),
                    'username' => $username,
                    'email' => $me->getEmail(),
                    'avatar' => $me->getPicture()->getUrl()
                ];
                $this->userModel->save($data);
                $user = $this->userModel->getOneBy('email', $me->getEmail());
            }
            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'role' => 'user',
                'avatar' => $user->avatar
            ];
            $this->session->set('login_user', $userData);
            $this->session->setMessage('success', 'Login successfully');
            $this->redirect(\App\Helper::getUrl('user/'.$user->username));

        } catch (\Exception $e) {
            $this->session->setMessage('error', $e);
            $this->redirect(\App\Helper::getUrl('user/account/login'));
        }
    }
}