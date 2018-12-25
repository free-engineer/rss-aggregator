<?php

namespace Controllers;

use Models\Channel;
use Models\Episode;
use Models\AbstractStorage;
use Exception;
use DateTime;
use SimplePie;

class Aggregator
{

    private $storage;
    private $start_time;
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct()
    {
        $this->storage = AbstractStorage::getInstance();
        // Create SimplePie's cache 
        //
        //if(!file_exists(CACHE_DIR)) {
        //    mkdir(CACHE_DIR, 0777, true);
        //}
    }

    public static function getCurrentTime()
    {
        return date(self::DATETIME_FORMAT);
    }

    public function getStartTime()
    {
        return $this->start_time;
    }
    public function setStartTime( $date_string )
    {
        $this->start_time = $date_string;
    }

    /**
     * return list of ( a Channel and some Episodes )
     *
     * @return array
     */
    public function getChannelsAndEpisodes() : array
    {
        $result_array = array();
        try {
            $channels = $this->storage->loadChannels();
            foreach($channels as $ch){
                $channels_and_episodes = array();
                $channels_and_episodes[] = $ch;
                $episodes = $this->storage->loadEpisodes($ch->getSubscribeUrl());
                $channels_and_episodes[] = $episodes;
                $result_array[] = $channels_and_episodes;
            }
            return $result_array;
        } catch (Exception $e) {

            error_log("ERROR on CRAWLING:" . $e->getMessage());
            error_log("ERROR TRACE      :" . $e->getTraceAsString());
        }
    }

    public function refresh( $isVerbose = false )
    {
        if($isVerbose) {
            echo "<p>Crawl start. (current MAX_CHANNEL_COUNT_PER_1_CRAWL settings in Config.php : " . MAX_CHANNEL_COUNT_PER_1_CRAWL . ")</p><hr />";
        }
        $this->setStartTime(self::getCurrentTime());

        // 1. Load Channel->link from DB.
        $channels = $this->storage->loadChannelsForCrawling();
        // 2. Crawling 1's URL.
        // 3. Overwrite channels and episodes.
        $this->doCrawlAndSave($channels, $isVerbose);
        if($isVerbose) {
            echo "<hr /><p>Crawl finished.</p>";
        }
    }

    public function refreshChannel( $channel )
    {
        $this->doCrawlAndSave(array($channel));
    }

    private function doCrawlAndSave( array $channels , $isVerbose = false )
    {
        foreach($channels as $ch){
            try {
                $pie = new SimplePie();
                if(defined('CURL_OPTIONS')) {
                    $pie->set_curl_options(CURL_OPTIONS);
                }
                // $pie->set_cache_location(CACHE_DIR);        
                $pie->enable_cache(false);
                $pie->set_feed_url($ch->getSubscribeUrl());
                if($isVerbose) {
                    $erapsed_time = strtotime(self::getCurrentTime()) - strtotime($this->getStartTime());
                    echo "<p>{$erapsed_time} sec elapsed. : {$ch->getSubscribeUrl()} | {$ch->getTitle()}</p>";
                    flush();
                }
                $pie->init();
                $pie->handle_content_type();
                $ch->updateChannel(
                    $pie->subscribe_url(),
                    $pie->get_link(),
                    $pie->get_title(),
                    $pie->get_description(),
                    $pie->get_category(),
                    $pie->get_image_url(),
                    $pie->get_author(),
                    '', //$pie->error(),
                    '', // In acctually, Episode's latest pubdate will be used as Channel's pubdate.
                    self::getCurrentTime() // crawled_at
                );
                $ch->save();

                // Create ( or update ) episodes.
                $items = $pie->get_items(0, MAX_SHOW_EPISODES);
                // Delete episodes on DB every channels.
                $this->storage->deleteEpisodesInChannel($ch->getSubscribeUrl());
                foreach($items as $item){
                    $episode = new Episode(
                        $ch->getSubscribeUrl(),
                        $item->get_id(),
                        $item->get_title(),
                        $item->get_enclosure(),
                        $item->get_description(),
                        $item->get_enclosure()->get_duration(true), // true option converts n[sec] to hh:mm:ss format.
                        $item->get_date(self::DATETIME_FORMAT),
                        $item->get_permalink(),
                        self::getCurrentTime(),
                        self::getCurrentTime()
                    );
                    $episode->save();
                }

                // Long crawling will be killed by hosting server's restriction.
                // So, check the time-limit of proccess.
                if (MAX_CRAWLING_TIME <= ( strtotime(self::getCurrentTime()) - strtotime($this->getStartTime()) )) {
                    if ($isVerbose) {
                        echo "Time up. ( current MAX_CRAWLING_TIME settings in Config.php :" . MAX_CRAWLING_TIME . " sec.";
                    }
                    return;
                }
            } catch (Exception $e) {
                error_log("ERROR on CRAWLING:" . $e->getMessage());
                error_log("ERROR TRACE      :" . $e->getTraceAsString());
            } finally {
                $pie->__destruct();
                unset($items);
                unset($pie);
            }
        }
    }
}
