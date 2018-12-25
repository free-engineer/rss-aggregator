<?php

namespace Models;

use PDO;
use Models\AbstractStorage;

class StorageMySql extends AbstractStoragePdo
{
    // Instantiate the connector to Storage.
    public function connect()
    {
        $dsn = "mysql:host=".MYSQL_HOST.";dbname=".MYSQL_NAME.";port=".MYSQL_PORT.";charset=".MYSQL_CHARSET.";";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        try {
            $this->setPdo(new PDO($dsn, MYSQL_USER, MYSQL_PASSWORD, $options));
        } catch( PDOException $e ) {
            error_log("DB Connection Error:" . $e->getMessage());
            return false;
        }
        return true;
    }
}
