<?php
namespace Core;
use PDO;

abstract class Model
{
    protected $_table;

    public function __construct()
    {
        if (!$this->_table) {
            throw new \Exception('Select the table for class: '.get_class($this));
        }
    }
    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
    protected function getDB()
    {
        $db = null;
        if ($db === null) {
            $host = 'localhost';
            $dbname = 'ghmvc';
            $username = 'root';
            $password = '';
            $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8';
            $db = new PDO($dsn, $username, $password);
            // Throw an Exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $db;
    }

    /**
     * Get all the posts as an object
     */
    public function getAll()
    {
        $db = $this->getDB();
        $stmt = $db->query('SELECT * FROM '. $this->_table);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}