<?php
/**
 * Pic Puller for Craft CMS
 *
 * PicPuller Variable
 *
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picpuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPullerVariable
{
    public function __constructor(Array $tags = null)
    {
        return "constructor";
    }

    /**
     * Get user info from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function user($tags = null) {
        return craft()->picPuller_feed->user($tags);
    }

    /**
     * Get a single piece of media from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function media($tags = null) {
        return craft()->picPuller_feed->media($tags);
    }

    /**
     * Get recent media from a single user from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function media_recent($tags = null) {
        return craft()->picPuller_feed->media_recent($tags);
    }

    /**
     * Get media based on a single tag from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function tagged_media($tags = null) {
        return craft()->picPuller_feed->tagged_media($tags);
    }

    /**
     * Get the feed (those people a user follows) of the authorized user from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function user_feed($tags = null) {
        return craft()->picPuller_feed->user_feed($tags);
    }

    /**
     * Get the liked media of the authorized user from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function user_liked($tags = null) {
        return craft()->picPuller_feed->user_liked($tags);
    }

    /**
     * Get popular photos from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function popular($tags = null) {
        return craft()->picPuller_feed->popular($tags);
    }

    // The following are used within the control panel

    public function getUserOauthValue( $id ) {
        return craft()->picPuller_appManagement->getUserOauthValue($id);
    }

    public function getUserOauthId( $id ) {
        return craft()->picPuller_appManagement->getUserOauthId($id);
    }

    public function getUsers() {
        return craft()->picPuller_appManagement->getUsers();
    }
}