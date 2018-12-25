<?php

use PHPUnit\Framework\TestCase;
use Controllers\Aggregator;
use Models\AbstractStorage;
use Models\Channel;
use Models\Episode;

class ChannelTest extends TestCase
{

    private static $storage;
    private static $testtime;

    // Old and stopped Podcast feeds.
    private static $TEST_SUBSCRIBE_URL_1 = "http://cosmo.space-podcast.net/index20.rdf";

    public static function setUpBeforeClass()
    {
        self::$storage = AbstractStorage::getInstance();
        self::$testtime = Aggregator::getCurrentTime();
    }

    public function setUp()
    {
    }

    // Create instance.
    public function testCreateChannel()
    {
        $channel_1 = new Channel(self::$TEST_SUBSCRIBE_URL_1);
        $this->assertEquals($channel_1->getSubscribeUrl(), self::$TEST_SUBSCRIBE_URL_1);
        // title is null. (Title will be filled after call Aggregator->doCrawlAndSave())
        $this->assertNull($channel_1->getTitle());
    }

    // Update Channel instance.
    public function testUpdateChannel()
    {
        $channel_1 = new Channel(self::$TEST_SUBSCRIBE_URL_1);
        $channel_1->updateChannel(
            self::$TEST_SUBSCRIBE_URL_1,
            "link1",
            "title1",
            "desc1",
            "category1", 
            "image_file1", 
            "author=𠮷つちよしだ", 
            "☀ERROR MESSAGE with Emoji☀", 
            self::$testtime,    // pubdate
            self::$testtime     // crawled_at
        );
        $this->assertEquals(self::$TEST_SUBSCRIBE_URL_1, $channel_1->getSubscribeUrl());
        $this->assertEquals("link1", $channel_1->getLink());
        $this->assertEquals("title1", $channel_1->getTitle());
        $this->assertEquals("desc1", $channel_1->getDescription());
        $this->assertEquals("category1", $channel_1->getCategory());
        $this->assertEquals("image_file1", $channel_1->getImage());
        $this->assertEquals("author=𠮷つちよしだ", $channel_1->getAuthor());
        $this->assertEquals("☀ERROR MESSAGE with Emoji☀", $channel_1->getError());
        $this->assertEquals(self::$testtime, $channel_1->getPubdate());
        $this->assertEquals(self::$testtime, $channel_1->getCrawledAt());
    }

    // Save to Storage.
    public function testSaveChannel()
    {
        $channel_1 = new Channel(self::$TEST_SUBSCRIBE_URL_1);
        $channel_1->updateChannel(
            self::$TEST_SUBSCRIBE_URL_1,
            "link1",
            "title1",
            "desc1",
            "category1",
            "image_file1", 
            "author=𠮷つちよしだ", 
            "☀ERROR MESSAGE with Emoji☀", 
            "1970-01-01 00:00:00",  // pubdate
            self::$testtime         // crawled_at
        );
        $channel_1->save();
        // Get data from Storage
        /**
         * @var $saved_channel Channel
         */
        $saved_channel = self::$storage->loadChannel(self::$TEST_SUBSCRIBE_URL_1);
        $this->assertEquals(self::$TEST_SUBSCRIBE_URL_1, $saved_channel->getSubscribeUrl());
        $this->assertEquals("link1", $saved_channel->getLink());
        $this->assertEquals("title1", $saved_channel->getTitle());
        $this->assertEquals("desc1", $saved_channel->getDescription());
        $this->assertEquals("category1", $saved_channel->getCategory());
        $this->assertEquals("image_file1", $saved_channel->getImage());
        $this->assertEquals("author=𠮷つちよしだ", $saved_channel->getAuthor());
        $this->assertEquals("☀ERROR MESSAGE with Emoji☀", $saved_channel->getError());
        $this->assertEquals("1970-01-01 00:00:00", $saved_channel->getPubdate());
        $this->assertEquals(self::$testtime, $saved_channel->getCrawledAt());
    }

    // Exist check.
    public function testIsExistChannel()
    {
        // Fail test.
        $isExist_false = Channel::isExist("not exist link");
        $this->assertFalse($isExist_false);

        // self::$TEST_SUBSCRIBE_URL_1 has been saved.
        $isExist_true = Channel::isExist(self::$TEST_SUBSCRIBE_URL_1);
        $this->assertTrue($isExist_true);
    }

    // Delete channel test.
    public function testDeleteChannel()
    {
        $episode_1 = new Episode(self::$TEST_SUBSCRIBE_URL_1,"guid_1");
        $episode_1->save();
        $episode_2 = new Episode(self::$TEST_SUBSCRIBE_URL_1,"guid_2");
        $episode_2->save();

        self::$storage->deleteChannel(self::$TEST_SUBSCRIBE_URL_1);

        $this->assertFalse(Channel::isExist(self::$TEST_SUBSCRIBE_URL_1));
        $this->assertFalse(Episode::isExist("guid_1"));
        $this->assertFalse(Episode::isExist("guid_2"));
    }


    public static function tearDownAfterClass()
    {
        self::$storage = null;
    }
}
