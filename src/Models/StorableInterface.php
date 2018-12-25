<?php

namespace Models;

interface StorableInterface
{
    public function loadChannel( $key );
    public function loadChannels();
    public function saveChannel( $channel );
    public function deleteChannel( $key );

    public function loadEpisode( $guid );
    public function loadEpisodes( $channel_link );
    public function saveEpisode( $episode );
    public function deleteEpisode( $key );
}
