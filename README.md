# RSS Aggregator
====

## Overview

Aggregate your favorite rss feeds and shows Channels/Episodes on your web site.

## Description

- Add your favorite rss feed urls in DB.
- You can see your these feed channels and recent episodes on your hosting server.
- This application can be used as a simple curation page.

## Demo

[Japanese podcasts for engineers.](http://bit.ly/rss-agg)

## Requirement

- PHP 7
- This application is using simplepie.

## Install

1. git clone on your hosting server.
1. composer install
1. Add feed urls in DB. ( No admin page, so you have to insert into records with db management utility like DB Browser for SQLite. )
1. Kick crawling
1. Browse your url /src/Views/index.php

## Licence

[GPL v3.0](https://github.com/free-engineer/rss-aggregator/blob/master/LICENSE)

## Author

[free-engineer S](https://github.com/free-engineer)
