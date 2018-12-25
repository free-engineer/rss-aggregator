<?php

namespace Models;

use Controllers\Aggregator;
use Exception;
use PDO;

abstract class AbstractStoragePdo extends AbstractStorage
{
    private $pdo;

    abstract public function connect();

    protected function getPdo()
    {
        return $this->pdo;
    }

    protected function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    protected function get1row(string $str, array $params)
    {
        $stmt = $this->getPdo()->prepare($str);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        if (1 < count($result)) {
            // If results has multiple rows, throw Exception.
            throw new Exception('MulutipleRowsException. (Expected only 1 row here.)');
        }

        // return array ( Keys are changed to UPPER CASE )
        return array_change_key_case($result[0], CASE_UPPER);
    }

    protected function execute(string $sql, array $params): int
    {
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("PDO Execute Error:" . $e->getMessage());
            error_log($e->getTraceAsString());
        }
    }

    // ================
    // Static methods.
    // ================
    protected static function isExist($sql, $param): bool
    {
        $storage = AbstractStorage::getInstance();
        // Judge by first column
        $record_count = (int) $storage->get1row($sql, $param)[0];
        if (is_int($record_count)) {
            return $record_count === 0 ? false : true;
        } else {
            throw new Exception('IsExistReturnValueException. (SQL result value is not Int)');
        }
    }

    public static function isExistChannel($subscribe_url): bool
    {
        $sql = 'SELECT COUNT(SUBSCRIBE_URL) FROM CHANNELS WHERE SUBSCRIBE_URL=:subscribe_url';
        return self::isExist($sql, array($subscribe_url));
    }

    public static function isExistEpisode($guid): bool
    {
        $sql = 'SELECT COUNT(GUID) FROM EPISODES WHERE GUID=:guid';
        return self::isExist($sql, array($guid));
    }

    // ================
    // Channel methods.
    // ================
    public function loadChannel($subscribe_url)
    {
        $stmt = $this->getPdo()->prepare('SELECT * FROM CHANNELS WHERE SUBSCRIBE_URL = :subscribe_url');
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Models\Channel');
        $stmt->execute(array('subscribe_url' => $subscribe_url));

        $result = $stmt->fetchAll();
        return empty($result[0]) ? null : $result[0];
    }

    public function loadChannels(): array
    {
        $sql = "SELECT"
            . " CH.SUBSCRIBE_URL, CH.LINK, CH.TITLE, CH.DESCRIPTION, CH.CATEGORY, CH.IMAGE, CH.AUTHOR, CH.ERROR, EP.PUBDATE as pubdate, CH.CRAWLED_AT, CH.CREATED_AT, CH.UPDATED_AT "
            . " FROM ( "
            . " SELECT SUBSCRIBE_URL, MAX(PUBDATE) AS PUBDATE FROM EPISODES WHERE PUBDATE <= '{Aggregator::getCurrentTime()}' "
            . " GROUP BY SUBSCRIBE_URL ORDER BY PUBDATE DESC "
            . " ) EP LEFT OUTER JOIN (SELECT * FROM CHANNELS) AS CH "
            . " ON EP.SUBSCRIBE_URL = CH.SUBSCRIBE_URL LIMIT :max_channels";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Models\Channel');
        $stmt->execute(array('max_channels' => MAX_SHOW_CHANNELS));

        $result = $stmt->fetchAll();
        return $result;
    }

    public function loadChannelsForCrawling(): array
    {
        // Crawling many channels at a time causes Maximum execution time error.
        $stmt = $this->getPdo()->prepare(
            'SELECT * FROM CHANNELS WHERE ( CRAWLED_AT IS NULL OR CRAWLED_AT < :yesterday ) ORDER BY CRAWLED_AT LIMIT :channel_max'
        );
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Models\Channel');
        $stmt->execute(
            array(
                'yesterday' => date(Aggregator::DATETIME_FORMAT, strtotime('-1 day')),
                'channel_max' => MAX_CHANNEL_COUNT_PER_1_CRAWL,
            )
        );
        $result = $stmt->fetchAll();
        return $result;
    }

    public function saveChannel($channel)
    {
        $current_datetime = Aggregator::getCurrentTime();
        try {
            $sql = null;
            $params = $channel->getArray();
            $isExist = (self::isExistChannel($channel->getSubscribeUrl()));
            if ($isExist) {
                $sql = "UPDATE CHANNELS SET "
                . "LINK=:link,"
                . "TITLE=:title,"
                . "DESCRIPTION=:description,"
                . "CATEGORY=:category,"
                . "IMAGE=:image,"
                . "AUTHOR=:author,"
                . "ERROR=:error,"
                . "PUBDATE=:pubdate,"
                . "CRAWLED_AT=:crawled_at,"
                //. "CREATED_AT=:created_at,"
                 . "UPDATED_AT=:updated_at"
                    . "  WHERE "
                    . "SUBSCRIBE_URL=:subscribe_url";
                // datetime field overwrite by current time.
                unset($params['created_at']);
                $params['updated_at'] = $current_datetime;
            } else {
                $sql = "INSERT INTO CHANNELS VALUES ( "
                    . ":subscribe_url,"
                    . ":link,"
                    . ":title,"
                    . ":description,"
                    . ":category,"
                    . ":image,"
                    . ":author,"
                    . ":error,"
                    . ":pubdate,"
                    . ":crawled_at,"
                    . ":created_at,"
                    . ":updated_at )";
                // datetime fields overwrite by current time.
                $params['updated_at'] = $current_datetime;
                $params['created_at'] = $current_datetime;
            }
            $this->execute($sql, $params);
        } catch (Exception $e) {
            error_log("ERROR MESSAGE: " . $e->getMessage());
            error_log($e->getTraceAsString());
        }
    }

    public function deleteChannel($subscribe_url)
    {
        try {
            $this->getPdo()->beginTransaction();
            try {
                // Delete channel record.
                $sql = 'DELETE FROM CHANNELS WHERE SUBSCRIBE_URL = :subscribe_url';
                $params = array($subscribe_url);
                $delete_count = $this->execute($sql, $params);
                if ($delete_count === 0) {
                    throw new Exception('NoRecordsDeletedException. (Deleted row count was 0.)');
                }
                // Delete episode records belonged to deleted channel.
                $sql = 'DELETE FROM EPISODES WHERE SUBSCRIBE_URL = :subscribe_url';
                $delete_count = $this->execute($sql, $params);
                // TODO logging deteled count of channels and episodes.
                $this->getPdo()->commit();
            } catch (Exception $e) {
                $this->getPdo()->rollback();
                throw new Exception('Rollbacked delete episode command.');
            }
        } catch (Exception $e) {
            error_log("ERROR MESSAGE: " . $e->getMessage());
            error_log($e->getTraceAsString());
        }
    }

    // ===============
    // Episode methods
    // ===============
    public function loadEpisode($guid): Episode
    {
        $stmt = $this->getPdo()->prepare('SELECT * FROM EPISODES WHERE GUID = :guid');
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Models\Episode');
        $stmt->execute(array('guid' => $guid));

        $result = $stmt->fetchAll();
        return $result[0];
    }

    public function loadEpisodes($subscribe_url): array
    {
        $stmt = $this->getPdo()->prepare('SELECT * FROM EPISODES WHERE SUBSCRIBE_URL = :subscribe_url LIMIT :max_shows_episodes');
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Models\Episode');
        $stmt->execute(array($subscribe_url, MAX_SHOW_EPISODES));

        $result = $stmt->fetchAll();
        return $result;
    }

    public function saveEpisode($episode)
    {
        $current_datetime = Aggregator::getCurrentTime();
        try {
            $sql = null;
            $params = $episode->getArray();
            $isExist = (self::isExistEpisode($episode->getGuid()));
            if ($isExist) {
                $sql = "UPDATE EPISODES SET "
                . "SUBSCRIBE_URL=:subscribe_url,"
                . "TITLE=:title,"
                . "ENCLOSURE=:enclosure,"
                . "DESCRIPTION=:description,"
                . "DURATION=:duration,"
                . "PUBDATE=:pubdate,"
                . "PERMALINK=:permalink,"
                //. "CREATED_AT=:created_at,"
                 . "UPDATED_AT=:updated_at"
                    . "  WHERE "
                    . "GUID=:guid";
                // datetime field overwrite by current time.
                unset($params['created_at']);
                $params['updated_at'] = $current_datetime;
            } else {
                $sql = "INSERT INTO EPISODES VALUES ( "
                    . ":subscribe_url,"
                    . ":guid,"
                    . ":title,"
                    . ":enclosure,"
                    . ":description,"
                    . ":duration,"
                    . ":pubdate,"
                    . ":permalink,"
                    . ":created_at,"
                    . ":updated_at )";
                // datetime fields overwrite by current time.
                $params['updated_at'] = $current_datetime;
                $params['created_at'] = $current_datetime;
            }
            $this->execute($sql, $params);
        } catch (Exception $e) {
            error_log("ERROR MESSAGE: " . $e->getMessage());
            error_log($e->getTraceAsString());
        }
    }

    protected function deleteEpisodesInStorage($sql, array $params)
    {
        $this->execute($sql, $params);
    }

    public function deleteEpisodesInChannel($subscribe_url)
    {
        $sql = "DELETE FROM EPISODES WHERE SUBSCRIBE_URL = :subscribe_url";
        $this->deleteEpisodesInStorage($sql, array('subscribe_url' => $subscribe_url));
    }

    public function deleteEpisode($guid)
    {
        $sql = "DELETE FROM EPISODES WHERE GUID = :guid";
        $this->deleteEpisodesInStorage($sql, array('guid' => $guid));
    }
}
