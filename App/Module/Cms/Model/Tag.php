<?php
namespace App\Module\Cms\Model;
use Core\Model;

/**
 * Tag model
 */
class Tag extends Model
{
    protected $_table = 'cms_tag';

    public function getColors()
    {
        return [
            'label-muted' => '#777',
            'label-danger' => '#a94442',
            'label-primary' => '#337ab7',
            'label-success' => '#3c763d',
            'label-info' => '#31708f',
            'label-warning' => '#8a6d3b'
        ];
    }

    public function getRandomColor()
    {
        return array_rand($this->getColors());
    }

    public function getHotTags($num)
    {
        $db = $this->getDB();
        $sql = sprintf('SELECT t.name, t.alias, COUNT(pt.post_id) AS num_post');
        $sql .= sprintf(' FROM %s AS t', $this->_table);
        $sql .= sprintf(' LEFT JOIN %s AS pt ON t.id = pt.tag_id', 'cms_post_tag');
        $sql .= sprintf(' WHERE %s', 'is_active = 1');
        $sql .= sprintf(' GROUP BY pt.tag_id');
        $sql .= sprintf(' ORDER BY %s DESC', 'num_post');
        $sql .= sprintf(' LIMIT %s', $num);

        $sth = $db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }
}