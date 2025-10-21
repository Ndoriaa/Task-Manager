<?php

class User
{
    public $id = null;

    public $firstName = null;

    public $secondName = null;

    public $passwordHash = null;

    public $department = null;

    public $is_admin = 0;

    public function __construct($data=array()){
        if (isset($data['id']))$this->id = (int)$data['id'];
        if (isset($data['firstName']))$this->firstName = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "",$data['firstName']);
        if (isset($data['secondName']))$this->secondName = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "",$data['secondName']);
        if (isset($data['password'])) $this->passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        if (isset($data['passwordHash'])) $this->passwordHash = $data['passwordHash'];
        if (isset($data['department']))$this->department = $data['department'];
        if (isset($data['is_admin'])) $this->is_admin = (int)$data['is_admin'];
    }
    
    public function storeFormValues ( $params ) {
        $this->__construct( $params );
        $this->firstName = $params['firstName'] ?? '';
        $this->secondName = $params['secondName'] ?? '';
        $this->passwordHash = $params['passwordHash'] ?? '';
        $this->department = $params['department'] ?? '';
        $this->is_admin = isset($params['is_admin']) ? 1 : 0;
    }   
    
    public static function getById( $id ){
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT * FROM users WHERE id =:id";
        $st = $conn->prepare($sql);
        $st->bindVAlue(":id",$id, PDO :: PARAM_INT );
        $st->execute();
        $row = $st->fetch();
        $conn = null;
        if ($row) return new User( $row);

    }

    public function insert() {
        if (!is_null($this->id)) trigger_error("User::insert(): Attempt to insert a User that already has an ID.", E_USER_ERROR);
    
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO users (firstName, secondName, passwordHash, department, is_admin) VALUES (:firstName, :secondName, :passwordHash, :department, :is_admin)";
        $st = $conn->prepare($sql);
        $st->bindValue(":firstName", $this->firstName, PDO::PARAM_STR);
        $st->bindValue(":secondName", $this->secondName, PDO::PARAM_STR);
        $st->bindValue(":passwordHash", $this->passwordHash, PDO::PARAM_STR);
        $st->bindValue(":department", $this->department, PDO::PARAM_STR);
        $st->bindValue(":is_admin", $this->is_admin, PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
      }

      public function update() {
        if (is_null($this->id)) trigger_error("User::update(): User ID is not set.", E_USER_ERROR);
    
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "UPDATE users SET firstName = :firstName, secondName = :secondName, passwordHash = :passwordHash, department = :department, is_admin = :is_admin WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":firstName", $this->firstName, PDO::PARAM_STR);
        $st->bindValue(":secondName", $this->secondName, PDO::PARAM_STR);
        $st->bindValue(":passwordHash", $this->passwordHash, PDO::PARAM_STR);
        $st->bindValue(":department", $this->department, PDO::PARAM_STR);
        $st->bindValue(":is_admin", $this->is_admin, PDO::PARAM_INT);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
      }

      public function delete() {
        if (is_null($this->id)) trigger_error("User::delete(): User ID is not set.", E_USER_ERROR);
    
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $st = $conn->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
      }

      public static function authenticate($firstName, $password) {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT * FROM users WHERE firstName = :firstName";
        $st = $conn->prepare($sql);
        $st->bindValue(":firstName", $firstName, PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch();
        $conn = null;
      
        if ($row && password_verify($password, $row['passwordHash'])) {
          return new User($row);
        }
      
        return false;
      }
      
}

?>