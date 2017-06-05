<?php
namespace App\Module\User\Model;
use Core\Model;

/**
 * Post model
 */
class Action extends Model
{
    protected $_table = 'user_action';

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

    public function updateRelationVersion($followedId, $current)
    {
        $this->_table = 'user_relation';
        $this->_key = 'followed_id';
        $data = ['update_version_at' => $current];
        $this->save($data, $followedId);
    }
}