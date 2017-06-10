<?php
namespace App\Module\User\Model;
use Core\Model;

/**
 * Post model
 */
class Action extends Model
{
    protected $_table = 'user_action';

    const ACTION_POST_ADD = 'post_add';

    const ACTION_COMMENT_ADD = 'comment_add';

    public function updateRelation($followerId, $followedId, $follow = true)
    {
        $this->_table = 'user_relation';
        if ($follow) {
            $data = ['follower_id' => $followerId, 'followed_id' => $followedId];
            return $this->save($data);
        } else {
            $db = $this->getDB();
            $query = $db->prepare("DELETE FROM {$this->_table} WHERE follower_id = :follower_id AND followed_id = :followed_id");
            $query->bindParam(':follower_id', $followerId, \PDO::PARAM_INT);
            $query->bindParam(':followed_id', $followedId, \PDO::PARAM_INT);
            return $query->execute();
        }
    }

    public function setEventNewPost($userId, $postId)
    {
        $current = date('Y-m-d H:i:s');
        $this->save([
            'user_id' => $userId,
            'action_type' => self::ACTION_POST_ADD,
            'action_detail' => $postId,
            'created_at' => $current
        ]);
    }

    public function setEventNewComment($userId, $commentId)
    {
        $current = date('Y-m-d H:i:s');
        $this->save([
            'user_id' => $userId,
            'action_type' => self::ACTION_COMMENT_ADD,
            'action_detail' => $commentId,
            'created_at' => $current
        ]);
    }

    public function readNotification($userId)
    {
        $notiIds = $this->getUnreadNotificationId($userId);
        foreach ($notiIds as $id) {
            $this->save(['is_read' => 1], $id);
        }
    }

    public function getNotificationUser($userId, $limit)
    {
        $posts = $this->getNewPosts($userId, $limit);
        $comments = $this->getNewComments($userId, $limit);
        $news = array_merge($posts, $comments);
        usort($news, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        foreach ($news as $key => $action) {
            if ($action->id) {
                $news[$key]->content = '<strong>'.$action->display_name.'</strong>';

                switch ($action->action_type) {
                    case Action::ACTION_POST_ADD:
                        $news[$key]->content .= ' ' . \App\Helper::__('has a new post') . ': ';
                        $news[$key]->content .= '<strong>'.$action->title.'</strong>';
                        $news[$key]->link = \App\Helper::getLink('post/'.$action->alias);
                        break;
                    case Action::ACTION_COMMENT_ADD:
                        $news[$key]->content .= ' ' . \App\Helper::__('comment on your post') . ': ';
                        $news[$key]->content .= '<strong>'.$action->title.'</strong>';
                        $news[$key]->link = \App\Helper::getLink('post/'.$action->alias.'#comment-'.$action->action_detail);
                        break;
                    default:
                        break;
                }
            }
        }
        return $news;
    }

    public function getNewPosts($userId, $limit)
    {
        $db = $this->getDB();
        $sql = 'SELECT a.*,r.*, u.display_name, u.avatar, p.title, p.alias';
        $sql .= ' FROM user_action AS a';
        $sql .= ' LEFT JOIN user_relation AS r ON r.followed_id = a.user_id';
        $sql .= ' LEFT JOIN user AS u ON r.followed_id = u.id';
        $sql .= ' LEFT JOIN cms_post AS p ON p.id = a.action_detail';
        $sql .= ' WHERE r.follower_id = '.$userId;
        $sql .= ' AND a.action_type = "'.Action::ACTION_POST_ADD.'"';
        $sql .= ' ORDER BY a.created_at DESC';
        $sql .= ' LIMIT '.$limit;

        $sth = $db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getNewComments($userId, $limit)
    {
        $db = $this->getDB();
        $sql = 'SELECT a.*, u.display_name, u.avatar, p.title, p.alias';
        $sql .= ' FROM user_action AS a';
        $sql .= ' LEFT JOIN cms_comment AS c ON c.id = a.action_detail';
        $sql .= ' LEFT JOIN cms_post AS p ON c.post_id = p.id';
        $sql .= ' LEFT JOIN user AS u ON a.user_id = u.id';
        $sql .= ' WHERE p.user_id = '.$userId;
        $sql .= ' AND a.action_type = "'.Action::ACTION_COMMENT_ADD.'"';
        $sql .= ' ORDER BY a.created_at DESC';
        $sql .= ' LIMIT '.$limit;

        $sth = $db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getUnreadNotificationId($userId)
    {
        $db = $this->getDB();

        // number post new
        $sql = 'SELECT a.id FROM user_action AS a';
        $sql .= ' LEFT JOIN user_relation AS r ON r.followed_id = a.user_id';
        $sql .= ' WHERE a.is_read = 0';
        $sql .= ' AND r.follower_id = '.$userId;
        $sql .= ' AND a.action_type = "'.Action::ACTION_POST_ADD.'"';

        $sql .= ' UNION ';

        // number comment new
        $sql .= 'SELECT a.id FROM user_action AS a';
        $sql .= ' LEFT JOIN user_relation AS r ON r.followed_id = a.user_id';
        $sql .= ' LEFT JOIN cms_comment AS c ON c.id = a.action_detail';
        $sql .= ' LEFT JOIN cms_post AS p ON c.post_id = p.id';
        $sql .= ' WHERE a.is_read = 0';
        $sql .= ' AND p.user_id = '.$userId;
        $sql .= ' AND a.action_type = "'.Action::ACTION_COMMENT_ADD.'"';

        $db->prepare($sql);
        if ($result = $db->query($sql)) {
            return $result->fetchAll(\PDO::FETCH_COLUMN, 0);
        }

        return [];
    }
}