<?php
namespace App\Module\Cms\Controller;
use Core\Controller;
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
        UserActionModel $action
    ) {
        $this->commentModel = $comment;
        $this->userActionModel = $action;
        $this->session = $this->getSession();
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
                if ($_POST['post_owner_id'] != $data['user_id']) {
                    $this->userActionModel->setEventNewComment($data['user_id'], $result);
                }
                $this->session->setMessage('success', 'Save successfully');
            } else {
                $this->session->setMessage('error', 'Save unsuccessfully');
            }
            $this->redirect($this->getPreviousUrl().'#comment-'.$result);
        }
    }

    public function addReplyAction()
    {
        if ($_POST) {
            if (empty($_POST['reply'])) {
                $this->session->setMessage('error', 'Missing reply content');
                $this->redirect($this->getPreviousUrl());
            }
            $data = $this->sanitizeReplyData($_POST);
            $result = $this->commentModel->save($data);
            if ($result) {
                if ($_POST['comment_owner_id'] != $data['user_id']) {
                    $this->userActionModel->setEventNewReply($data['user_id'], $result);
                }
                $this->session->setMessage('success', 'Save successfully');
            } else {
                $this->session->setMessage('error', 'Save unsuccessfully');
            }
            if (isset($_POST['comment_id'])) {
                $commentIds = explode('-', $_POST['comment_id']);
                $commentId = $commentIds[0];
            } else {
                $commentId = $result;
            }

            $this->redirect($this->getPreviousUrl().'#comment-'.$commentId);
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

    protected function sanitizeReplyData($data)
    {
        $escapeData = [
            'content' => $this->cleanInput($data['reply']),
            'user_id' => $data['user_id'],
            'post_id' => $data['post_id'],
            'parent_id' => $data['comment_id']
        ];
        return $escapeData;
    }
}