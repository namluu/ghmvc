<?php
namespace App;

class Config
{
    /**
     * Database
     */
    const DB_HOST = 'localhost';
    const DB_NAME = 'ghmvc';
    const DB_USER = 'root';
    const DB_PASSWORD = '';

    /**
     * Debug
     */
    const SHOW_ERRORS = true;

    /**
     * Setting
     */
    const BASE_URL = 'http://ghmvc.loc/';
    const ADMIN_URI = 'admin_37wh1';
    const ADMIN_URL = self::BASE_URL.self::ADMIN_URI.'/';

    const SALT = '23drf4yy6@aw177';

    const TIMEZONE = 'Asia/Ho_Chi_Minh';
}