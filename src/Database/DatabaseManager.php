<?php
namespace Azelea\Core\Database;
use Azelea\Core\Core;
use Azelea\Core\Session;

/**
 * The database manager for the AzeleaCore
 */
class DatabaseManager
{
    private $conn;
    private static array $queries = [];

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
            Core::error($e);
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
        return array_push(self::$queries, $sql);
    }

    /**
     * Pulls the class from the database.
     * Will return all from class type if the ID is left empty.
     * @param string $class name of the class. Can be given with ClassExample::class
     * @param mixed $id The id of the model (optional)
     * @return object
     */
    public function getModel($class, $id = null) {
        $c = new $class();
        $reflection = new \ReflectionClass($c);

        $tableName = str_replace("azelea\\core\\", "", strtolower($class));
        $columns = $this->getColumns($reflection->getProperties());

        $all = function() use ($tableName, $columns, $c) {
            $query = sprintf("SELECT %s FROM %s", $columns, $tableName);
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS, get_class($c));
            return $stmt->fetchAll();
        };
    
        $one = function($id) use ($tableName, $columns, $c) {
            $query = (!is_numeric($id)) ? 
                sprintf("SELECT %s FROM %s WHERE :auth = :id", $columns, $tableName) : 
                sprintf("SELECT %s FROM %s WHERE id = :id", $columns, $tableName);
            $stmt = $this->conn->prepare($query);
            (!is_numeric($id)) ? $stmt->bindParam(':auth', $this->getAuthDetails(), \PDO::PARAM_STR) : $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
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
     * @param object $class
     * @return int The number of queries stored
     */
    public function parse($class) {
        $reflection = new \ReflectionClass($class);
        $properties = $reflection->getProperties();
        if ($properties[0]->name == "id") unset($properties[0]); // Removed to prevent clashing with primary key
        $columns = $this->getColumns($properties);
    
        $values = [];
        foreach ($properties as $property) {
            $getter = 'get' . ucfirst($property->getName());
            
            if (method_exists($class, $getter)) {
                $values[] = $this->conn->quote($class->$getter());
            }
        }

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)", 
            str_replace("azelea\\core\\", "", strtolower($class::class)),
            $columns,
            implode(", ", $values)
        );
    
        return array_push(self::$queries, $query);
    }
    
    /**
     * Get the property names from class and parses them into SQL readable.
     * @param mixed $properties
     * @return string
     */
    private function getColumns($properties) {
        $pattern = '/Property \[ private (?:int|string) \$(.*?) \]/'; //TODO: needs dynamic type replacing
        $replacement = '$1';
        $result = preg_replace($pattern, $replacement, implode(", ", $properties));
        $result = preg_replace('/\s*,/', ',', $result);
        return strtolower($result);
    }

    /**
     * Pushes all local sql queries into the database.
     * @return void
     */
    public function push() {
        try {
            $this->conn->beginTransaction();
            foreach(self::$queries as $q) {
                $this->conn->exec($q);
            }
            self::$queries = [];
            $this->conn->commit();
        } catch (\PDOException $e) {
            return Core::error($e);
        }
    }

    /**
     * Logs the user in and stores it in the session.
     * @param string $class The name of the class
     * @param mixed $form The form where the POST data is stored
     * @return object|false
     */
    public function login(string $class, $form) {
        $config = $this->getAuthDetails();
        $id = $form->getData($config);
        $user = $this->getModel($class, $id);
        if (password_verify($form->getData("password"), $user->getPassword())) {
            $uuid = $this->generateToken();
            $userId = $user->getId();
            //there should also be a section to check if this id already has a token.
            //if so, that token should be revoked and this one should be used instead.
            //in config, there should be a limit of tokens a single ID can have.
            //like five tokens, meaning that id can login to 5 different browsers before 
            //the oldest token starts to get revoked.
            $this->addSql("INSERT INTO tokens (uuid, user_id) VALUES ('$uuid', '$userId')");
            $this->push();
            Session::setSessionKey("user_id", $uuid);
            return $user;
        } else {
            Session::addFlash("Email and password do not match", "danger");
            return false;
        }
    }

    /**
     * Authenticates the user from out of the session.
     * @param string $token The token that is stored inside the session
     * @return void
     */
    private function AuthUser(string $token) {
        $id = explode("_", $token); 
        if ($id[0] != $this->getAuthDetails()) Core::dd("no");
    }

    /**
     * Generates a token. 
     * Can also be used for generating API tokens.
     */
    public function generateToken(int $length = 32): string
    {
        $token = "";
        try {
            $bytesWithMargin = random_bytes($length*3);
            $base64 = base64_encode($bytesWithMargin);
            $purified = preg_replace("/[+=\/.]/", "", $base64);
            $token = substr($purified, 0, $length);
        } catch (\Exception $e) { 
            Core::error($e);
        }

        return $token;
    }

    /**
     * Returns the user login identifier from the config file.
     * @return string The identifier type
     */
    private function getAuthDetails() {
        return $_ENV['AUTH_ID'];
    }

    /**
     * Checks all Models and compares them to
     * the database counterpart.
     * If any information is missing, it 
     * creates it inside the migration.
     * 
     * Also creates a file in /migrations containing
     * all the information that needs to be parsed.
     */
    public function createMigration() {
        
    }

    /**
     * Goes through the migration folders and checks them off one for one.
     * @return void Pushes the queries to db
     */
    public function migrate() {
        $dir = substr(dirname(__DIR__), 0, strpos(dirname(__DIR__), "\\vendor\\"));
        $d = glob($dir."\\migrations\\*.php");
        // Core::dd(str_replace($dir."\\migrations\\", "", str_replace(".php", "", $d[0])));
        foreach ($d as $item) {
            include "$item";
            $c = "\\".str_replace($dir."\\migrations\\", "", str_replace(".php", "", $item));
            $reflector = new \ReflectionClass($c);
            $instance = $reflector->newInstance();
            $remove = $reflector->getMethod("remove");
            $remove->invoke($instance, []);
            $insert = $reflector->getMethod("insert");
            $insert->invoke($instance, []);
        }
        return $this->push();
    }
}
