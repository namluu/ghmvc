<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\Social\Facebook;
use Core\Social\Google;
use Core\Social\Github;
use Core\Url;
use App\Module\User\Model\User;

class Auth extends Controller
{
    protected $facebook;
    protected $session;
    protected $url;
    protected $userModel;
    protected $google;
    protected $github;

    public function __construct(
        array $routeParams,
        Facebook $facebook,
        Google $google,
        Github $github,
        Url $url,
        User $user
    ) {
        $this->userModel = $user;
        $this->url = $url;
        $this->session = $this->getSession();
        $this->facebook = $facebook;
        $this->google = $google;
        $this->github = $github;
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

    public function googleAction()
    {
        $this->google->gClient->setRedirectUri(\App\Helper::getUrl('user/auth/ggcallback'));
        $authUrl = $this->google->gClient->createAuthUrl();
        $this->redirect($authUrl);
    }

    public function ggcallback()
    {
        /**
         * Invalid parameter value for redirect_uri:
         * Non-public domains not allowed: http://ghmvc.loc/user/auth/ggcallback
         */
        $a = 5;
    }

    public function githubAction()
    {
        $authUrl = $this->github->get_login_url();
        $this->redirect($authUrl);
    }

    public function ghcallback()
    {
        $isAuthorized = $this->github->authorize();
        //Login failed!
        if (!$isAuthorized)
        {
            $this->session->setMessage('error', $this->github->get_error());
            $this->redirect(\App\Helper::getUrl('user/account/login'));
        }
        //Login success!
        else
        {
            $userProfile = (array)$this->github->user();
            $userProfile['email'] = $this->github->user_email();
            if ($userProfile['email']) {
                $user = $this->userModel->getOneBy('email', $userProfile['email']);
                if (!$user) {
                    $username = $this->url->slug($userProfile['login'], array('toascii'=>true,'tolower'=>true));
                    $data = [
                        'gh_id' => $userProfile['id'],
                        'display_name' => $userProfile['name'],
                        'username' => $username,
                        'email' => $userProfile['email'],
                        'avatar' => $userProfile['avatar_url']
                    ];
                    $this->userModel->save($data);
                    $user = $this->userModel->getOneBy('email', $userProfile['email']);
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
            }
        }
        $this->session->setMessage('error', 'Github callback fail');
        $this->redirect(\App\Helper::getUrl('user/account/login'));
    }
}