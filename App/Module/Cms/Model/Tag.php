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
}