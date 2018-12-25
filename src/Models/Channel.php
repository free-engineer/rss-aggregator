<?php

namespace Models;

use Models\AbstractStorage;
use Models\Episode;

class Channel
{
    private $subscribe_url;  // primary key
    private $link;
    private $title;
    private $description;
    private $category;
    private $image;
    private $author;
    private $error;
    private $pubdate;   // pubdate of latest episode ( exclude future date ).
    private $crawled_at;
    private $updated_at;
    private $created_at;

    public function __construct(
        $subscribe_url = '',     // This parameter should be Required. but when this option set to required, PDO query will be failed.
        $link = '',
        $title = '',
        $description = '',
        $category = '',
        $image = '',
        $author = '',
        $error = '',
        $pubdate = '',
        $crawled_at = '',
        $updated_at = '',
        $created_at = ''
    ) {
        $this->subscribe_url = $subscribe_url;
    }

    // Accessor methods.
    public function getSubscribeUrl()
    {
        return $this->subscribe_url;    
    }
    public function getLink()
    {
        return $this->link;    
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getCategory()
    {
        return $this->category;
    }
    public function getImage()
    {
        return $this->image;
    }
    public function getAuthor()
    {
        return $this->author;
    }
    public function getError()
    {
        return $this->error;
    }
    public function getPubdate()
    {
        return $this->pubdate;
    }
    public function getCrawledAt()
    {
        return $this->crawled_at;
    }
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // Convert instance properties to array.
    public function getArray()
    {
        return array(
            'subscribe_url'=>$this->getSubscribeUrl(),
            'link'=>$this->getLink(),
            'title'=>$this->getTitle(),
            'description'=>$this->getDescription(),
            'category'=>$this->getCategory(),
            'image'=>$this->getImage(),
            'author'=>$this->getAuthor(),
            'error'=>$this->getError(),
            'pubdate'=>$this->getPubdate(),
            'crawled_at'=>$this->getCrawledAt(),
            'created_at'=>$this->getCreatedAt(),
            'updated_at'=>$this->getUpdatedAt()
        );
    }

    // Check channel subscribe_url has been stored in DB.
    static public function isExist( $subscribe_url )
    {
        $storage = AbstractStorage::getInstance();
        return $storage->isExistChannel($subscribe_url);
    }

    public function updateChannel( $subscribe_url, $link, $title, $desc, $cat, $img, $author, $error,$pubdate, $crawled_at )
    {
        $this->subscribe_url = $subscribe_url;
        $this->link = $link;
        $this->title = $title;
        $this->description = $desc;
        $this->category = $cat;
        $this->image = $img;
        $this->author = $author;
        $this->error = $error;
        $this->pubdate = $pubdate;
        $this->crawled_at = $crawled_at;
    }

    public function save()
    {
        $storage = AbstractStorage::getInstance();
        $storage->saveChannel($this);
    }
}
