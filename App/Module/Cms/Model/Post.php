<?php
namespace App\Module\Cms\Model;
use Core\Model;

/**
 * Post model
 */
class Post extends Model
{
    protected $_table = 'cms_post';

    public function getAll()
    {
        $db = $this->getDB();
        $sql = "SELECT main.*, u.username, u.display_name FROM {$this->_table} AS main";
        $sql .= ' LEFt JOIN user AS u ON main.user_id = u.id';
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}