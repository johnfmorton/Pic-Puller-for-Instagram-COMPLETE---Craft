<?php
/**
 * Pic Puller for Craft CMS
 *
 * PicPuller_PicPuller Record
 *
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2016 John F Morton
 * @link      http://picpuller.com
 * @package   PicPuller
 * @since     2.0.0
 */

namespace Craft;

class PicPuller_AuthorizationRecord extends BaseRecord
{
    /**
     * Returns the name of the database table the model is associated with (sans table prefix). By convention,
     * tables created by plugins should be prefixed with the plugin name and an underscore.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'picpuller_authorizations';
    }

    /**
     * Returns an array of attributes which map back to columns in the database table.
     *
     * @access protected
     * @return array
     */
   protected function defineAttributes()
    {
        $attributes = array(
            'user_id'    => array( AttributeType::String, 'required' => true ),
            'instagram_id' => array( AttributeType::String, 'required' => true ),
            'oauth' => array( AttributeType::String, 'required' => true ),
            );
        return $attributes;
    }

    /**
     * If your record should have any relationships with other tables, you can specify them with the
     * defineRelations() function
     * @return array
     */
    // public function defineRelations()
    // {
    //     return array(
    //     );
    // }
    //
    /**
     * Create a new instance of the current class. This allows us to
     * properly unit test our service layer.
     *
     * @return BaseRecord
     */
    public function create()
    {
        $class = get_class($this);
        $record = new $class();

        return $record;
    }
}