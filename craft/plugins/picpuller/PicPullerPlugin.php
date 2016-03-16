<?php
/**
 * PicPuller plugin for Craft CMS
 *
 * Integrate Instagram into Craft CMS
 *
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picPuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPullerPlugin extends BasePlugin
{
    /**
     * Called after the plugin class is instantiated; do any one-time initialization here such as hooks and events:
     *
     * craft()->on('entries.saveEntry', function(Event $event) {
     *    // ...
     * });
     *
     * or loading any third party Composer packages via:
     *
     * require_once __DIR__ . '/vendor/autoload.php';
     *
     * @return mixed
     */
    public function init()
    {
    }

    /**
     * Returns the user-facing name.
     *
     * @return mixed
     */
    public function getName()
    {
         $shortname = $this->getSettings()->shortname;
        // It should not be possible for the shortname to be empty,
        // but PP checks for it in case something went wrong.
        if ( is_string($shortname) && !empty($shortname) ) {
            return $shortname;
        } else {
            return Craft::t( 'Pic Puller for Craft' );
        }

    }

    /**
     * Plugins can have descriptions of themselves displayed on the Plugins page by adding a getDescription() method
     * on the primary plugin class:
     *
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Integrate Instagram into Craft CMS');
    }

    /**
     * Plugins can have links to their documentation on the Plugins page by adding a getDocumentationUrl() method on
     * the primary plugin class:
     *
     * @return string
     */
    public function getDocumentationUrl()
    {
        return 'http://picPuller.com/documentation/';
    }

    /**
     * Plugins can now take part in Craft’s update notifications, and display release notes on the Updates page, by
     * providing a JSON feed that describes new releases, and adding a getReleaseFeedUrl() method on the primary
     * plugin class.
     *
     * @return string
     */
    public function getReleaseFeedUrl()
    {
        return 'http://picPuller.com/releases.json';
    }

    /**
     * Returns the version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * As of Craft 2.5, Craft no longer takes the whole site down every time a plugin’s version number changes, in
     * case there are any new migrations that need to be run. Instead plugins must explicitly tell Craft that they
     * have new migrations by returning a new (higher) schema version number with a getSchemaVersion() method on
     * their primary plugin class:
     *
     * @return string
     */
    public function getSchemaVersion()
    {
        return '2.0.0';
    }

    /**
     * Returns the developer’s name.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'John F Morton';
    }

    /**
     * Returns the developer’s website URL.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://picpuller.com';
    }

    /**
     * Returns whether the plugin should get its own tab in the CP header.
     *
     * @return bool
     */
    public function hasCpSection()
    {
        $admin = craft()->userSession->getUser()->admin;
        $shareoauth = $this->getSettings()->shareoauth;
        $masteroauthuser = $this->getSettings()->sharedoauthuser;

        $thisUser = craft()->userSession->getUser()->id;
        /*
        If the user is not an admin, i.e. permission is false,
        and the shareoauth is set to true, then we do not show
        the global nav element for non-admin users because
        there is nothing for them to do there.
         */
        if ($shareoauth && ($masteroauthuser !==  $thisUser) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Called right before your plugin’s row gets stored in the plugins database table, and tables have been created
     * for it based on its records.
     */
    public function onBeforeInstall()
    {
    }

    /**
     * Called right after your plugin’s row has been stored in the plugins database table, and tables have been
     * created for it based on its records.
     */
    public function onAfterInstall()
    {
    }

    /**
     * Called right before your plugin’s record-based tables have been deleted, and its row in the plugins table
     * has been deleted.
     */
    public function onBeforeUninstall()
    {
    }

    /**
     * Called right after your plugin’s record-based tables have been deleted, and its row in the plugins table
     * has been deleted.
     */
    public function onAfterUninstall()
    {
    }

    /**
     * Defines the attributes that model your plugin’s available settings.
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'shortname' => array(AttributeType::String, 'label' => 'Short name for Pic Puller for Craft', 'default' => 'Pic Puller for Craft' ),
            'shareoauth' => array(AttributeType::Bool, 'default' => false),
            'sharedoauthuser' =>array(AttributeType::Number, 'default' => 1)
        );
    }

    /**
     * Returns the HTML that displays your plugin’s settings.
     *
     * @return mixed
     */
    public function getSettingsHtml()
    {
       return craft()->templates->render('picpuller/settings/index', array(
           'settings' => $this->getSettings()
       ));
    }

    /**
     * If you need to do any processing on your settings’ post data before they’re saved to the database, you can
     * do it with the prepSettings() method:
     *
     * @param mixed $settings  The Widget's settings
     *
     * @return mixed
     */
    public function prepSettings($settings)
    {
        if ( empty( $settings['shortname'] ) ) {
           $settings['shortname'] = 'Pic Puller for Craft';
        }

        return $settings;
    }

    public function registerCpRoutes()
    {
        // There are 2 routes: 1st is with just the userId, 2nd w userId and nextMaxId
        // using (?P<nextMaxId>\S+) instead of (?P<nextMaxId>\d+) because nextMaxId may be a string "S" and not just digits "d"
        // The 2 routes for searching by tag are similar, they use (?P<tag>\S+) not (?P<tag>\d+) because the tag will be a string

        return array(
            'picpuller/mediarecent' => 'picpuller/fields/mediarecent',
            'picpuller/mediarecent/(?P<nextMaxId>\S+)' => 'picpuller/fields/mediarecent',
            //'picpuller/mediabytag/(?P<searchTag>\S+)' => 'picpuller/fields/mediabytag',
            //'picpuller/mediabytag/(?P<searchTag>\S+)/(?P<nextMaxId>\S+)' => 'picpuller/fields/mediabytag',
            'picpuller/mediabyid/(?P<mediaId>\S+)' => 'picpuller/fields/mediabyid'
       );
    }

}