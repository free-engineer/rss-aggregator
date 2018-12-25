<?php

use PHPUnit\Framework\TestCase;
use Models\AbstractStorage;

class StorageSqliteTest extends TestCase
{
    const STORAGE_CLASSNAME = "Models\StorageSqlite";

    public function SetUp()
    {
    }

    // Test create instance.
    public function testCreateConnector()
    {
        $storage = AbstractStorage::getInstance(self::STORAGE_CLASSNAME);
        $this->assertEquals(get_class($storage), self::STORAGE_CLASSNAME);
    }

    // Storage class can't instantiate by new command.
    public function testSingletonInstantiation()
    {
        $this->expectExceptionMessage("Cannot instantiate");
        $db3 = new AbstractStorage();
    }

    // Test uniqueness of instance.
    public function testSingletonUniqueness()
    {
        $db1 = AbstractStorage::getInstance(self::STORAGE_CLASSNAME);
        $db2 = AbstractStorage::getInstance(self::STORAGE_CLASSNAME);
        $this->assertTrue($db1 === $db2);
    }

    // Test connect to db.
    public function testConnectDB()
    {
        $storage = AbstractStorage::getInstance(self::STORAGE_CLASSNAME);
        $this->assertTrue($storage->connect());
    }
}
