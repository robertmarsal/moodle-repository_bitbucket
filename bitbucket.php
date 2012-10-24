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
        $uri = '/users/' . $this->username;
        $response = $this->client->get(self::APIBASE . $uri);
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
                    'thumbnail' => substr($repo->logo, 0, -6) . '128.png',
                    'children' => $this->get_repo_files($repo),
                );
            }
        }

        return $files;
    }

    private function get_repo_files($repo) {
        $uri = '/repositories/' . $this->username . '/' . $repo->slug . '/branches';
        $branches = $this->get($uri);

        $files = array();
        $directories = array();
        
        foreach ($branches as $branch) {
            $uri = '/repositories/' . $this->username . '/' . $repo->slug . '/src/' . $branch->branch . '/';
            $node = $this->get($uri);

            foreach ($node->files as $file) {
                $files[] = array(
                    'title' => $file->path,
                    'size' => $file->size,
                    'date' => strtotime($file->timestamp),
                    'path' => $repo->name . '/' . $file->path,
                    'type' => 'file',
                );
            }
        }
        
        return $files;
    }

    private function get($uri) {
        $response = $this->client->get(self::APIBASE . $uri);
        if ($response) {
            return json_decode($response);
        }
    }

}