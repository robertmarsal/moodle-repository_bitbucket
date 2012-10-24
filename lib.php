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
 * @since 2.0
 * @package    repository_bitbucket
 * @copyright  2012 Robert Boloc <robertboloc@gmail.com>
 * @license    The MIT License (MIT)
 */

require_once($CFG->dirroot . '/repository/lib.php');

class repository_bitbucket extends repository {
        
    const APIROOT = 'https://api.bitbucket.org/1.0/';
    
    public function check_login() {
        return false;
    }
    
    public function print_login() {
        $login = array();
        $form = new stdClass();
        $form->type = 'text';
        $form->label = get_string('search', 'repository_bitbucket');
        $form->name  = 'bitbucket_username';
        
        $login['login'] = array($form);
        
        return $login;
        
    }

    public function get_listing() {
        $listing = array();
        $listing['dynload'] = true;
        $listing['nosearch'] = true;
        
        $listing['list'] = array();
        
        return $listing;
    }

}