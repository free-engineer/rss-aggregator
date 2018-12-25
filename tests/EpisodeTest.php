<?php

use PHPUnit\Framework\TestCase;
use Models\AbstractStorage;
use Models\Episode;
use Controllers\Aggregator;

class EpisodeTest extends TestCase
{

    private static $storage;
    private static $test_subscribe_url = "http://cosmo.space-podcast.net/index20.rdf";
    private static $test_guid = "0000 guid 1234";
    private static $test_title = "episode_title1";
    private static $test_enclosure = "enclosure1";
    private static $test_description = ">説明１<script>";
    private static $test_duration = "999分99秒";
    private static $test_permalink = "http://example.jp/issue/abc0000123456.html";
    private static $test_pubdate = "2018-01-01 13:13:13";
    private static $test_datetime_1 = "1970-01-01 00:00:00";
    private static $test_currenttime;

    public static function setUpBeforeClass()
    {
        self::$storage = AbstractStorage::getInstance();
        self::$test_currenttime = Aggregator::getCurrentTime();
    }

    public function setUp()
    {
    }

    private function getNewEpisode()
    {
        return new Episode(
            self::$test_subscribe_url, 
            self::$test_guid, 
            self::$test_title, 
            self::$test_enclosure, 
            self::$test_description,
            self::$test_duration,
            self::$test_pubdate,
            self::$test_permalink,
            self::$test_datetime_1,
            self::$test_currenttime
        );
    }

    // create instance.
    public function testCreateEpisode()
    {
        $episode = $this->getNewEpisode();
        $this->assertEquals(self::$test_subscribe_url, $episode->getSubscribeUrl());
        $this->assertEquals(self::$test_guid, $episode->getGuid());
        $this->assertEquals(self::$test_title, $episode->getTitle());
        $this->assertEquals(self::$test_enclosure, $episode->getEnclosure());
        $this->assertEquals(self::$test_description, $episode->getDescription());
        $this->assertEquals(self::$test_duration, $episode->getDuration());
        $this->assertEquals(self::$test_pubdate, $episode->getPubdate());
        $this->assertEquals(self::$test_permalink, $episode->getPermaLink());
        $this->assertEquals(self::$test_datetime_1, $episode->getCreatedAt());
        $this->assertEquals(self::$test_currenttime, $episode->getUpdatedAt());

    }

    // save to Storage.
    public function testSaveEpisode()
    {
        $episode = $this->getNewEpisode();
        $episode->save();
        // Get data from Storage directly
        $saved_episode = self::$storage->loadEpisode(self::$test_guid);
        $this->assertEquals(self::$test_subscribe_url, $saved_episode->getSubscribeUrl());
        $this->assertEquals(self::$test_guid, $saved_episode->getGuid());
        $this->assertEquals(self::$test_title, $saved_episode->getTitle());
        $this->assertEquals(self::$test_enclosure, $saved_episode->getEnclosure());
        $this->assertEquals(self::$test_description, $saved_episode->getDescription());
        $this->assertEquals(self::$test_duration, $saved_episode->getDuration());
        $this->assertEquals(self::$test_pubdate, $saved_episode->getPubdate());
        $this->assertEquals(self::$test_permalink, $saved_episode->getPermaLink());
        $this->assertEquals(self::$test_currenttime, $saved_episode->getCreatedAt());
        $this->assertEquals(self::$test_currenttime, $saved_episode->getUpdatedAt());
    }

    public function testDeleteEpisode()
    {
        self::$storage->deleteEpisode(self::$test_guid);
        $this->assertFalse(Episode::isExist(self::$test_guid));
    }
}
