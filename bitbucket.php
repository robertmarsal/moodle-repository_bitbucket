<?php

class bitbucket {
         
    const APIBASE = 'https://api.bitbucket.org/1.0';
    
    private $username;
    private $client;
    
    public function __construct($username) {
        $this->username = $username;
        $this->client = new curl();
    }

    public function get_repositories() {
        $repos = $this->client->get(self::APIBASE.'/users/'.$this->username);
    }

}