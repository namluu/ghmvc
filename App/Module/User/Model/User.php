<?php
namespace App\Module\User\Model;
use Core\Model;

/**
 * Post model
 */
class User extends Model
{
    protected $_table = 'user';

    public function getFolloweds($id)
    {
        $db = $this->getDB();
        $sql = sprintf('SELECT u.* FROM %s AS r LEFT JOIN %s AS u ON r.follower_id = u.id WHERE r.followed_id = %d',
            'user_relation', $this->_table, $id);
        $sth = $db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getFollowers($id)
    {
        $db = $this->getDB();
        $sql = sprintf('SELECT u.* FROM %s AS r LEFT JOIN %s AS u ON r.followed_id = u.id WHERE r.follower_id = %d',
            'user_relation', $this->_table, $id);
        $sth = $db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getFollowedIds($id)
    {
        $db = $this->getDB();
        $sql = sprintf('SELECT u.id FROM %s AS r LEFT JOIN %s AS u ON r.follower_id = u.id WHERE r.followed_id = %d',
            'user_relation', $this->_table, $id);
        $sth = $db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public function updateRelation($followerId, $followedId, $follow = true)
    {
        $this->_table = 'user_relation';
        if ($follow) {
            $data = ['follower_id' => $followerId, 'followed_id' => $followedId];
            $this->save($data);
        } else {
            $db = $this->getDB();
            $query = $db->prepare("DELETE FROM {$this->_table} WHERE follower_id = :follower_id AND followed_id = :followed_id");
            $query->bindParam(':follower_id', $followerId, \PDO::PARAM_INT);
            $query->bindParam(':followed_id', $followedId, \PDO::PARAM_INT);
            return $query->execute();
        }
    }

    public function updateRelationVersion($followedId, $current)
    {
        $this->_table = 'user_relation';
        $this->_key = 'followed_id';
        $data = ['update_version_at' => $current];
        $this->save($data, $followedId);
    }

    public function getNotificationUser($userId)
    {
        $news = $this->getNewUpdateVersions($userId);
        foreach ($news as $key => $action) {
            if ($action->id) {
                $this->_table = 'user';
                $fromUser = $this->getOneBy('id', $action->followed_id);
                $news[$key]->content = '<strong>'.$fromUser->display_name.'</strong>';

                switch ($action->action_type) {
                    case 'post_add':
                        $news[$key]->content .= ' ' . \App\Helper::__('has a new post') . ': ';
                        $this->_table = 'cms_post';
                        $result = $this->getOneBy('id', $action->action_detail);
                        $news[$key]->content .= $result->title;
                        $news[$key]->link = \App\Helper::getLink('post/'.$result->alias);
                        $news[$key]->avatar = $fromUser->avatar;
                        $news[$key]->created_at = $action->created_at;
                        break;
                    default:
                        break;
                }
            }
        }
        return $news;
    }

    public function getNewUpdateVersions($userId)
    {
        $db = $this->getDB();
        $sql = 'SELECT r.*, a.* FROM user_action AS a';
        $sql .= ' LEFT JOIN user_relation AS r ON r.followed_id = a.user_id';
        $sql .= ' WHERE r.follower_id = '.$userId;
        $sql .= ' ORDER BY a.created_at DESC';
        $sql .= ' LIMIT 10';

        $sth = $db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }

    public function countNewUpdateVersions($userId)
    {
        $db = $this->getDB();
        $sql = 'SELECT count(a.id) FROM user_action AS a';
        $sql .= ' LEFT JOIN user_relation AS r ON r.followed_id = a.user_id';
        $sql .= ' WHERE a.created_at >= r.read_version_at AND a.created_at <= r.update_version_at';
        $sql .= ' AND r.follower_id = '.$userId;

        $sth = $db->prepare($sql);
        if ($result = $db->query($sql)) {
            return $result->fetchColumn();
        }
        return 0;
    }

    public function readNotification($userId, $current)
    {
        $this->_table = 'user_relation';
        $this->_key = 'follower_id';
        $data = ['read_version_at' => $current];
        $this->save($data, $userId);
    }
}