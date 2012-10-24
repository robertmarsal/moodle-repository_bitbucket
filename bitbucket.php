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
        $response = $this->client->get(self::APIBASE . '/users/' . $this->username);
        $repos = json_decode($response);

        $files = array();
        if ($repos) {
            foreach ($repos->repositories as $repo) {
                $files[] = array(
                    'title' => $repo->name,
                    'size' => $repo->size,
                    'date' => strtotime($repo->last_updated),
                    'path' => $repo->name,
                    'type' => 'folder',
                    'icon' => substr($repo->logo, 0, -6) . '32.png',
                );
            }
        }

        return $files;
    }

}