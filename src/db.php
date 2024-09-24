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
    private $queries = [];

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

    /**
     * Parses all the data in the class into an sql query.
     * @param $class
     * @return void
     */
    public function parse($class) {
        $fields = array_filter(get_class_methods($class), function($method) { //searches for available getters
            return 'get' === substr($method, 0, 3); //returns all function with "get" in its name
        });
        array_shift($fields); //removes getId from the array, since we do not need to fill that anyways
        $values = []; //empty array for the values of the getters
        foreach($fields as $f) {
            array_push($values, "'".$class->$f()."'"); //getter value is surrounding by '' so that the sql query sees them as a string
        }       
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)", 
            strtolower($class::class),
            str_replace("get", "", strtolower(implode(", ", $fields))), //removes "get" from the function name so that it can be used as row name
            implode(", ", $values)
        );
        array_push($this->queries, $query);
        return;
    }

    /**
     * Pushes all local sql queries into the database.
     * @return void
     */
    public function push() {
        try {
            $this->conn->beginTransaction();
            foreach($this->queries as $q) {
                $this->conn->exec($q);
            }
            $this->conn->commit();
        } catch (PDOException $e) {
            echo "no";
            return;
        }
    }
}
