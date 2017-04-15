<?php
namespace App\Module\Cms\Model;
use Core\Model;

/**
 * Post model
 */
class Post extends Model
{
    protected $_table = 'cms_post';

    public function getAll($isActiveOnly = false)
    {
        $db = $this->getDB();
        $sql = "SELECT main.*, u.username, u.display_name, COUNT(c.id) AS comment_count 
                FROM {$this->_table} AS main";
        $sql .= ' LEFt JOIN user AS u ON main.user_id = u.id';
        $sql .= ' LEFt JOIN cms_comment AS c ON main.id = c.post_id';
        if ($isActiveOnly) {
            $sql .= ' WHERE main.is_active = 1';
        }
        $sql .= ' GROUP BY main.id';

        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getPostTagIds($postId)
    {
        $db = $this->getDB();

        $sql = "SELECT tag_id FROM cms_post_tag WHERE post_id = {$postId}";
        if ($result = $db->query($sql)) {
            return $result->fetchAll(\PDO::FETCH_COLUMN);
        }
        return [];
    }

    public function updatePostTag($postId, $tagIds)
    {
        $this->_table = 'cms_post_tag';
        $this->_key = 'post_id';
        parent::delete($postId);
        $data = [];
        foreach ($tagIds as $tagId) {
            $data[] = ['post_id' => $postId, 'tag_id' => $tagId];
        }
        return parent::insertMultiple($data);
    }
}