<?php
/**
 * Pic Puller for Craft CMS
 *
 * PicPuller Latest Widget
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picpuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

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
        // If PP setting indicate that a single IG authorization is shared,
        // that affected the "latest" widget as well
        $shared = craft()->plugins->getPlugin('picpuller')->getSettings()->shareoauth;
        $sharedOauthUser = craft()->plugins->getPlugin('picpuller')->getSettings()->sharedoauthuser;

        if (!$shared) {
            $media_recent = craft()->picPuller_feed->media_recent(array('user_id' => craft()->userSession->user->id, 'limit' => 1));
        } else {
            $media_recent = craft()->picPuller_feed->media_recent(array('user_id' => $sharedOauthUser, 'limit' => 1));
        }

        $renderedTemplate = craft()->templates->render('picpuller/widgets/latest',  array( 'media_recent' => $media_recent) );

        return $renderedTemplate;
    }
}
