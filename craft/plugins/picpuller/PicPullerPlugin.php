<?php
namespace Craft;

class PicPullerPlugin extends BasePlugin
{
    function getName() {
        $shortname = $this->getSettings()->shortname;
        // It should not be possible for the shortname to be empty,
        // but PP checks for it in case something went wrong.
        if ( is_string($shortname) && !empty($shortname) ) {
            return $shortname;
        } else {
            return Craft::t( 'Pic Puller for Instagram' );
        }
    }

    function getVersion() {
        return '1.3.4';
    }

    function getDeveloper() {
        return 'John F Morton';
    }

    function getDeveloperUrl() {
        return 'http://craft.picpuller.com';
    }

    /**
     * Has CP Section
     */
    public function hasCpSection() {
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
     * Define default setting for Pic Puller
     * @return Array The settings.
     */
    protected function defineSettings() {
        $params = '';

        return array(
            'shortname' => array(AttributeType::String, 'default' => array( 'Pic Puller for Instagram') ),
            'shareoauth' => array(AttributeType::Bool, 'default' => false),
            'sharedoauthuser' =>array(AttributeType::Number, 'default' => 1)
        );

    }

    public function prepSettings( $settings ) {
        // In case the shortname was empty, replace it with the default name

        if ( empty( $settings['shortname'] ) ) {
           $settings['shortname'] = 'Pic Puller for Instagram';
        }
        return $settings;
    }

    public function getSettingsHtml() {
        return craft()->templates->render( 'picpuller/settings/settings', array(
                'settings' => $this->getSettings()
            ) );
    }

    /**
     * After plugin is installed
     */
    public function onAfterInstall() {

    }

    public function registerCpRoutes()
    {
        // There are 2 routes: 1st is with just the userId, 2nd w userId and nextMaxId
        // using (?P<nextMaxId>\S+) instead of (?P<nextMaxId>\d+) because nextMaxId may be a string "S" and not just digits "d"
        // The 2 routes for searching by tag are similar, they use (?P<tag>\S+) not (?P<tag>\d+) because the tag will be a string

        return array(
            'picpuller/mediarecent' => 'picpuller/_fieldtype/mediarecent',
            'picpuller/mediarecent/(?P<nextMaxId>\S+)' => 'picpuller/_fieldtype/mediarecent',
            'picpuller/mediabytag/(?P<searchTag>\S+)' => 'picpuller/_fieldtype/mediabytag',
            'picpuller/mediabytag/(?P<searchTag>\S+)/(?P<nextMaxId>\S+)' => 'picpuller/_fieldtype/mediabytag',
            'picpuller/mediabyid/(?P<mediaId>\S+)' => 'picpuller/_fieldtype/mediabyid'
       );
    }
}
