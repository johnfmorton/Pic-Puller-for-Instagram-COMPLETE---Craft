<?php
/**
 * PicPuller plugin for Craft CMS
 *
 * PicPuller_PicPuller Model
 *
 * --snip--
 * Models are containers for data. Just about every time information is passed between services, controllers, and
 * templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 * --snip--
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picPuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPuller_AuthorizationModel extends BaseModel
{
    /**
     * Defines this model's attributes.
     *
     * @return array
     */

    public function getHelpText()
    {
        return "This variable holds oAuth Instagram user ID and authorization (oAuth) associated a Craft CMS user.";
    }

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'user_id' => array( AttributeType::String, 'required' => true ),
            'instagram_id' => array( AttributeType::String, 'required' => true ),
            'oauth' => array( AttributeType::String, 'required' => true ),
        ));
    }

}