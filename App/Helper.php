<?php
namespace App;

class Helper
{
    public static function getUrl($url = '') {
        return Config::BASE_URL . $url;
    }

    public static function getAdminUrl($url = '') {
        return Config::ADMIN_URL . $url;
    }
}