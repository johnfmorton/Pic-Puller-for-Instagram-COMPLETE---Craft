<?php
namespace Craft;

class PicPuller_CredentialsModel extends BaseModel
{

    /**
     * Define the attributes this model will have.
     *
     * @return array
     */

    public function getHelpText()
    {
        return "This variable holds the client ID and client secret for communicating with Instagram.";
    }

    public function defineAttributes() {
        $attributes = array(
            'id'    => AttributeType::Number,
            'clientId' => array( AttributeType::String, 'required' => true ),
            'clientSecret' => array( AttributeType::String, 'required' => true )
        );

        return $attributes;
    }
}
