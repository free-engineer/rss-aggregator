<?php

namespace Models;

use Exception;

Abstract class AbstractStorage implements StorableInterface
{
    private static $instance = [];

    // Singleton
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance($concreteStorageClass = "Models\Storage".STORAGE_TYPE)
    {
        // Default STORAGE_TYPE is selected in Config.php
        try {
            if (!isset(self::$instance[$concreteStorageClass])) {
                self::$instance[$concreteStorageClass] = new $concreteStorageClass;
                self::$instance[$concreteStorageClass]->connect();
            }
            return self::$instance[$concreteStorageClass];
        } catch (Exception $e) {
            error_log("{$concreteStorageClass} instantiation error at getInstance() method.");
            error_log($e->getTraceAsString());
        }
    }
}
