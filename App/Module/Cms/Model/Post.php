<?php
namespace App\Module\Cms\Model;
use Core\Model;

/**
 * Post model
 */
class Post extends Model
{
    protected $_table = 'cms_post';

    public function getAll($isActiveOnly = false, $limit = 10, $page = 1, $isHot = false)
    {
        $db = $this->getDB();
        $sql = "SELECT main.*, u.username, u.display_name, u.avatar, COUNT(c.id) AS comment_count 
                FROM {$this->_table} AS main";
        $sql .= ' LEFt JOIN user AS u ON main.user_id = u.id';
        $sql .= ' LEFt JOIN cms_comment AS c ON main.id = c.post_id';
        if ($isActiveOnly) {
            $sql .= ' WHERE main.is_active = 1';
        }
        if ($isHot) {
            $sql .= ' AND main.is_hot = 1';
        }
        $sql .= ' GROUP BY main.id';
        $sql .= ' ORDER BY created_at DESC';

        if ($limit != 'all') {
            $sql .= ' LIMIT '. ($page - 1) * $limit . ',' . $limit;
        }

        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getAllBy($key, $values, $isActiveOnly = false, $limit = 10, $page = 1)
    {
        if (!$values) {
            return null;
        }
        $db = $this->getDB();
        $qMarks = str_repeat('?,', count($values) - 1) . '?';
        $sql = "SELECT * FROM {$this->_table} WHERE {$key} IN ($qMarks)";
        if ($isActiveOnly) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY created_at DESC';
        if ($limit != 'all') {
            $sql .= ' LIMIT '. ($page - 1) * $limit . ',' . $limit;
        }
        $sth = $db->prepare($sql);
        $sth->execute($values);
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }
}