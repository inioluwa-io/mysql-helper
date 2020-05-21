<?php

include_once "errorlogging.php";

interface IDatabase{
    public function find();
    public function insert($data);
    public function delete($data);
    public function update($where, $set);
}

class Database implements IDatabase{
    protected $conn;
    private $tableName;
    private $query;
    function __construct($HOST ="127.0.0.1", $DB_NAME= "petrochina", $USER = "root", $PASSWORD = ""){
        $this->connect($HOST, $DB_NAME, $USER, $PASSWORD);
    }
    
    // use table from database
    public function use($tableName){
        $this->tableName = $tableName;
        return $this;
    }

    // Connect to database
    private function connect($HOST, $DB_NAME, $USER, $PASSWORD){
        
        $DB = ("mysql:host=$HOST;dbname=$DB_NAME");
        try{
            $conn = new PDO($DB, $USER, $PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // error_reporting(0);
            $this->conn = $conn;
            return $conn;
        }
        catch(\PDOException $e){
            ErrorLogging::logError('/logs/error_log.log', date("l jS \of F Y h:i:s A").": ".$e->getMessage().'; ');
            die('could not connect: '. $e->getMessage());
        }
    }

    // check if where criteria is needed then compute needed sql string and add to query variable
    public function find($data = false){
        $query = "SELECT * FROM ".$this->tableName;
        $params = array();
        if($data){
            $query .= " WHERE";
            $count = 0;
            $length = sizeof($data);
            foreach($data as $key => $value){
                if($count < $length -1 && $length > 1) {
                    $query .=" $key = :$key and";
                } else {
                    $query .=" $key = :$key";
                }
                // prepare parameter for PDO execute function
                $params[":$key"] = $value;
                $count++;
            }
        }
        $sth = $this->conn->prepare($query, [PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY]);
        try{
            $data ? $sth->execute($params) : $sth->execute();
            return $sth->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e){
            ErrorLogging::logError('/logs/error_log.log', date("l jS \of F Y h:i:s A").": ".$e->getMessage().'; ');
            die($e->getMessage());
        }
    }

    public function insert($data){
        $query = "INSERT INTO `$this->tableName` VALUES (NULL";
        $params = array();
        $count = 1;
        foreach($data as $value){
            $query .=", :column_$count";
            $params[":column_$count"] = $value;
            $count++;
        }
        $query.=")";
        // $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$this->tableName'";
        try{
            $sth = $this->conn->prepare($query, [PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY]);
            $sth->execute($params);
            return true;
        } catch(\PDOException $e) {
            ErrorLogging::logError('/logs/error_log.log', date("l jS \of F Y h:i:s A").": ".$e->getMessage().'; ');
            die($e->getMessage());
        }
    }

    public function delete($data) {
        $query = "DELETE FROM `$this->tableName` WHERE";
        $count = 0;
        $length = sizeof($data);
        $params = array();
        foreach($data as $key => $value){
            if($count < $length -1 && $length > 1) {
                $query .=" `$key` = :$key and";
            } else {
                $query .=" `$key` = :$key";
            }
            $params[":$key"] = $value;
            $count++;
        }
        try{
            echo $query."<br/>";
            $sth = $this->conn->prepare($query, [PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY]);
            $sth->execute($params);
            return true;
        } catch (\PDOException $e) {
            ErrorLogging::logError('/logs/error_log.log', date("l jS \of F Y h:i:s A").": ".$e->getMessage().'; ');
            die($e->getMessage());
        }
    }

    public function update($where, $set){
        $query = "UPDATE `$this->tableName` SET";
        $count = 1;
        $params = array();
        $length = sizeof($set);
        foreach($set as $key => $value){
            $query .=" `$key` = :SET_COLUMN_$count";
            $count < $length && $query.=" ,";
            $params[":SET_COLUMN_$count"] = $value;
            $count++;
        }
        $query .=" WHERE ";
        $count = 1;
        $length = sizeof($where);
        foreach($where as $key => $value){
            $query .=" `$key` = :WHERE_COLUMN_$count"; 
            $count < $length && $query.=" and";
            $params[":WHERE_COLUMN_$count"] = $value; 
            $count++;
        }
        
        try{
            $sth = $this->conn->prepare($query, [PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY]);
            $sth->execute($params);
            return true;
        } catch (\PDOException $e) {
            ErrorLogging::logError('/logs/error_log.log', date("l jS \of F Y h:i:s A").": ".$e->getMessage().'; ');
            die($e->getMessage());
        }
    }
}
?>