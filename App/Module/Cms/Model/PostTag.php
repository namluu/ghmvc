<?php
namespace App\Module\Cms\Model;
use Core\Model;

/**
 * Post model
 */
class PostTag extends Model
{
    protected $_table = 'cms_post_tag';

    protected $_key = 'post_id';

    public function updatePostTag($postId, $tagIds)
    {
        parent::delete($postId);
        $data = [];
        foreach ($tagIds as $tagId) {
            $data[] = ['post_id' => $postId, 'tag_id' => $tagId];
        }
        return parent::insertMultiple($data);
    }

    public function getPostTagIds($postId)
    {
        if (!$postId) {
            return null;
        }

        $db = $this->getDB();

        $sql = "SELECT tag_id FROM {$this->_table} WHERE {$this->_key} = {$postId}";
        if ($result = $db->query($sql)) {
            return $result->fetchAll(\PDO::FETCH_COLUMN);
        }
        return [];
    }
}