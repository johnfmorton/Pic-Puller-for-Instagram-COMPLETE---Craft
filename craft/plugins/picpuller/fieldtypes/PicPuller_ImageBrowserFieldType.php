<?php
/**
 * Pic Puller for Craft CMS
 *
 * PicPuller_ImageBrowser FieldType
 *
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picpuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPuller_ImageBrowserFieldType extends BaseFieldType
{
    /**
     * Returns the name of the fieldtype.
     *
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('Pic Puller Image Browser');
    }

    /**
     * Returns the label for the "Browse Instagram" button.
     *
     * @access protected
     * @return string
     */
    protected function getBrowseButtonLabel()
    {
        return Craft::t('Browse Instagram');
    }

    /**
     * Returns the content attribute config.
     *
     * @return mixed
     */
    public function defineContentAttribute()
    {
        return AttributeType::Mixed;
    }

    protected function defineSettings()
    {
        return array(
            // 'ppBrowserType' => array(AttributeType::Number, 'min' => 0)
            'ppBrowserType' => array(AttributeType::Number, 'column' => ColumnType::TinyInt)
        );
        // 0 = user stream image browser
        // 1 = full Instagram tag search
        // 2 = user stream + Instagtam tag search
    }

    /**
     * Returns the field's input HTML.
     *
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    public function getInputHtml($name, $value)
    {
        // Reformat the input name into something that looks more like an ID
        $id = craft()->templates->formatInputId($name);

        // Figure out what that ID is going to look like once it has been namespaced
        $namespacedId = craft()->templates->namespaceInputId($id);

        // Include the PP Javascript
        craft()->templates->includeJs("var picpuller = {'adminPath':'" . craft()->config->get('cpTrigger') . "', 'userId':'". craft()->userSession->getUser()->id ."', 'fieldId': '". $namespacedId ."-field'};");
        craft()->templates->includeJsResource('picpuller/js/fields/imagebrowser.js');
        craft()->templates->includeJs("$('#{$namespacedId}').imagebrowser();");

        return craft()->templates->render('picpuller/fields/imagebrowser', array(
                'name' => $name,
                'id' => $id,
                'value' => $value,
                'settings' => $this->getSettings(),
                'browseButtonLabel' => $this->getBrowseButtonLabel(),
            ));
    }
}