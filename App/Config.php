<?php
namespace App;

class Config
{
    public static $config;
    /**
     * Database
     */
    const DB_HOST = 'localhost';
    const DB_NAME = 'kipalog';
    const DB_USER = 'root';
    const DB_PASSWORD = '';

    public function loadConfig()
    {
        $model = new \App\Module\Dashboard\Model\Configuration();
        self::$config = $model->getAll();
    }

    public static function getConfig($key)
    {
        foreach (self::$config as $data) {
            if ($data->key == $key) {
                return $data->value;
            }
        }
        return null;
    }
}