<?php
/**
 * PicPuller plugin for Craft CMS
 *
 * PicPuller_PicPuller Controller
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picPuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPuller_AuthorizationsController extends BaseController
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = false;

    // protected $allowAnonymous = array('actionIndex',);

    // public function actionIndex() {
    //    $nonsense = array("foo", "bar", "baz", "blong");
    //    json_encode($nonsense);
    //    $this->returnJson($nonsense);
    // }

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/picPuller
     */
    public function actionSaveCredentials()
    {
        Craft::log('PicPuller: saveCredentials action');


        $this->requirePostRequest();

        $model = new PicPuller_AuthorizationModel();

        $attributes = craft()->request->getPost();

        $formattedReturnedData = array(
                'user_id'=>$attributes['user_id'],
                'oauth'=>$attributes['oauth'],
                'instagram_id'=>$attributes['instagram_id'],
            );

        $model->setAttributes($formattedReturnedData);

        /*
        There can be only 1 authorization code per user for Pic Puller 2.
        If there is an existing authorization code, we need to remove it
        before adding the one that was just returned to the control panel.
         */

        // Create the database command looking up an existing authorization
        // for the usse
        $existingOauthId = craft()->db->createCommand()
                ->select('id')
                ->from('picpuller_authorizations')
                ->where(array('and','user_id='.$attributes['user_id']))
                ->queryRow();
        // existingOauthId contains data...
        if ($existingOauthId) {
            // ...we will remove that line from the database
            craft()->picPuller_appManagement->deleteAuthorizationById($existingOauthId['id']);
        }

        // call on the PicPuller_appManagement SERVICE to 'saveCredentials'
        if (craft()->picPuller_appManagement->saveCredentials($model)) {
            $formattedReturnedData['success'] = true;
        } else {
            $formattedReturnedData['success'] = false;
            $formattedReturnedData['message'] = 'Error in Authorization Controller';
        };

        json_encode($formattedReturnedData);
        $this->returnJson($formattedReturnedData);

    }


    /**
     * Remove an Instagram authorization from the database
     * Take a user_id passed in by POST
     * @return [type] [description]
     */
    public function actionRemoveOauth() {

        $this->requirePostRequest();

        $attributes = craft()->request->getPost();

        // TO DO: Think about making this get the user_id not by POST but by
        // simply using the current logged in user's id
        // Downside would be that admin would need to log into each user to
        // delete their authorizations. Not sure this is really an issue though.

        return craft()->picPuller_appManagement->deleteAuthorizationById($attributes['authorization_id']);
    }
}