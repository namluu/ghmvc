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

    public function init()
    {
        /**
         * Init application
         */
        date_default_timezone_set($this->getConfig('timezone'));

        /**
         * Error and Exception handling
         */
        error_reporting(E_ALL);
        set_error_handler('Core\Error::errorHandler');
        set_exception_handler('Core\Error::exceptionHandler');

        /**
         * Session
         */
        session_start();

        /**
         * Define
         */
        define ('DS', DIRECTORY_SEPARATOR);
        define ('ROOT', dirname(dirname(__FILE__)));


    }
}