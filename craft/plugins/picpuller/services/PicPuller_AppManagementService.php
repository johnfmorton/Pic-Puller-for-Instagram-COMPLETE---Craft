<?php
/**
 * PicPuller plugin for Craft CMS
 *
 * PicPuller_PicPuller Service
 *
 * --snip--
 * All of your plugin’s business logic should go in services, including saving data, retrieving data, etc. They
 * provide APIs that your controllers, template variables, and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 * --snip--
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picPuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPuller_AppManagementService extends BaseApplicationComponent
{

    // This is the client ID for the Pic Puller 2 application
    const IG_CLIENT_ID = '55b44fb02bd146a491edeb0e5dd9ef67';
    protected $authorizationRecord;

    public function __construct($authorizationRecord=null)
    {
        $this->authorizationRecord = $authorizationRecord;
        if (is_null($this->authorizationRecord)) {
            $this->authorizationRecord = PicPuller_AuthorizationRecord::model();
        }
    }

    /**
     * Save oAuth credentials in the database along with the userid they are associated with.
     * @param Model $model PicPuller_AuthorizationModel containing a user_id and oauth from IG
     * @return BOOL true or false
     *
     *  craft()->picPuller_AppManagement->saveCredentials()
     */
    public function saveCredentials(PicPuller_AuthorizationModel &$model) {
        $record = $this->authorizationRecord->create();

        $record->setAttributes($model->getAttributes());

        if ( $record->save() ) {
            return true;
        } else {
            $model->addErrors($record->getErrors());
            return false;
        }
    }

    /**
     * Delete an AuthorizationRecord by its ID
     * @param INT $id the ID of the authorization to delete
     * @return BOOL true or false
     */
    public function deleteAuthorizationById($id) {
        Craft::log('PicPuller service: deleteAppById '.$id);
        if( $this->authorizationRecord->deleteByPk($id) )
        {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the ID of the oAuth for a user based on the user's ID
     * @param  INT $id The ID of the Craft user
     * @return INT     The ID of the oAuth for the user
     */
    public function getUserOauthId( $id ) {
        $query = craft()->db->createCommand()
                ->select('id')
                ->from('picpuller_authorizations')
                ->where('user_id=' . $id )
                ->queryRow();
        return $query['id'];
    }

    /**
     * Return the oAuth value of for a user based on the user's Craft ID
     * @param  INT $id The ID of the Craft user
     * @return STR     The oAuth for the user
     */
    public function getUserOauthValue( $id ) {
        $query = craft()->db->createCommand()
                ->select('oauth')
                ->from('picpuller_authorizations')
                ->where('user_id=' . $id )
                ->queryRow();
        return $query['oauth'];
    }

     /**
     * Return the Instagram ID of for a user based on the user's Craft ID
     * @param  INT $id The ID of the Craft user
     * @return STR     The Instagram ID for the user
     */
    public function getInstagramId($userId) {
        $query = craft()->db->createCommand()
            ->select('instagram_id')
            ->from('picpuller_authorizations')
            ->where('user_id=' . $userId )
            ->queryRow();
        return $query['instagram_id'];
    }

    /**
     * Return users who have Instagram credentials in the database
     * @return ARR
     */
    public function getUsers() {

        $query = craft()->db->createCommand()
                ->select('user_id, instagram_id, oauth, u.firstname, u.lastname, u.username')
                ->from('picpuller_authorizations oauth')
                ->join('users u', 'oauth.user_id=u.id')
                ->order('user_id')
                ->query();
         return $query;
    }
}