<?php
namespace Core;
use PDO;
use App\Config;

abstract class Model
{
    protected $_table;

    protected $_key = 'id';

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
     *
     * @param bool $isActiveOnly
     * @return object
     */
    public function getAll($isActiveOnly = false)
    {
        $db = $this->getDB();
        $sql = sprintf('SELECT * FROM %s', $this->_table);
        if ($isActiveOnly) {
            $sql .= sprintf(' WHERE %s', 'is_active = 1');
        }

        return $db->query($sql, PDO::FETCH_OBJ);
    }

    /**
     * Get one record by column
     *
     * @param string $key
     * @param string $value
     * @return object
     */
    public function getOneBy($key, $value)
    {
        $db = $this->getDB();
        $query = $db->prepare("SELECT * FROM {$this->_table} WHERE {$key} = :value LIMIT 1");
        $query->bindParam(':value', $value);
        $query->execute();
        return $query->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get all record by column
     *
     * @param string $key
     * @param array $values
     * @return object
     */
    public function getAllBy($key, $values)
    {
        if (!$values) {
            return null;
        }
        $db = $this->getDB();
        $qMarks = str_repeat('?,', count($values) - 1) . '?';
        $sth = $db->prepare("SELECT * FROM {$this->_table} WHERE {$key} IN ($qMarks)");
        $sth->execute($values);
        return $sth->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Load one data as an object
     *
     * @param int $id
     *
     * @return object
     */
    public function load($id = null)
    {
        $db = $this->getDB();
        if ($id) {
            $query = $db->prepare("SELECT * FROM {$this->_table} WHERE id = :id");
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_OBJ);
        } else {
            $query = $db->query("SELECT * FROM {$this->_table} LIMIT 0");
            $emptyObject = new \stdClass();
            $num = $query->columnCount();
            for ($i = 0; $i < $num; $i++) {
                $col = $query->getColumnMeta($i);
                $columnName = $col['name'];
                $emptyObject->$columnName = '';
            }
            return $emptyObject;
        }
    }

    public function save($data, $id = null)
    {
        $db = $this->getDB();
        $values       = [];
        $bind         = [];
        if (!$id) {
            foreach ($data as $row) {
                $values[] = '?';
                $bind[] = $row;
            }
            $line = implode(', ', $values);
            $line = sprintf('(%s)', $line);
            $cols = array_keys($data);
            $insertQuery = $this->_getInsertSqlQuery($cols, [$line]);
            $stmt = $db->prepare($insertQuery);
            return $stmt->execute($bind);
        } else {
            foreach ($data as $key => $row) {
                $values[] = $key . '=?';
                $bind[] = $row;
            }
            $updateQuery = $this->_getUpdateSqlQuery($values, $id);
            $stmt = $db->prepare($updateQuery);
            return $stmt->execute($bind);
        }
    }

    public function delete($id)
    {
        $id = (int)$id;
        $db = $this->getDB();
        $query = $db->prepare("DELETE FROM {$this->_table} WHERE {$this->_key} = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    public function countBy($key, $value)
    {
        $db = $this->getDB();

        $sql = "SELECT COUNT(*) FROM {$this->_table} WHERE {$key} = '{$value}'";
        if ($result = $db->query($sql)) {
            return $result->fetchColumn();
        }
        return 0;
    }

    public function insertMultiple(array $data)
    {
        $row = reset($data);
        // support insert syntaxes
        if (!is_array($row)) {
            return $this->save($data);
        }
        // validate data array
        $cols = array_keys($row);
        $insertArray = [];
        foreach ($data as $row) {
            $line = [];
            if (array_diff($cols, array_keys($row))) {
                throw new \Exception('Invalid data for insert');
            }
            foreach ($cols as $field) {
                $line[] = $row[$field];
            }
            $insertArray[] = $line;
        }
        unset($row);
        return $this->insertArray($cols, $insertArray);
    }

    /**
     * Insert array into a table based on columns definition
     *
     * $data can be represented as:
     * - arrays of values ordered according to columns in $columns array
     *      array(
     *          array('value1', 'value2'),
     *          array('value3', 'value4'),
     *      )
     * - array of values, if $columns contains only one column
     *      array('value1', 'value2')
     *
     * @param   array $columns
     * @param   array $data
     * @return  int
     * @throws  \Exception
     */
    public function insertArray(array $columns, array $data)
    {
        $db = $this->getDB();
        $values       = [];
        $bind         = [];
        $columnsCount = count($columns);
        foreach ($data as $row) {
            if ($columnsCount != count($row)) {
                throw new \Exception('Invalid data for insert');
            }
            $values[] = $this->_prepareInsertData($row, $bind);
        }

        $insertQuery = $this->_getInsertSqlQuery($columns, $values);
        $stmt = $db->prepare($insertQuery);
        return $stmt->execute($bind);
    }

    protected function _prepareInsertData($row, &$bind)
    {
        $row = (array)$row;
        $line = [];
        foreach ($row as $value) {
            $line[] = '?';
            $bind[] = $value;
        }
        $line = implode(', ', $line);

        return sprintf('(%s)', $line);
    }

    protected function _getInsertSqlQuery(array $columns, array $values)
    {
        $columns   = implode(',', $columns);
        $values    = implode(', ', $values);

        $insertSql = sprintf('INSERT INTO %s (%s) VALUES %s', $this->_table, $columns, $values);

        return $insertSql;
    }

    protected function _getUpdateSqlQuery(array $values, $id)
    {
        $values    = implode(', ', $values);

        $updateSql = sprintf('UPDATE %s SET %s WHERE %s = %d', $this->_table, $values, $this->_key, $id);

        return $updateSql;
    }
}