<?php
/**
 * @package    Joomla.Shell
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined('_JEXEC'))
{
	// Initialize Joomla framework
	define('_JEXEC', 1);
}

@ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(dirname(__DIR__)) . '/defines.php'))
{
	require_once dirname(dirname(__DIR__)) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(__DIR__)));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

/**
 * Export the Joomla database.
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class DatabaseDump extends JApplicationCli
{
	public function doExecute()
	{
		$config = JFactory::getConfig();

		$this->out("Enter the relative path to the folder where you want to save the sql dump...");
		$path = $this->in();

		if(file_exists(JPATH_BASE . "/" . $path))
		{
			$username = $config->get('user');
			$password = $config->get('password');
			$database = $config->get('db');

			$this->out('Exporting database...');
			exec("mysqldump --user={$username} --password={$password} --quick --add-drop-table --add-locks --extended-insert --lock-tables {$database} > " . JPATH_SITE . "/" . $path . "/database-backup.sql");
		}
		else
		{
			$this->out("The specified path does not exist.");
		}
	}
}

JApplicationCli::getInstance('DatabaseDump')->execute();