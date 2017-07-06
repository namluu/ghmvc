<?php
namespace App\Module\User\Controller;

use Core\Controller;
use Core\View;
use App\Helper;
use App\Module\User\Model\User;
use App\Module\User\Model\Action;
use App\Config;
use App\Module\Cms\Model\Post as PostModel;
use Core\Paginator;

/**
 * PHP version 7.0
 */
class Account extends Controller
{
    protected $userModel;
    protected $actionModel;
    protected $postModel;
    protected $paginator;
    protected $cacheData = [];

    public function __construct(
        array $routeParams,
        User $user,
        Action $action,
        PostModel $post,
        Paginator $paginator
    ) {
        parent::__construct($routeParams);
        $this->userModel = $user;
        $this->actionModel = $action;
        $this->postModel = $post;
        $this->paginator = $paginator;
    }

    /**
     * Show the login page
     *
     * @return void
     */
    public function loginAction()
    {
        if (isset($_GET['back-url'])) {
            $backUrl = $_GET['back-url'];
        } else {
            $backUrl = null;
        }
        View::renderTemplate('User::frontend/account/login.html', [
            'back_url' => $backUrl
        ]);
    }

    public function loginSubmitAction()
    {
        if( !$_POST ) {
            $this->redirect(Helper::getUrl('user/account/login'));
        }

        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = $this->cleanInput($_POST['email']);
            $password = $this->cleanInput($_POST['password']);
            if ( filter_var($email,FILTER_VALIDATE_EMAIL) ) {
                $user = $this->userModel->getOneBy('email', $email);
                $hash = md5(Config::getConfig('salt') . $password);
                if ($user && $user->is_active && $hash === $user->password) {
                    $userData = [
                        'id' => $user->id,
                        'username' => $user->username,
                        'display_name' => $user->display_name,
                        'role' => 'user',
                        'avatar' => $user->avatar
                    ];
                    $this->getSession()->set('login_user', $userData);
                    $this->getSession()->setMessage('success', 'Login successfully');
                    if ($_POST['back_url']) {
                        $this->redirect($_POST['back_url']);
                    } else {
                        $this->redirect(Helper::getUrl('user/'.$user->username));
                    }
                } else {
                    $this->getSession()->setMessage('error', 'Wrong account');
                    $this->redirect(Helper::getUrl('user/account/login'));
                }
            }
        }
        $this->getSession()->setMessage('error', 'Please enter your account');
        $this->redirect(Helper::getUrl('user/account/login'));
    }

    /**
     * Show the register page
     *
     * @return void
     */
    public function registerAction()
    {
        $user = $this->userModel->load();
        $user = $this->getSession()->getFormData('user_form_data', $user);
        View::renderTemplate('User::frontend/account/register.html', [
            'user' => $user
        ]);
    }

    public function registerSubmitAction()
    {
        if( !$_POST ) {
            $this->redirect(Helper::getUrl('user/account/register'));
        }

        $errorMsg = array();
        $dataCache = array();
        $username = $this->cleanInput($_POST['username']);
        $email = $this->cleanInput($_POST['email']);
        $password = $this->cleanInput($_POST['password']);
        $passwordConfirm = $this->cleanInput($_POST['confirm-password']);

        // basic name validation
        if (empty($username)) {
            $errorMsg[] = Helper::__('Please enter your username.');
        } elseif (strlen($username) < 3) {
            $errorMsg[] = 'Username must have at least 3 characters.';
        } elseif (!preg_match("/^[a-zA-Z0-9]+$/",$username)) {
            $errorMsg[] = 'Name must contain alphabets and numbers.';
        } else {
            // check name exist or not
            $count = $this->userModel->countBy(['username' => $username]);
            $countUsername = $this->userModel->countBy(['username' => $username]);
            if ($count || $countUsername) {
                $errorMsg[] = Helper::__('Provided Username is already in use.');
            }
            $dataCache['username'] = $username;
        }

        // basic email validation
        if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
            $errorMsg[] = 'Please enter valid email address.';
        } else {
            // check email exist or not
            $count = $this->userModel->countBy(['email' => $email]);
            if ($count) {
                $errorMsg[] = 'Provided Email is already in use.';
            }
            $dataCache['email'] = $email;
        }

        // password validation
        if (empty($password)){
            $errorMsg[] = "Please enter password.";
        } else if(strlen($password) < 6) {
            $errorMsg[] = 'Password must have at least 6 characters.';
        } else if ($password != $passwordConfirm) {
            $errorMsg[] = 'Please make sure your passwords match.';
        }

        if (!$errorMsg) {
            $hash = md5(Config::getConfig('salt').$password);
            $data = array(
                'display_name' => $username,
                'username' => $username,
                'password' => $hash,
                'email' => $email
            );
            $this->userModel->save($data);
            $this->getSession()->setMessage('success', 'Register successfully');
            $this->redirect(Helper::getLink('user/account/login'));
        }
        $this->getSession()->setFormData('user_form_data', $dataCache);
        $this->getSession()->setMessage('error', join('<br>', $errorMsg));
        $this->redirect(Helper::getLink('user/account/register'));
    }

    public function logout()
    {
        $this->getSession()->destroy();
        $this->redirect(Helper::getUrl());
    }

    public function indexAction()
    {
        View::renderTemplate('User::frontend/account/index.html', [
        ]);
    }

    public function viewAction()
    {
        $username = $this->routeParams['username'];
        $user = $this->userModel->getOneBy('username', $username);
        if (!$user) {
            throw new \Exception('No route matched.', 404);
        }
        $followeds = $this->userModel->getFolloweds($user->id);
        $followers = $this->userModel->getFollowers($user->id);
        $followedIds = $this->userModel->getFollowedIds($user->id);
        $limit = \App\Config::getConfig('pagination_frontend');
        $page = $this->routeParams['page'];
        $posts = $this->postModel->getAllBy('user_id', [$user->id], true, $limit, $page);
        $totalRows = $this->postModel->countBy(['is_active' => 1, 'user_id' => $user->id]);
        $paginator = $this->paginator->init($totalRows, $limit, $page, $this->routeParams);
        View::renderTemplate('User::frontend/account/view.html', [
            'user' => $user,
            'posts' => $posts,
            'paginator' => $paginator,
            'followeds' => $followeds,
            'followers' => $followers,
            'followedIds' => $followedIds
        ]);
    }

    public function editAction()
    {
        $id = $this->routeParams['id'];
        $loginUser = $this->getSession()->get('login_user');
        if ($loginUser['id'] != $id) {
            throw new \Exception('No route matched.', 404);
        }
        $user = $this->userModel->getOneBy('id', $id);
        if (!$user) {
            throw new \Exception('User not found', 500);
        }
        View::renderTemplate('User::frontend/account/edit.html', [
            'user' => $user
        ]);
    }

    public function saveAction()
    {
        if ($_POST) {
            $id = (int)$_POST['id'];
            $user = $this->userModel->getOneBy('id', $id);
            if (!$user) {
                throw new \Exception('User not found', 500);
            }
            $errorMsg = $this->validateData($_POST, $user->email);
            if ($errorMsg) {
                $this->getSession()->setFormData('user_form_data_edit', $this->cacheData);
                $this->getSession()->setMessage('error', implode(', ', $errorMsg));
                $id ? $this->redirect(Helper::getUrl("user/account/{$id}/edit")) :
                    $this->redirect(Helper::getUrl('user/'.$user->username));
            } else {
                $data = $this->sanitizeData($_POST);
                $result = $this->userModel->save($data, $id);

                if ($result) {
                    $this->getSession()->setMessage('success', 'Save successfully');
                } else {
                    $this->getSession()->setMessage('error', 'Save unsuccessfully');
                }
            }
            $this->redirect(Helper::getUrl('user/'.$user->username));
        }
        throw new \Exception('No route matched.', 404);
    }

    protected function validateData($data, $email)
    {
        $msg = array();
        if (empty($data['display_name'])) {
            $msg[] = 'Missing name';
        } else {
            $this->cacheData['display_name'] = $data['display_name'];
        }
        if (empty($data['email'])) {
            $msg[] = 'Missing email';
        } else {
            if ( !filter_var($data['email'],FILTER_VALIDATE_EMAIL) ) {
                $msg[] = 'Please enter valid email address.';
            } else {
                // check email exist or not
                if ($data['email'] != $email) {
                    $count = $this->userModel->countBy(['email' => $data['email']]);
                    if ($count) {
                        $msg[] = 'Provided Email is already in use.';
                    }
                }

                $this->cacheData['email'] = $data['email'];
            }
        }

        return $msg;
    }

    protected function sanitizeData($data)
    {
        $escapeData = [
            'display_name' => $this->cleanInput($data['display_name']),
            'email' => $this->cleanInput($data['email']),
            'avatar' => $data['avatar']
        ];
        return $escapeData;
    }

    public function upload()
    {
        $upload_handler = new \Core\UploadHandler(array(
            'upload_dir' => Helper::getPath('public/uploads/user/'),
            'upload_url' => Helper::getUrl('uploads/user/'),
            'image_versions' => array(
                '' => array(
                    'auto_orient' => true
                ),
                'thumbnail' => array(
                    'max_width' => 150,
                    'max_height' => 150,
                    'crop' => true
                )
            )
        ));
    }

    public function follow()
    {
        $this->updateRelation(true);
    }

    public function unfollow()
    {
        $this->updateRelation(false);
    }

    protected function updateRelation($follow = true)
    {
        if ($_POST) {
            $followerId = (int)$_POST['follower_id'];
            $user = $this->userModel->getOneBy('id', $followerId);
            if (!$user) {
                throw new \Exception('User not found', 500);
            }
            $followedId = (int)$_POST['followed_id'];
            $user = $this->userModel->getOneBy('id', $followedId);
            if (!$user) {
                throw new \Exception('User not found', 500);
            }
            $this->actionModel->updateRelation($followerId, $followedId, $follow);
        }
        $this->redirect($this->getPreviousUrl());
    }

    public function readNotification()
    {
        if ($_POST) {
            $userId = (int)$_POST['user_id'];
            $user = $this->userModel->getOneBy('id', $userId);
            if (!$user) {
                throw new \Exception('User not found', 500);
            }
            $this->actionModel->readNotification($userId);
            echo json_encode('');
        }
    }
}