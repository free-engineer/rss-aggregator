<?php

// Set timezone.
date_default_timezone_set('Asia/Tokyo');

// Use WordPress table for storage and behave as a Plugin.
// define('STORAGE_TYPE', 'WordPress');
// -------------------------------------------------------
// Use pure MySql DB.
//define('STORAGE_TYPE', 'MySql');
// -------------------------------------------------------
// Use sqlite3.
define('STORAGE_TYPE', 'Sqlite');

define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_PORT', '3306');
define('MYSQL_NAME', 'rssdb');
define('MYSQL_CHARSET','utf8mb4');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', 'password');
define('SQLITE_FILE_PATH',__DIR__ . "\\..\\db\\aggdb.sqlite3");
define('CACHE_DIR', __DIR__ . '/rss_cache/');
define('CRAWLING_INTERVAL', 3600); // sec.
define('MAX_CRAWLING_TIME', 15);     // sec.
define('MAX_SHOW_CHANNELS', 100);
define('MAX_SHOW_EPISODES', 3);
define('MAX_CHANNEL_COUNT_PER_1_CRAWL', 3);
// for behind a proxy server
/*
define('CURL_OPTIONS', array(
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false)
);
*/