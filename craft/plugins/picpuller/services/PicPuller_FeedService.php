<?php
/**
 * Pic Puller for Craft CMS
 *
 * PicPuller_Feed Service
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picPuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPuller_FeedService extends BaseApplicationComponent
{

    // This is the URL for v1 of the Instagram API
    const IG_API_URL = 'https://api.instagram.com/v1/';

    private $cache_name = 'picpuller';
    private $_ig_picpuller_prefix = '';
    private $use_stale_cache = TRUE;

    // $refresh stores the amount of time we'll keep cached data (urls, not actual images) from Instagram
    private $refresh = 1440;    // Period between cache refreshes, in minutes. 1440 is 24 hours.

    // protected $authorizationRecord;

    public function __construct($authorizationRecord=null)
    {
        // $this->authorizationRecord = $authorizationRecord;
        // if (is_null($this->authorizationRecord)) {
        //     $this->authorizationRecord = PicPuller_AuthorizationRecord::model();
        // }
    }

    /**
     * User
     *
     * Get the user information from a specified Craft user that has authorized the Instagram application
     * http://instagram.com/developer/endpoints/users/#get_users
     * @param   tag param, 'user_id', the Craft member ID of a user that has authorized the Instagram application
     * @param   use_stale_cache:
     * @return  tag data, username, bio, profile_picture, website, full_name, counts_media, counts_followed_by, counts_follows, id, status
     */

    public function user($tags = null)
    {
        Craft::log('Pic Puller: user');

        $variables = array();

        $user_id = isset($tags['user_id']) ? $tags['user_id'] : '';

        if ( $user_id == '' ) {
            return $this->_missinguser_idErrorReturn();
        }

        $use_stale_cache = isset($tags['use_stale_cache']) ? $tags['use_stale_cache'] : $this->use_stale_cache;

        $oauth = $this->_getUserOauth($user_id);

        if(!$oauth)
        {
            return $this->_unauthorizedUserErrorReturn();
        }

        $ig_user_id = $this->_getInstagramId($user_id);


        // // set up the USERS url used by Instagram
        $query_string = "users/$ig_user_id?access_token={$oauth}";

        $data = $this->_fetch_data($query_string, $use_stale_cache);

        if ($data['status'] === FALSE ) {
            // No images to return, even from cache, so exit the function and return the error
            // Set up the basic error messages returned by _fetch_data function
            $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'],
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'],
                $this->_ig_picpuller_prefix.'status' => $data['status']
            );
            return $variables;
        }


        $cacheddata = (isset($data['cacheddata'])) ? TRUE : FALSE;
        $node = $data['data'];
        $variables[] = array(
            $this->_ig_picpuller_prefix.'username' => $node['username'],
            $this->_ig_picpuller_prefix.'bio' => $node['bio'],
            $this->_ig_picpuller_prefix.'profile_picture' => $node['profile_picture'],
            $this->_ig_picpuller_prefix.'website' => $node['website'],
            $this->_ig_picpuller_prefix.'full_name' => $node['full_name'],
            $this->_ig_picpuller_prefix.'counts_media' => strval($node['counts']['media']),
            $this->_ig_picpuller_prefix.'counts_followed_by' => strval($node['counts']['followed_by']),
            $this->_ig_picpuller_prefix.'counts_follows' => strval($node['counts']['follows']),
            $this->_ig_picpuller_prefix.'id' => $node['id'],
            $this->_ig_picpuller_prefix.'status' => $data['status'],
            $this->_ig_picpuller_prefix.'cacheddata' => $cacheddata,
            $this->_ig_picpuller_prefix.'error_type' => $data['error_type'],
            $this->_ig_picpuller_prefix.'error_message' => $data['error_message']
        );
        return $variables;
    }


    /**
     * Media
     *
     * Get information about a single media object.
     * http://instagram.com/developer/endpoints/media/#get_media
     *
     * @access  public
     * @param   tag param: 'user_id', the Craft member ID of a user that has authorized the Instagram application
     * @param   tag param: 'media_id', the Instagram media ID of the image to be returned
     * @param   use_stale_cache:
     * @return  tag data: status, username, user_id, full_name, profile_picture, website, created_time, link, caption, low_resolution, thumbnail, standard_resolution, latitude, longitude, likes
     */
     public function media($tags = null)
     {
        Craft::log('Pic Puller: media');

        $variables = array();
        $use_stale_cache = isset($tags['use_stale_cache']) ? $tags['use_stale_cache'] : $this->use_stale_cache;

        $user_id = isset($tags['user_id']) ? $tags['user_id'] : '';

        if ( $user_id == '' ) {
            return $this->_missinguser_idErrorReturn();
        }


        $oauth = $this->_getUserOauth($user_id);

        if(!$oauth)
        {
            return $this->_unauthorizedUserErrorReturn();
        }

        $media_id = isset($tags['media_id']) ? $tags['media_id'] : '';

        if($media_id == '')
        {
            $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => 'MissingReqParameter',
                $this->_ig_picpuller_prefix.'error_message' => 'No media_id set for this function',
                $this->_ig_picpuller_prefix.'status' => FALSE
            );

            return $variables;
        }

        // set up the MEDIA url used by Instagram
        $query_string = "media/{$media_id}?access_token={$oauth}";

        $data = $this->_fetch_data($query_string, $use_stale_cache);

        if ($data['status'] === FALSE ) {
            // No images to return, even from cache, so exit the function and return the error
            // Set up the basic error messages returned by _fetch_data function
            $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'],
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'],
                $this->_ig_picpuller_prefix.'status' => $data['status'],
                $this->_ig_picpuller_prefix.'code' => $data['code']
            );
            return $variables;
        }

        $cacheddata = (isset($data['cacheddata'])) ? TRUE : FALSE;

        $node = $data['data'];

        if( isset($node['caption']['text']) ) {
            $caption = $node['caption']['text'];
            $titletohashpattern = '/^[^#]*(?!#)/';
            preg_match($titletohashpattern, $caption, $captionTitle);
        }

        $tags = $node['tags'];

        $variables[] = array(
            $this->_ig_picpuller_prefix.'type' => $node['type'],
            $this->_ig_picpuller_prefix.'video_low_bandwidth' => isset($node['videos']['low_bandwidth']['url']) ? $node['videos']['low_bandwidth']['url'] : "",
            $this->_ig_picpuller_prefix.'video_low_bandwidth_width' => isset($node['videos']['low_bandwidth']['width']) ? $node['videos']['low_bandwidth']['width'] : "",
            $this->_ig_picpuller_prefix.'video_low_bandwidth_height' => isset($node['videos']['low_bandwidth']['height']) ? $node['videos']['low_bandwidth']['height'] : "",
            $this->_ig_picpuller_prefix.'video_low_resolution' => isset($node['videos']['low_resolution']['url']) ? $node['videos']['low_resolution']['url'] : "",
            $this->_ig_picpuller_prefix.'video_low_resolution_width' => isset($node['videos']['low_resolution']['width']) ? $node['videos']['low_resolution']['width'] : "",
            $this->_ig_picpuller_prefix.'video_low_resolution_height' => isset($node['videos']['low_resolution']['height']) ? $node['videos']['low_resolution']['height'] : "",
            $this->_ig_picpuller_prefix.'video_standard_resolution' => isset($node['videos']['standard_resolution']['url']) ? $node['videos']['standard_resolution']['url'] : "",
            $this->_ig_picpuller_prefix.'video_standard_resolution_width' => isset($node['videos']['standard_resolution']['width']) ? $node['videos']['standard_resolution']['width'] : "",
            $this->_ig_picpuller_prefix.'video_standard_resolution_height' => isset($node['videos']['standard_resolution']['height']) ? $node['videos']['standard_resolution']['height'] : "",
            $this->_ig_picpuller_prefix.'username' => $node['user']['username'],
            $this->_ig_picpuller_prefix.'user_id' => $node['user']['id'],
            $this->_ig_picpuller_prefix.'full_name' => $node['user']['full_name'],
            $this->_ig_picpuller_prefix.'profile_picture' => $node['user']['profile_picture'],
            $this->_ig_picpuller_prefix.'created_time' => $node['created_time'],
            $this->_ig_picpuller_prefix.'link' => $node['link'],
            $this->_ig_picpuller_prefix.'caption' => isset($caption) ? $caption : '',
            $this->_ig_picpuller_prefix.'caption_only' => isset($captionTitle[0]) ? $captionTitle[0] : '' ,
            $this->_ig_picpuller_prefix.'tags' => isset($tags) ? $tags : [] ,
            $this->_ig_picpuller_prefix.'low_resolution' => $node['images']['low_resolution']['url'],
            $this->_ig_picpuller_prefix.'low_resolution_width' => isset($node['images']['low_resolution']['width']) ? $node['images']['low_resolution']['width'] : '',
            $this->_ig_picpuller_prefix.'low_resolution_height' => isset($node['images']['low_resolution']['height']) ? $node['images']['low_resolution']['height'] : '',
            $this->_ig_picpuller_prefix.'thumbnail' => $node['images']['thumbnail']['url'],
            $this->_ig_picpuller_prefix.'thumbnail_width' => isset($node['images']['thumbnail']['width']) ? $node['images']['thumbnail']['width'] : '',
            $this->_ig_picpuller_prefix.'thumbnail_height' => isset($node['images']['thumbnail']['height']) ? $node['images']['thumbnail']['height'] : '',
            $this->_ig_picpuller_prefix.'standard_resolution' => $node['images']['standard_resolution']['url'],
            $this->_ig_picpuller_prefix.'standard_resolution_width' => isset($node['images']['standard_resolution']['width']) ? $node['images']['standard_resolution']['width'] : '',
            $this->_ig_picpuller_prefix.'standard_resolution_height' => isset($node['images']['standard_resolution']['height']) ? $node['images']['standard_resolution']['height'] : '',
            $this->_ig_picpuller_prefix.'latitude' => isset($node['location']['latitude']) ? $node['location']['latitude'] : '',
            $this->_ig_picpuller_prefix.'longitude' => isset($node['location']['longitude']) ? $node['location']['longitude'] : '',
            $this->_ig_picpuller_prefix.'comment_count' => $node['comments']['count'],
            $this->_ig_picpuller_prefix.'likes' => $node['likes']['count'],
            $this->_ig_picpuller_prefix.'cacheddata' => $cacheddata,
            $this->_ig_picpuller_prefix.'error_type' => $data['error_type'],
            $this->_ig_picpuller_prefix.'error_message' => $data['error_message'],
            $this->_ig_picpuller_prefix.'status' => $data['status']
        );
        return $variables;
     }

    /**
     * Media Recent
     *
     * Get the most recent media published from a specified Craft user that has authorized the Instagram application
     * http://instagram.com/developer/endpoints/users/#get_users_media_recent
     *
     * @access  public
     * @param   tag param: 'user_id', the Craft member ID of a user that has authorized the Instagram application
     * @param   tag param: 'limit', an integer that determines how many images to return
     * @param   use_stale_cache:
     * @return  tag data: caption, media_id, next_max_id, low_resolution, thumbnail, standard_resolution, latitude, longitude, link, created_time
     */

    public function media_recent($tags = null)
    {
        Craft::log('Pic Puller: media_recent');
        $variables = array();

        $user_id = isset($tags['user_id']) ? $tags['user_id'] : '';

        if ( $user_id == '' ) {
            return $this->_missinguser_idErrorReturn();
        }

        $use_stale_cache = isset($tags['use_stale_cache']) ? $tags['use_stale_cache'] : $this->use_stale_cache;

        $limit = isset($tags['limit']) ? $tags['limit'] : '';

        if($limit != '')
        {
            $limit = "&count=$limit";
        }

        $min_id = isset($tags['min_id']) ? $tags['min_id'] : '';

        if($min_id != '')
        {
            $min_id = "&min_id=$min_id";
        }

        $max_id = isset($tags['max_id']) ? $tags['max_id'] : '';

        if($max_id != '')
        {
            $max_id = "&max_id=$max_id";
        }

        $oauth = $this->_getUserOauth($user_id);

        if(!$oauth)
        {
            return $this->_unauthorizedUserErrorReturn();
        }

         $ig_user_id = $this->_getInstagramId($user_id);
        // set up the MEDIA/RECENT url used by Instagram
        $query_string = "users/{$ig_user_id}/media/recent/?access_token={$oauth}". $limit.$max_id.$min_id;

        $data = $this->_fetch_data($query_string, $use_stale_cache);

        if ($data['status'] === FALSE ) {
            // No images to return, even from cache, so exit the function and return the error
            // Set up the basic error messages returned by _fetch_data function
            $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'],
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'],
                $this->_ig_picpuller_prefix.'status' => $data['status']
            );
            return $variables;
        }

        $node = $data['data'];

        $next_max_id = '';
        if (isset($data['pagination']['next_max_id'])){
            $next_max_id = $data['pagination']['next_max_id'];
        }

        $cacheddata = (isset($data['cacheddata'])) ? TRUE : FALSE;

        foreach($data['data'] as $node)
        {
            if( isset($node['caption']['text']) ) {
                $caption = $node['caption']['text'];
                $titletohashpattern = '/^[^#]*(?!#)/';
                preg_match($titletohashpattern, $caption, $captionTitle);
            }

            $tags = $node['tags'];

            $variables[] = array(
                $this->_ig_picpuller_prefix.'type' => $node['type'],
                $this->_ig_picpuller_prefix.'video_low_bandwidth' => isset($node['videos']['low_bandwidth']['url']) ? $node['videos']['low_bandwidth']['url'] : "",
                $this->_ig_picpuller_prefix.'video_low_bandwidth_width' => isset($node['videos']['low_bandwidth']['width']) ? $node['videos']['low_bandwidth']['width'] : "",
                $this->_ig_picpuller_prefix.'video_low_bandwidth_height' => isset($node['videos']['low_bandwidth']['height']) ? $node['videos']['low_bandwidth']['height'] : "",
                $this->_ig_picpuller_prefix.'video_low_resolution' => isset($node['videos']['low_resolution']['url']) ? $node['videos']['low_resolution']['url'] : "",
                $this->_ig_picpuller_prefix.'video_low_resolution_width' => isset($node['videos']['low_resolution']['width']) ? $node['videos']['low_resolution']['width'] : "",
                $this->_ig_picpuller_prefix.'video_low_resolution_height' => isset($node['videos']['low_resolution']['height']) ? $node['videos']['low_resolution']['height'] : "",
                $this->_ig_picpuller_prefix.'video_standard_resolution' => isset($node['videos']['standard_resolution']['url']) ? $node['videos']['standard_resolution']['url'] : "",
                $this->_ig_picpuller_prefix.'video_standard_resolution_width' => isset($node['videos']['standard_resolution']['width']) ? $node['videos']['standard_resolution']['width'] : "",
                $this->_ig_picpuller_prefix.'video_standard_resolution_height' => isset($node['videos']['standard_resolution']['height']) ? $node['videos']['standard_resolution']['height'] : "",
                $this->_ig_picpuller_prefix.'created_time' => $node['created_time'],
                $this->_ig_picpuller_prefix.'link' => $node['link'],
                $this->_ig_picpuller_prefix.'caption' => isset($caption) ? $caption : '',
                $this->_ig_picpuller_prefix.'caption_only' => isset($captionTitle[0]) ? $captionTitle[0] : '' ,
                $this->_ig_picpuller_prefix.'tags' => isset($tags) ? $tags : [] ,
                $this->_ig_picpuller_prefix.'low_resolution' => $node['images']['low_resolution']['url'],
                $this->_ig_picpuller_prefix.'low_resolution_width' => isset($node['images']['low_resolution']['width']) ? $node['images']['low_resolution']['width'] : '',
                $this->_ig_picpuller_prefix.'low_resolution_height' => isset($node['images']['low_resolution']['height']) ? $node['images']['low_resolution']['height'] : '',
                $this->_ig_picpuller_prefix.'thumbnail' => $node['images']['thumbnail']['url'],
                $this->_ig_picpuller_prefix.'thumbnail_width' => isset($node['images']['thumbnail']['width']) ? $node['images']['thumbnail']['width'] : '',
                $this->_ig_picpuller_prefix.'thumbnail_height' => isset($node['images']['thumbnail']['height']) ? $node['images']['thumbnail']['height'] : '',
                $this->_ig_picpuller_prefix.'standard_resolution' => $node['images']['standard_resolution']['url'],
                $this->_ig_picpuller_prefix.'standard_resolution_width' => isset($node['images']['standard_resolution']['width']) ? $node['images']['standard_resolution']['width'] : '',
                $this->_ig_picpuller_prefix.'standard_resolution_height' => isset($node['images']['standard_resolution']['height']) ? $node['images']['standard_resolution']['height'] : '',
                $this->_ig_picpuller_prefix.'latitude' => isset($node['location']['latitude']) ? $node['location']['latitude'] : '',
                $this->_ig_picpuller_prefix.'longitude' => isset($node['location']['longitude']) ? $node['location']['longitude'] : '',
                $this->_ig_picpuller_prefix.'media_id' => $node['id'],
                $this->_ig_picpuller_prefix.'next_max_id' => $next_max_id,
                $this->_ig_picpuller_prefix.'comment_count' => $node['comments']['count'],
                $this->_ig_picpuller_prefix.'likes' => $node['likes']['count'],
                $this->_ig_picpuller_prefix.'cacheddata' => $cacheddata,
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'],
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'],
                $this->_ig_picpuller_prefix.'status' => $data['status']
            );
        }
        return $variables;
    }

    /**
     * Get popular photos from Instagram
     * @access public
     * @param  Array    limit
     * @return Arra     An array of images and associated information
     */
    public function popular($tags = null)
    {
        $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => 'API Endpoint Removed by Instagram',
                $this->_ig_picpuller_prefix.'error_message' => 'Instagram has terminated access to the SELF feed for all 3rd party apps.',
                $this->_ig_picpuller_prefix.'status' => FALSE
            );
        return $variables;
    }

    /**
     * Get user_liked photos from Instagram
     * @access public
     * @param  Array    limit
     * @return Arra     An array of images and associated information
     */
    public function user_liked($tags = null)
    {
        $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => 'API Endpoint Access Denied by Instagram',
                $this->_ig_picpuller_prefix.'error_message' => 'Instagram does not allow access to this API endpoint.',
                $this->_ig_picpuller_prefix.'status' => FALSE
            );
        return $variables;
    }

    /**
     * Get tagged_media photos from Instagram
     * @access public
     * @param  Array    limit
     * @return Arra     An array of images and associated information
     */
    public function tagged_media($tags = null)
    {
        $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => 'API Endpoint Access Denied by Instagram',
                $this->_ig_picpuller_prefix.'error_message' => 'Instagram does not allow access to this API endpoint.',
                $this->_ig_picpuller_prefix.'status' => FALSE
            );
        return $variables;
    }

    /**
     * Get user_feed photos from Instagram
     * @access public
     * @param  Array    limit
     * @return Arra     An array of images and associated information
     */
    public function user_feed($tags = null)
    {
        $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => 'API Endpoint Removed by Instagram',
                $this->_ig_picpuller_prefix.'error_message' => 'Instagram has terminated access.',
                $this->_ig_picpuller_prefix.'status' => FALSE
            );
        return $variables;
    }



    /*****************************************
    *                                        *
    *     -------------------------------    *
    *     PRIVATE HELPER FUNCTIONS FOLLOW    *
    *     -------------------------------    *
    *                  ***                   *
    *                                        *
    *****************************************/

    /**
     * A single function to return a consistent error message when a Craft user_id has not been supplied to a function
     * @return ARR error_type, error_message, and status
     */
    private function _missinguser_idErrorReturn() {
        $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => 'MissingReqParameter',
                $this->_ig_picpuller_prefix.'error_message' => 'No user ID set for this function',
                $this->_ig_picpuller_prefix.'status' => FALSE
            );
        return $variables;
    }

    /**
     * Get user oAuth by Craft user ID
     * @param  INT $user_id Craft user ID
     * @return STR     oAuth code for Instagram user for this app
     */
    private function _getUserOauth( $user_id ) {
        return craft()->picPuller_appManagement->getUserOauthValue($user_id);
    }


    /**
     * Get Instagram ID
     *
     * Get Instagram ID for an Craft member ID
     *
     * @access  private
     * @param   string - User ID number for an Craft member
     * @return  mixed - returns Instagram ID if available in DB, or FALSE if unavailable
     */

    private function _getInstagramId($user_id)
    {
        return craft()->picPuller_appManagement->getInstagramId($user_id);
    }

    /**
     * A single function to return a consistent error message when a Craft user hasn't authorized Pic Puller
     * @return ARR error_type, error_message, and status
     */
    private function _unauthorizedUserErrorReturn() {
        $variables[] = array(
                $this->_ig_picpuller_prefix.'error_type' => 'UnauthorizedUser',
                $this->_ig_picpuller_prefix.'error_message' => 'User has not authorized Pic Puller for access to Instagram.',
                $this->_ig_picpuller_prefix.'status' => FALSE
            );

        return $variables;
    }


    /**
     * Fetch Data
     *
     * Using CURL, fetch requested Instagram URL and return with validated data
     *
     * @access  private
     * @param   string - a full Instagram API call URL
     * @return  array - the original data or cached data (if stale allowed) with the error array
     */

    private function _fetch_data($url, $use_stale_cache)
    {
        $options = array(
                    'debug' => false,
                    'CURLOPT_RETURNTRANSFER' => 1,
                    'CURLOPT_SSL_VERIFYPEER' => false,
                    'CURLOPT_TIMEOUT_MS' => 1000,
                    'CURLOPT_NOSIGNAL' => 1,
                );
        $client = new \Guzzle\Http\Client();
        $request = $client->createRequest('GET', self::IG_API_URL.$url, $options);
        try
        {
            $response = $request->send();
        }
        catch (\Guzzle\Http\Exception\BadResponseException $e)
        {
            Craft::log('The request to '.self::IG_API_URL.$url.' failed: '.$e->getMessage(), LogLevel::Warning, false, 'PicPuller');
            $response = $e->getResponse();
        }

        $body = JsonHelper::decode($response->getBody());

        $valid_data = $this->_validate_data($body, $url, $use_stale_cache);
        return $valid_data;
    }

    /**
     * Validate Data
     *
     * Validate that data coming in from an Instagram API call is valid data and respond with that data plus error_state details
     *
     * @access  private
     * @param   string - the data to validate
     * @param   string - the URL that generated that data
     * @return  array - the original data or cached data (if stale allowed) with the error array
     */

    private function _validate_data($data, $url, $use_stale_cache){
        $meta = $data['meta'];
        if ($meta['code'] == 200)
        {
            // There is an outlying chance that IG says 200, but the data array is empty.
            // Pic Puller considers that an error so it returns a custom error message
            if(count($data['data']) == 0) {
                $error_array = array(
                    'status' => FALSE,
                    // 'code' => (isset($meta['code']) ? $meta['code'] : '000' ),
                    'error_message' => "There was no media to return for that user.",
                    'error_type' => 'NoData'
                );
            }
            else
            {
                $error_array = array(
                    'status' => TRUE,
                    // 'code' => (isset($meta['code']) ? $meta['code'] : '000' ),
                    'error_message' => "Nothing wrong here. Move along.",
                    'error_type' => 'NoError'
                );
                // Fresher valid data was received, so update the cache to reflect that.
                $this->_write_cache($data, $url);
            }
        }
        else
        {
            if ($use_stale_cache == TRUE)
            {
                $data = $this->_check_cache($url);

                if ($data) {
                    $data['cacheddata'] = TRUE;
                    $data['code'] = (isset($meta['code']) ? $meta['code'] : '000' );
                    $error_array = array(
                        'status' => TRUE,
                        'code' => (isset($meta['code']) ? $meta['code'] : '000' ),
                        'error_message' => (isset($meta['error_message']) ? $meta['error_message'] : 'No data returned from Instagram API. Check http://api-status.com/6404/174981/Instagram-API. Using cached data.' ), //. ' Using stale data as back up if available.',
                        'error_type' =>  (isset($meta['error_type']) ? $meta['error_type'] : 'NoCodeReturned')
                    );
                }
            }
            else {
                $data['cacheddata'] = FALSE;
                $data['code'] = (isset($meta['code']) ? $meta['code'] : '000' );
                $error_array = array(
                            'status' => FALSE,
                            'code' => (isset($meta['code']) ? $meta['code'] : '000' ),
                            'error_message' => (isset($meta['error_message']) ? $meta['error_message'] : 'No error message provided by Instagram. No cached data available.' ),
                            'error_type' =>  (isset($meta['error_type']) ? $meta['error_type'] : 'NoCodeReturned')
                        );
            }
        }
        // merge the original data or cached data (if stale allowed) with the error array
        $returnedData =  array_merge($data, $error_array);

        return $returnedData;
    }

    // ---------- CACHE CONTROL/ ------------- //

    /**
     * Check Cache
     *
     * Check for cached data
     *
     * @access  private
     * @param   string
     * @param   bool    Allow pulling of stale cache file
     * @return  mixed - string if pulling from cache, FALSE if not
     */
    private function _check_cache($url)
    {
        // Check for cache directory
        Craft::log('Pic Puller: Checking Cache');
        $cacheDirectory = craft()->path->getCachePath() . '/' . $this->cache_name . '/';

        if ( ! IOHelper::folderExists($cacheDirectory)){
            Craft::log("Cache folder does not exist; no cache to check for.");
            return FALSE;
        }

        // Check for cache file

        $file = $cacheDirectory.md5($url);

        if ( ! IOHelper::fileExists($file)){
            Craft::log("Pic Puller cache file DOES NOT exist for this request.");
            return FALSE;
        }

        $cache = IOHelper::getFileContents($file);

        // Grab the timestamp from the first line

        $eol = strpos($cache, "\n");

        $timestamp = substr($cache, 0, $eol);
        $cache = trim((substr($cache, $eol)));

        if (time() > ($timestamp + ($this->refresh * 60)))
        {
            return FALSE;
        }

        Craft::log("Instagram data retrieved from cache.");

        $cache = JsonHelper::decode($cache);

        return $cache;
    }


    /**
     * Write Cache
     *
     * Write the cached data
     *
     * @access  private
     * @param   string
     * @return  void
     */
    private function _write_cache($data, $url)
    {
        Craft::log('Pic Puller: _write_cache $data '. gettype($data));
        $data = json_encode($data);

        // Figure out the cache directory path and name
        $cacheDirectory = craft()->path->getCachePath() . '/' . $this->cache_name . '/';
        // Make sure the folder exists and create it if it doesn't.
        IOHelper::ensureFolderExists($cacheDirectory);

        // \FB::info($cacheDirectory, 'cacheDirectory');
        // \FB::info($url, 'url');

        // add a timestamp to the top of the file
        $data = time()."\n".$data;

        $file = $cacheDirectory.md5($url);
        // \FB::info($file, 'file');
        // Write it out to the file
        IOHelper::writeToFile($file , $data , true);

        // now clean up the cache
        $this->_clear_cache();
    }

    /**
     * Clear out the cache directory and keep only the 50 most recent files
     * @return NULL
     */
    private function _clear_cache()
    {
        $cacheDirectory = craft()->path->getCachePath() . '/' . $this->cache_name . '/';
        $file = '*';
        $dir = $cacheDirectory;

        $sorted_array = $this->listdir_by_date($dir.$file);

        // \FB::info($sorted_array, 'sorted_array');

        $count = count($sorted_array);
        foreach ($sorted_array as $value) {
            if($count > 50 ){
            // unlinking, as in deleting, cache files that are oldest, but keeping 25 most recent
            unlink($dir.$value);
            }
            $count--;
        }
    }

    /**
     * List files in a directory by the date created
     * @param  STR $pathtosearch The server path to the directory in question
     * @return ARR The files in order
     */
    private function listdir_by_date($pathtosearch)
    {
        foreach (glob($pathtosearch) as $filename)
        {
            $file_array[filectime($filename)]=basename($filename); // or just $filename
        }
        ksort($file_array);

        return $file_array;
    }

}
