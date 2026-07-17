<?php

class DatabaseSingle
{
  private static $connection;

  public static function connect()
  {
    if (!self::$connection) {
      self::$connection = new PDO(
        "mysql:host=127.0.0.1;dbname=reservas_db",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
      );
    }
    return self::$connection;
  }
}
