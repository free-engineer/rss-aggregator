<?php

namespace Models;

class Episode
{
    private $subscribe_url;  // Key of channels table.
    private $guid;          // guid is become unique key with single field.
    private $title;
    private $enclosure;
    private $description;
    private $duration;
    private $pubdate;
    private $permalink;
    private $crawled_at;
    private $created_at;
    private $updated_at;

    public function __construct(
        $subscribe_url = '',
        $guid = '',
        $title = '',
        $enclosure = '',
        $description = '',
        $duration = '',
        $pubdate = '',
        $permalink = '',
        $created_at = '',
        $updated_at = ''
    ) {
        $this->subscribe_url = $subscribe_url;
        $this->guid = $guid;
        $this->title = $title;
        $this->enclosure = $enclosure;
        $this->description = $description;
        $this->duration = $duration;
        $this->pubdate = $pubdate;
        $this->permalink = $permalink;
        $this->created_at = $created_at;                
        $this->updated_at = $updated_at;
    }

    // Accessor methods.
    public function getSubscribeUrl()
    {
        return $this->subscribe_url;    
    }
    public function getGuid()
    {
        return $this->guid;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getEnclosure()
    {
        return $this->enclosure;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getDuration()
    {
        return $this->duration;
    }
    public function getPubdate()
    {
        return $this->pubdate;
    }
    public function getPermaLink()
    {
        return $this->permalink;
    }
    public function getCreatedAt()
    {   
        return $this->created_at;
    }
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    // Convert instance properties to array.
    public function getArray()
    {
        return array(
            'subscribe_url'=>$this->getSubscribeUrl(),
            'guid'=>$this->getGuid(),
            'title'=>$this->getTitle(),
            'enclosure'=>$this->getEnclosure(),
            'description'=>$this->getDescription(),
            'duration'=>$this->getDuration(),
            'pubdate'=>$this->getPubdate(),
            'permalink'=>$this->getPermaLink(),
            'created_at'=>$this->getCreatedAt(),
            'updated_at'=>$this->getUpdatedAt()
        );
    }

    public function save()
    {
        $storage = AbstractStorage::getInstance();
        $storage->saveEpisode($this);    
    }

    // Check episode guid has been stored in DB.
    static public function isExist( $guid )
    {
        $storage = AbstractStorage::getInstance();
        return $storage->isExistEpisode($guid);
    }

    public function delete1episode( striing $guid )
    {
    }
}