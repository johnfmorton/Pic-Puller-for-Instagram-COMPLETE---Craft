<?php
namespace Craft;

class PicPullerPlugin extends BasePlugin
{
    function getName() {
        $shortname = $this->getSettings()->shortname;
        if ( is_string($shortname) && !empty($shortname) ) {
            return $shortname;
        } else {
            return Craft::t( 'Pic Puller for Instagram' );
        }
    }

    function getVersion() {
        return '1.2.1';
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
        return true;
    }

    /**
     * Define default setting for Pic Puller
     * @return Array The settings.
     */
    protected function defineSettings() {
        $params = '';
        // return array(
        //     'pp_settings' => array( AttributeType::Mixed, 'default' => array('Pic Puller for Instagram') ),
        // );

        return array(
            //'pp_settings' => array(AttributeType::Mixed, 'default' => array( 'PP', 'Sours', 'Fizzes', 'Juleps'), 'shortname' => 'PP'),
            'shortname' => array(AttributeType::String, 'default' => array( 'Pic Puller for Instagram') )
        );

    }

    public function prepSettings( $settings ) {
        // Modify $settings here...

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
       //  return array(
       //      'picpuller/mediarecent/(?P<userId>\d+)' => 'picpuller/_fieldtype/mediarecent',
       //      'picpuller/mediarecent/(?P<userId>\d+)/(?P<nextMaxId>\S+)' => 'picpuller/_fieldtype/mediarecent',
       //      'picpuller/mediabytag/(?P<searchTag>\S+)' => 'picpuller/_fieldtype/mediabytag',
       //      'picpuller/mediabytag/(?P<searchTag>\S+)/(?P<nextMaxId>\S+)' => 'picpuller/_fieldtype/mediabytag',
       //      'picpuller/mediabyid/(?P<userId>\d+)/(?P<mediaId>\S+)' => 'picpuller/_fieldtype/mediabyid'
       // );
        return array(
            'picpuller/mediarecent' => 'picpuller/_fieldtype/mediarecent',
            'picpuller/mediarecent/(?P<nextMaxId>\S+)' => 'picpuller/_fieldtype/mediarecent',
            'picpuller/mediabytag/(?P<searchTag>\S+)' => 'picpuller/_fieldtype/mediabytag',
            'picpuller/mediabytag/(?P<searchTag>\S+)/(?P<nextMaxId>\S+)' => 'picpuller/_fieldtype/mediabytag',
            'picpuller/mediabyid/(?P<mediaId>\S+)' => 'picpuller/_fieldtype/mediabyid'
       );
    }
}
