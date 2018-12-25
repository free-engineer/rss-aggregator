<?php

namespace Models;

use PDO;
use Models\AbstractStoragePdo;

class StorageSqlite extends AbstractStoragePdo
{
    // Instantiate the connector to Storage.
    public function connect()
    {
        $dbfile = SQLITE_FILE_PATH;
        $dsn = "sqlite:{$dbfile}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        try {
            $this->setPdo(new PDO($dsn, "", "", $options));
        } catch( PDOException $e ) {
            error_log("DB Connection Error:" . $e->getMessage());
        }
    }
}
