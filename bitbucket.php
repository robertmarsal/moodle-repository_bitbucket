<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class for interacting with the Bitbucket API
 * 
 * @package    repository
 * @subpackage bitbucket
 * @copyright  2012 Robert Boloc
 * @author     Robert Boloc <robertboloc@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bitbucket {

    /**
     * Base Bitbucket API url
     */
    const APIBASE = 'https://api.bitbucket.org/1.0';

    /**
     * Bitbucket username
     * 
     * @var string 
     */
    private $username;

    /**
     * Curl instance
     * 
     * @var curl
     */
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
                    'thumbnail' => substr($repo->logo, 0, -6) . '32.png',
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
        
        global $OUTPUT;
        
        $uri = '/repositories/' . $this->username . '/' . $repo . '/branches';
        $branches = $this->get($uri);

        $files = array();
        if ($branches) {
            foreach ($branches as $branch) {
                $files[] = array(
                    'title' => $branch->branch,
                    'size' => $branch->size,
                    'author' => $branch->author,
                    'date' => strtotime($branch->timestamp),
                    'path' => $repo . '/' . $branch->branch,
                    'type' => 'folder',
                    'thumbnail' => $OUTPUT->pix_url('f/folder-32')->out(false),
                    'children' => array(),
                );
            }
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
        
        global $OUTPUT;
        
        $fragments = explode('/', $path);
        $repo = array_shift($fragments);
        $branch = array_shift($fragments);

        $src = implode('/', $fragments);

        $uri = '/repositories/' . $this->username . '/' . $repo . '/src/' . $branch . '/' . $src;
        $node = $this->get($uri);

        $files = array();
        if ($node) {
            // List all files.
            foreach ($node->files as $file) {
                $title = str_replace($src.'/', '', $file->path);
                $pathinfo = pathinfo($file->path);
                $files[] = array(
                    'title' => $title,
                    'size' => $file->size,
                    'date' => strtotime($file->timestamp),
                    'path' => $path . '/' . $file->path,
                    'source' => self::APIBASE.'/repositories/'.$this->username.'/'.$repo.'/raw/'.$branch.'/'.$file->path,
                    'type' => 'file',
                    'thumbnail' => $OUTPUT->pix_url(file_extension_icon('.'.$pathinfo['extension'], 32))->out(false),
                );
            }

            // List all directories.
            foreach ($node->directories as $directory) {
                $files[] = array(
                    'title' => $directory,
                    'path' => $path . '/' . $directory,
                    'type' => 'folder',
                    'thumbnail' => $OUTPUT->pix_url('f/folder-32')->out(false),
                    'children' => array(),
                );
            }
        }

        return $files;
    }

    /**
     * Makes a GET petition using the curl instance
     * 
     * @param string $uri
     * @return array
     */
    private function get($uri) {
        $response = $this->client->get(self::APIBASE . $uri);
        if ($response) {
            return json_decode($response);
        }
    }

}