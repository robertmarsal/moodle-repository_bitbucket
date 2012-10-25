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
 * This plugin is used to access a bitbucket repository
 *
 * @package    repository
 * @subpackage bitbucket
 * @copyright  2012 Robert Boloc
 * @author     Robert Boloc <robertboloc@gmail.com>
 * @license    The MIT License (MIT)
 */
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/repository/bitbucket/bitbucket.php');

class repository_bitbucket extends repository {
    /**
     * Username preference identifier
     */

    const USERNAME = 'bitbucket_username';

    /**
     * Bitbucket api client instance
     * 
     * @var bitbucket
     */
    private $client;

    /**
     * Checks if username is set
     * 
     * @return boolean
     */
    public function check_login() {

        $username = get_user_preferences(self::USERNAME, '');
        if (empty($username)) {
            $username = optional_param('bitbucket_username', '', PARAM_ALPHANUM);
        }

        if ($username) {
            set_user_preference(self::USERNAME, $username);
            $this->client = new bitbucket($username);
            return true;
        }

        return false;
    }

    /**
     * Prints the login form
     * 
     * @return \stdClass
     */
    public function print_login() {
        $login = array();
        $form = new stdClass();
        $form->type = 'text';
        $form->label = get_string('search', 'repository_bitbucket');
        $form->name = 'bitbucket_username';

        $login['login'] = array($form);

        return $login;
    }

    /**
     * Builds the file tree, navigation, and options
     * 
     * @param string $path
     * @param int $page
     * @return array
     */
    public function get_listing($path = '', $page = '') {
        $listing = array();
        $listing['dynload'] = true;
        $listing['nosearch'] = true;
        $listing['path'] = array(
            array('name' => get_string('repositories', 'repository_bitbucket'),
                'path' => ''));

        if (empty($path)) {
            $listing['list'] = $this->client->get_repositories();
        } else {
            $fragments = explode('/', $path);
            if (count($fragments) == 1) {
                $listing['path'][] = array('name' => $fragments[0], 'path' => $fragments[0]);
                $listing['list'] = $this->client->get_branches($fragments[0]);
            } else if (count($fragments) > 1) {
                foreach ($fragments as $fragment) {
                    $listing['path'][] = array('name' => $fragment, 'path' => $fragment);
                }

                $listing['list'] = $this->client->get_path_listing($path);
            }
        }

        return $listing;
    }

    /**
     * Unsets the username
     * 
     * @return boolean
     */
    public function logout() {
        $this->username = '';
        unset_user_preference(self::USERNAME);
        return $this->print_login();
    }

}