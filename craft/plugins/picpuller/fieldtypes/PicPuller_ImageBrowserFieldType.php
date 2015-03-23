<?php
namespace Craft;

class PicPuller_ImageBrowserFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('Pic Puller for Instagram');
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

    public function getSettingsHtml()
    {
        return craft()->templates->render('picpuller/_fieldtype/settings', array(
            'settings' => $this->getSettings()
        ));
    }

	public function getInputHtml($name, $value)
	{
		// Reformat the input name into something that looks more like an ID
    	$id = craft()->templates->formatInputId($name);

    	// Figure out what that ID is going to look like once it has been namespaced
	    $namespacedId = craft()->templates->namespaceInputId($id);

		// Include the PP Javascript
		craft()->templates->includeJs("var PicPuller = {'adminPath':'" . craft()->config->get('cpTrigger') . "', 'userId':'". craft()->userSession->getUser()->id ."', 'fieldId': '". $namespacedId ."-field'};");
		craft()->templates->includeJsResource('picpuller/_fieldtype/imagebrowser.js');
		craft()->templates->includeJs("$('#{$namespacedId}').imagebrowser();");

		return craft()->templates->render('picpuller/_fieldtype/imagebrowser', array(
				'name' => $name,
				'id' => $id,
				'value' => $value,
				'settings' => $this->getSettings(),
				'browseButtonLabel' => $this->getBrowseButtonLabel(),
			));
	}
}