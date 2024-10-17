<?php
namespace Azelea\Core\Database;
use Azelea\Core\Core;

/**
 * The database manager for the AzeleaCore
 */
class DatabaseManager
{
    private $conn;
    private $queries = [];

    /**
     * When the DatabaseManager is construced, 
     * the connection details is loaded from the 
     * .env.local file
     */
    public function __construct()
    {
        $this->openConnection();
    }

    public function openConnection() {
        $servername = $_ENV["DB_HOST"];
        $database = $_ENV["DB_NAME"];
        $username = $_ENV["DB_USERNAME"];
        $password = $_ENV["DB_PASSWORD"];
        try {
            $this->conn = new \PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            return Core::error($e);
        }
    }

    /**
     * Closes the database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Insert an sql query into the database
     * @param string $sql
     */
    public function addSql(string $sql)
    {
        try {
            return $this->conn->exec($sql);
        } catch (\PDOException $e) {
            return Core::error($e);
        }
    }

    /**
     * Pulls the class from the database.
     * Will return all from class type if the ID is left empty.
     * @param string $class name of the class. Can be given with ClassExample::class
     * @param int $id The id of the model (optional)
     */
    public function getModel($class, int $id = null) {
        $c = new $class();
        $reflection = new \ReflectionClass($c);

        $tableName = str_replace("azelea\\core\\", "", strtolower($class));
        $columns = str_replace("get", "", strtolower(implode(", ", array_keys($reflection->getDefaultProperties()))));
    
        $all = function() use ($tableName, $columns, $c) {
            $query = sprintf("SELECT %s FROM %s", $columns, $tableName);
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, get_class($c));
            return $stmt->fetchAll();
        };
    
        $one = function($id) use ($tableName, $columns, $c) {
            $query = is_string($id) ? 
                sprintf("SELECT %s FROM %s WHERE :auth = :id", $columns, $tableName) : 
                sprintf("SELECT %s FROM %s WHERE id = :id", $columns, $tableName);
            $stmt = $this->conn->prepare($query);
            if (is_string($id)) $stmt->bindParam(':auth', $this->getAuthDetails(), \PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, get_class($c));
            $data = $stmt->fetchAll();
            return $data[0];
        };
    
        try {
            return $id !== null ? $one($id) : $all();
        } catch (\PDOException $e) {
            Core::error($e);
        }
    }

    /**
     * Parses all the data in the class into an sql query.
     * @param class $class
     * @return void
     */
    public function parse($class) {
        $reflection = new \ReflectionClass($class);
        $properties = $reflection->getDefaultProperties();
        unset($properties['id']); // Removed to prevent clashing with primary key
    
        $fields = [];
        $values = [];
    
        foreach ($properties as $property => $value) {
            $getter = 'get' . ucfirst($property);
            if (method_exists($class, $getter)) {
                $fields[] = strtolower($property);
                $values[] = $this->conn->quote($class->$getter());
            }
        }

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)", 
            str_replace("azelea\\core\\", "", strtolower($class::class)),
            implode(", ", $fields),
            implode(", ", $values)
        );
    
        array_push($this->queries, $query);
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
            return Core::error($e);
        }
    }

    /**
     * Logs the user in and stores it in the session.
     * @param string $class The name of the class
     * @param mixed $form The form where the POST data is stored
     * @return void|null
     */
    public function login(string $class, $form) {
        $config = $this->getAuthDetails();
        switch($config) {
            case "email":
                $email = $form->getData("email");
                Core::dd($email);
                return;
            case "username":
                $username = $form->getData("username");
                return;
            default;
                return null;
        }
    }

    /**
     * Returns the user login identifier from the config file.
     * @return JSON
     */
    private function getAuthDetails() {
        $config = file_get_contents(substr(dirname(__DIR__), 0, strpos(dirname(__DIR__), "\\vendor\\")) . "/config.json");
        $json = json_decode($config);
        return $json->authentication->identifier;
    }
}
