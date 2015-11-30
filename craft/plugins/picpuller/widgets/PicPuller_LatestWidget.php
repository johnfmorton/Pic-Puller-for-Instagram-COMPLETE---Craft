<?php
namespace Craft;

defined('CRAFT_PLUGINS_PATH')      || define('CRAFT_PLUGINS_PATH',      CRAFT_BASE_PATH.'plugins/');

// require_once(CRAFT_PLUGINS_PATH.'picpuller/lib/FirePHPCore/fb.php');
/*

Digging around? Enable FirePHP debugging by changin "devMode" to true in your config file, or, FB::setEnabled(true);
You'll need to use FirePHP for Firefox or FirePHP4Chrome and look at your console in your web browser

*/

// \FB::setEnabled(craft()->config->get('devMode'));

// Examples:
// \FB::log('Log message', 'Label');
// \FB::info('Info message', 'Label');
// \FB::warn('Warn message', 'Label');
// \FB::error('Error message', 'Label');

class PicPuller_LatestWidget extends BaseWidget
{
    public function getName()
    {
        // Craft::log(__METHOD__, LogLevel::Info, true);
        return Craft::t('Latest Instagram Post');
    }

    /**
     * @inheritDoc IWidget::getIconPath()
     *
     * @return string
     */
    public function getIconPath()
    {
        return craft()->path->getPluginsPath().'picpuller/resources/latest-widget-icon.svg';
    }

    public function getBodyHtml()
    {
        // Craft::log(__METHOD__, LogLevel::Info, true);

        $media_recent = craft()->picPuller_feedReader->media_recent(array('user_id' => craft()->userSession->user->id, 'limit' => 1));
        $renderedTemplate = craft()->templates->render('picpuller/_widgets/latest',  array( 'media_recent' => $media_recent) );

        return $renderedTemplate;
    }
}
