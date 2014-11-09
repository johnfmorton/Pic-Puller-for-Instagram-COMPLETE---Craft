<?php
namespace Craft;

class PicPuller_OauthModel extends BaseModel
{

    /**
     * Define the attributes this model will have.
     *
     * @return array
     */


    public function getHelpText()
    {
        return "This variable holds the information for negotiating oAuth with Instagram.";
    }


    protected function defineAttributes() {

        $attributes = array(
            'id'    => AttributeType::Number,
            'app_id'    => AttributeType::Number,
            'member_id'    => array( AttributeType::String, 'required' => true ),
            'instagram_id' => array( AttributeType::String, 'required' => true ),
            'oauth' => array( AttributeType::String, 'required' => true ),
        );

        return $attributes;
    }
}
