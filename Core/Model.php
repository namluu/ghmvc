<?php
namespace Core;
use PDO;
use App\Config;

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
            $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME. ';charset=utf8';
            $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);
            // Throw an Exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $db;
    }

    /**
     * Get all data as an object
     */
    public function getAll()
    {
        $db = $this->getDB();
        $stmt = $db->query("SELECT * FROM {$this->_table}");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Load one data as an object
     *
     * @param int $id
     *
     * @return object
     */
    public function load($id)
    {
        $db = $this->getDB();
        if ($id) {
            $query = $db->prepare("SELECT * FROM {$this->_table} WHERE id = :id");
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_OBJ);
        } else {
            $columns = [];
            $query = $db->query("SELECT * FROM {$this->_table} LIMIT 0");
            for ($i = 0; $i < $query->columnCount(); $i++) {
                $col = $query->getColumnMeta($i);
                $columns[] = $col['name'];
            }
            return $columns;
        }
    }

    public function save($data, $id = null)
    {
        $db = $this->getDB();
        if (!$id) {
            $fields = '';
            $values = '';
            foreach ($data as $field => $value) {
                $fields .= "$field,";
                $values .= (is_numeric($value) && (intval($value) == $value)) ? $value . ',' : "'$value',";
            }
            // remove our trailing
            $fields = substr($fields, 0, -1);
            // remove our trailing
            $values = substr($values, 0, -1);
            $insert = "INSERT INTO {$this->_table} ({$fields}) VALUES ({$values})";
            return $db->query($insert);
        } else {
            $update = "UPDATE {$this->_table} SET ";
            foreach ($data as $field => $value) {
                $update .= $field . " = '{$value}',";
            }
            // remove our trailing ,
            $update = substr($update, 0, -1);
            $update .= " WHERE id = " . $id;
            return $db->query($update);
        }
    }

    public function delete($id)
    {
        $id = (int)$id;
        $db = $this->getDB();
        $query = $db->prepare("DELETE FROM {$this->_table} WHERE id = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }
}