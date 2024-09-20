<?php

/**
 * The database manager for the AzeleaCore
 */
class DatabaseManager
{
    private $servername;
    private $database;
    private $username;
    private $password;
    private $conn;

    /**
     * When the DatabaseManager is construced, 
     * the connection details is loaded from the 
     * .env.local file
     */
    public function __construct()
    {
        $this->servername = $_ENV["DB_HOST"];
        $this->database = $_ENV["DB_NAME"];
        $this->username = $_ENV["DB_USERNAME"];
        $this->password = $_ENV["DB_PASSWORD"];

        try {
            $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->database", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            //insert faultmanager here
        }
    }

    /**
     * Insert an sql query into the database
     * @param string $sql
     */
    public function addSql(string $sql)
    {
        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            //insert faultmanager here
        }
    }

    public function closeConnection() {
        $this->conn = null;
    }
}

$db = new DatabaseManager();