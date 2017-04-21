<?php
namespace App\Module\Cms\Model;
use Core\Model;

/**
 * Post model
 */
class Comment extends Model
{
    protected $_table = 'cms_comment';

    public function getAllBy($key, $values)
    {
        if (!$values) {
            return null;
        }
        $db = $this->getDB();
        $qMarks = str_repeat('?,', count($values) - 1) . '?';
        $sql = "SELECT c.*, u.id, u.display_name, u.username FROM {$this->_table} AS c";
        $sql .= " LEFT JOIN user AS u ON c.user_id = u.id";
        $sql .= " WHERE {$key} IN ($qMarks)";
        $sth = $db->prepare($sql);
        $sth->execute($values);
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }
}