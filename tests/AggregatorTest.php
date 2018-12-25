<?php

use PHPUnit\Framework\TestCase;
use Controllers\Aggregator;
use Models\AbstractStorage;
use Models\Channel;
use Models\Episode;

class AggregatorTest extends TestCase
{

    private static $storage;
    private static $agg;
    // Old and stopped Podcast feed.
    private static $feed_url = "https://www.tfm.co.jp/podcasts/avanti/podcast.xml";
    private static $feed_link = "http://www.avanti-web.com/";
    private static $feed_title = "SUNTORY SATURDAY WAITING BAR AVANTI";
    private static $feed_description = "「東京一の日常会話」に聞き耳を立て続けて、この春で20年目。 これまでAVANTIに登場したお客様はのべ5000人以上。 その中から常連のお客様達のとっておきのトークを毎週選りすぐってお届けします。";
    // <itunes:category> tag has not been tested.
    //private static $feed_category = "Society & Culture";
    private static $feed_image = "http://www.tfm.co.jp/podcasts/upload/channel/cd333fe27d56d1c00b007992ccdf8e7d_6_300x300.jpg";
    private static $feed_author = "TOKYO FM";
    private static $feed_pubdate = "2013-03-30 17:55:00";
    // guid of last episode
    private static $item_subscribe_url = "https://www.tfm.co.jp/podcasts/avanti/podcast.xml";
    private static $item_guid = "http://podcasts.tfm.co.jp/podcasts/tokyo/avanti/avanti_vol365.mp3";
    private static $item_title = "SUNTORY SATURDAY WAITING BAR AVANTI PODCAST vol.365";
    private static $item_enclosure = "http://podcasts.tfm.co.jp/podcasts/tokyo/avanti/avanti_vol365.mp3";
    private static $item_description = "「東京一の日常会話」に聞き耳を立て続けて、この春で20年目。 これまでAVANTIに登場したお客様はのべ5000人以上。 その中から常連のお客様達のとっておきのトークを毎週選りすぐってお届けします。";
    private static $item_duration = "00:08:10";
    private static $item_pubdate = "2013-03-30 17:55:00";
    private static $item_permalink = "http://www.tfm.co.jp/podcasts/avanti/detail.php?id=1652";

    public static function setUpBeforeClass()
    {
        self::$agg = new Aggregator();
        self::$storage = AbstractStorage::getInstance();
    }
    
    public function setUp()
    {
    }

    // Test create instance.
    public function testCreateAggregator()
    {
        $this->assertEquals("Controllers\Aggregator", get_class(self::$agg));
    }

    // Test Crawling.
    public function testRefresh()
    {
        $channel = new Channel(self::$feed_url);
        self::$agg->refreshChannel($channel);
        $saved_channel = self::$storage->loadChannel(self::$feed_url);
        $this->assertEquals(self::$feed_url, $saved_channel->getSubscribeUrl());
        $this->assertEquals(self::$feed_title, $saved_channel->getTitle());
        $this->assertEquals(self::$feed_link, $saved_channel->getLink());
        $this->assertEquals(self::$feed_title, $saved_channel->getTitle());
        $this->assertEquals(self::$feed_description, $saved_channel->getDescription());
        //$this->assertEquals(self::$feed_category, $saved_channel->getCategory());
        $this->assertEquals(self::$feed_image, $saved_channel->getImage());
        $this->assertEquals(self::$feed_author, $saved_channel->getAuthor());
        $this->assertEquals(self::$feed_pubdate, $saved_channel->getPubdate());
    }

    public static function tearDownAfterClass()
    {
        self::$storage->deleteChannel(self::$feed_url);
    }
}
