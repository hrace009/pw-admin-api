<?php

  abstract class ClassDB implements InterfaceConfig{

    static $host = DB_HOST;
    static $username = DB_USER;
    static $type = 'mysql';
    static $port = '3306';
    static $password = DB_PASSWORD;
    static $namedb = DB_NAME;
    static $con;
    static $persist = true;
    private $protected = true;

    function __construct(){
      self::$host = DB_HOST;
      self::$username = DB_USER;
      self::$password = DB_PASSWORD;
      self::$namedb = DB_NAME;
    }

    public function get_protected(){
      return $this->protected;
    }

    public static function getConn(){
      if (!self::$con) {
        try {

          $db_host = self::$host;
          $db_type = self::$type;
          $db_port = self::$port;
          $db_database = self::$namedb;
          $db_username = self::$username;
          $db_password = self::$password;

          $dsn = $db_type . ':';
          $dsn .= 'dbname=' . $db_database . '';
          $dsn .= $db_host ? ';host=' . $db_host : '';
          $dsn .= $db_port ? ';port=' . $db_port : '';

          self::$con = new PDO($dsn, $db_username, $db_password);
          self::$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          self::$con->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
          self::$con->exec('SET NAMES UTF8');
          if (self::$persist) {
            self::$con->setAttribute(PDO::ATTR_PERSISTENT, true);
          }
        } catch (PDOException $oException) {
          echo 'Erro de coneccao';
          echo '<pre>';
          var_dump($dsn);
          var_dump($oException);
          //  die();
        }
      }
      return self::$con;
    }

    public static function select($sql, $params = null){

      $stm = null;
      if (self::getConn()) {
        $con = self::getConn();
        try {
          if ($params == null) {
            $stm = $con->query($sql);
            $con = false;
          } else {
            $stm = $con->prepare($sql);
            $stm->execute($params);
            $con = false;
          }
          $stm->setFetchMode(PDO::FETCH_ASSOC);
          return $stm->fetchAll();
        } catch (PDOException $oException) {
          $con = false;
          echo '<pre>';
          var_dump($oException);
          '</pre>';
          // throw new Exception($oException->getMessage(), 1);
        }
        return $stm;

      }
    }

    public static function insert($sql, $params = null){
      self::getConn();
      $stmt = self::$con->prepare($sql);
      try {
        $stmt->execute($params);
        return self::$con->lastInsertId();
      } catch (\Exception $e) {
        echo '<pre>'.$e.'</pre>';
      }
    }

    public static function delete($sql, $params = null){
      self::getConn();
      $stmt = self::$con->prepare($sql);
      try {
        $stmt->execute($params);
        return $stmt->rowCount();
      } catch (\Exception $e) {
        echo '<pre>'.$e.'</pre>';
      }
    }

    public static function lastInsertId() {
      return self::$con->lastInsertId();
    }


    // public function __destruct(){
    //   $this->con->close();
    // }
  }

 ?>
