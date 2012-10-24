<?php

class bitbucket {

    const APIBASE = 'https://api.bitbucket.org/1.0';

    private $username;
    private $client;

    public function __construct($username) {
        $this->username = $username;
        $this->client = new curl();
    }

    /**
     * Returns a list of repositories
     * 
     * @return array
     */
    public function get_repositories() {
        $uri = '/users/' . $this->username;
        $repos = $this->get($uri);

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
                    'children' => array(),
                );
            }
        }

        return $files;
    }

    /**
     * Returns the list of branches of a repository
     * 
     * @param string $repo - repository
     * @return array
     */
    public function get_branches($repo) {
        $uri = '/repositories/' . $this->username . '/' . $repo . '/branches';
        $branches = $this->get($uri);

        $files = array();
        foreach ($branches as $branch) {
            $files[] = array(
                'title' => $branch->branch,
                'size' => $branch->size,
                'author' => $branch->author,
                'date' => strtotime($branch->timestamp),
                'path' => $repo . '/' . $branch->branch,
                'type' => 'folder',
                'children' => array(),
            );
        }

        return $files;
    }

    /**
     * Returns a list of files and directories of a path
     *
     * @param string $path - path where to search for files
     * @return array
     */
    public function get_path_listing($path) {
        $fragments = explode('/', $path);
        $repo = array_shift($fragments);
        $branch = array_shift($fragments);

        $src = implode('/', $fragments);

        $uri = '/repositories/' . $this->username . '/' . $repo . '/src/' . $branch . '/' . $src;
        $node = $this->get($uri);

        // List all files
        $files = array();
        foreach ($node->files as $file) {
            $files[] = array(
                'title' => $file->path,
                'size' => $file->size,
                'date' => strtotime($file->timestamp),
                'path' => $path . '/' . $file->path,
                'type' => 'file',
            );
        }

        // List all directories
        foreach ($node->directories as $directory) {
            $files[] = array(
                'title' => $directory,
                'path' => $path . '/' . $directory,
                'type' => 'folder',
                'children' => array(),
            );
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