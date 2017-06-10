<?php
namespace App\Module\Cms\Controller;
use Core\Controller;
use Core\Session;
use App\Module\Cms\Model\Comment as CommentModel;
use App\Module\User\Model\Action as UserActionModel;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Comment extends Controller
{
    protected $commentModel;
    protected $session;
    protected $userActionModel;

    public function __construct(
        array $routeParams,
        CommentModel $comment,
        Session $session,
        UserActionModel $action
    ) {
        $this->commentModel = $comment;
        $this->userActionModel = $action;
        $this->session = $session;
        parent::__construct($routeParams);
    }

    public function addAction()
    {
        if ($_POST) {
            if (empty($_POST['content'])) {
                $this->session->setMessage('error', 'Missing content');
                $this->redirect($this->getPreviousUrl());
            }
            $data = $this->sanitizeData($_POST);
            $result = $this->commentModel->save($data);
            if ($result) {
                $this->userActionModel->setEventNewComment($data['user_id'], $result);
                $this->session->setMessage('success', 'Save successfully');
            } else {
                $this->session->setMessage('error', 'Save unsuccessfully');
            }
            $this->redirect($this->getPreviousUrl().'#comment-'.$result);
        }
    }

    protected function sanitizeData($data)
    {
        $escapeData = [
            'content' => $this->cleanInput($data['content']),
            'user_id' => $data['user_id'],
            'post_id' => $data['post_id']
        ];
        return $escapeData;
    }
}