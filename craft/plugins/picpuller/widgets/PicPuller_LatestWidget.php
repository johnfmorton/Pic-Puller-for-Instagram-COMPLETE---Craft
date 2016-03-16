<?php
namespace Craft;

defined('CRAFT_PLUGINS_PATH')      || define('CRAFT_PLUGINS_PATH',      CRAFT_BASE_PATH.'plugins/');

class PicPuller_LatestWidget extends BaseWidget
{
    public function getName()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);
        return Craft::t('Latest Instagram Post');
    }

    /**
     * @inheritDoc IWidget::getIconPath()
     *
     * @return string
     */
    public function getIconPath()
    {
        Craft::log(__METHOD__ . ': ' .craft()->path->getPluginsPath() . 'picpuller/resources/latest-widget-icon.svg', LogLevel::Info, true);
        Craft::log(__METHOD__ . ': ' .craft()->path->getResourcesPath().'images/widgets/feed.svg', LogLevel::Info, true);
        return craft()->path->getPluginsPath().'picpuller/resources/latest-widget-icon.svg';
    }

    public function getBodyHtml()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

//         {% set shareoauth = craft.picpuller.getShareOauthSetting %}

// {% if shareoauth == false %}
//     {% set pp_user = currentUser.id %}
// {% else %}
//     {% set pp_user = craft.picpuller.getSharedOauthUser %}
// {% endif %}
        $shareoauth = craft()->plugins->getPlugin('picpuller')->getSettings()->shareoauth;

        if ($shareoauth == false) {
            $pp_user = craft()->userSession->user->id;
        } else {
            $pp_user = craft()->plugins->getPlugin('picpuller')->getSettings()->sharedoauthuser;
        }

        $media_recent = craft()->picPuller_feed->media_recent(array('user_id' => $pp_user, 'limit' => 1));
        $renderedTemplate = craft()->templates->render('picpuller/widgets/latest',  array( 'media_recent' => $media_recent) );

        return $renderedTemplate;
    }
}
