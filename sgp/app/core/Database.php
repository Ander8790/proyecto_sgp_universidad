<?php
declare(strict_types=1);

class Database
{
    private $host;
    private $db_name;
    private $user;
    private $pass;
    private $charset;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct(array $config)
    {
        $this->host = $config['host'];
        $this->db_name = $config['name'];
        $this->user = $config['user'];
        $this->pass = $config['pass'];
        $this->charset = $config['charset'];

        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=' . $this->charset;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    // Prepare statement with query
    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Bind values
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    // Execute the prepared statement
    public function execute()
    {
        return $this->stmt->execute();
    }

    // Get result set as array of objects
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get single record as object
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Get row count
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }
    
    // Get last insert ID
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
    
    // Begin transaction
    public function beginTransaction()
    {
        return $this->dbh->beginTransaction();
    }
    
    // Commit transaction
    public function commit()
    {
        return $this->dbh->commit();
    }
    
    // Rollback transaction
    public function rollback()
    {
        return $this->dbh->rollBack();
    }
}
