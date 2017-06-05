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

    public function readNotification($userId, $current)
    {
        $this->_table = 'user_relation';
        $this->_key = 'follower_id';
        $data = ['read_version_at' => $current];
        $this->save($data, $userId);
    }
}