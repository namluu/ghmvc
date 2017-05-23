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
}