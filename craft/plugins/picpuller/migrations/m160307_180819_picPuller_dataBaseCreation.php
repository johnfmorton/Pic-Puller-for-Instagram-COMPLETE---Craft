<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160307_180819_picPuller_dataBaseCreation extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		craft()->db->createCommand()->createTable('picpuller_authorizations', array(
            'user_id'      => array('required' => true),
            'instagram_id' => array('required' => true),
            'oauth'        => array('required' => true),
        ), null, true);

        // craftThank()->db->createCommand->dropForeignKey('picpuller_credentials', 'app_id');
        craft()->db->createCommand()->dropTableIfExists('picpuller_credentials');
        craft()->db->createCommand()->dropTableIfExists('picpuller_oauths');

        return true;
	}
}
