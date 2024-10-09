<?php
namespace Azelea\Core;

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
            $this->conn = new \PDO("mysql:host=$this->servername;dbname=$this->database", $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
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
        } catch (\PDOException $e) {
            //insert faultmanager here
        }
    }

    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Pulls the class from the database.
     * @param string $class name of the class. Can be given with ClassExample::class
     * @param int $id optional
     */
    public function getModel($class, int $id = null) {
        $c = new $class();
        
        $fields = array_filter(get_class_methods($c), function($method) {
            return 'get' === substr($method, 0, 3);
        });
        
        $where = ($id) ? " WHERE id=$id" : "";
        $query = sprintf(
            "SELECT %s FROM %s%s",
            str_replace("get", "", strtolower(implode(", ", $fields))),
            str_replace("azelea\\core\\", "", strtolower($class)),
            $where
        );
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $data = $stmt->fetchAll();
        
        if (empty($data)) {
            return null; // Handle case where no data is returned
        }
        $fields2 = array_filter(get_class_methods($c), function($method) {
            return 'set' === substr($method, 0, 3);
        });

        $fieldsMap = [];
        foreach ($fields2 as $setter) {
            $fieldName = strtolower(substr($setter, 3)); // Remove 'set' and lowercase
            $fieldsMap[$fieldName] = $setter;
        }

        $firstRow = $data[0];
        foreach ($firstRow as $column => $value) {
            if (array_key_exists($column, $fieldsMap)) {
                $setter = $fieldsMap[$column];
                $c->$setter($value);
            }
        }
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty('id'); // Change 'id' to the actual private property name
        $property->setAccessible(true);
        $property->setValue($c, $id);
        return $c;
    }    

    /**
     * Parses all the data in the class into an sql query.
     * @param class $class
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
            str_replace("azelea\\core\\", "", strtolower($class::class)),
            str_replace("get", "", strtolower(implode(", ", $fields))), //removes "get" from the function name so that it can be used as row name
            implode(", ", $values)
        );
        array_push($this->queries, $query); //adds the current query to the backlog
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
        } catch (\PDOException $e) {
            echo "no";
            return;
        }
    }
}
