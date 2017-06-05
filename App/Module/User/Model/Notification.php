<?php
namespace App\Module\User\Model;
use Core\Model;

class Notification extends Model
{
    protected $_table = 'user';

    public function getNotificationUser($userId, $limit)
    {
        $news = $this->getNewUpdateVersions($userId, $limit);
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

    public function getNewUpdateVersions($userId, $limit)
    {
        $db = $this->getDB();
        $sql = 'SELECT r.*, a.* FROM user_action AS a';
        $sql .= ' LEFT JOIN user_relation AS r ON r.followed_id = a.user_id';
        $sql .= ' WHERE r.follower_id = '.$userId;
        $sql .= ' ORDER BY a.created_at DESC';
        $sql .= ' LIMIT '.$limit;

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
}