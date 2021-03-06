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

// Get the legacy libraries
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

/**
 * Get the version number
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class ListUsers extends JApplicationCli
{
	public function doExecute()
	{
		$db  = JFactory::getDbo();

		// Get the extensions from the database.
		$query = $db->getQuery(true)
			->select('id, name, username, email, block')
			->from('#__users')
			->where($db->quoteName('id') . '!= ' . 0)
			->order('id ASC');
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->out($e->getMessage());
		}

		if (empty($result))
		{
			$this->out('Some error occurred while retrieving the users');
		}
		else
		{
			printf("%-10s %-20s %-20s %-40s %-10s\n","ID", "Name", "Username", "E-Mail", "Block");
			printf("\n");

			foreach ($result as $item)
			{
				printf("%-10s %-20s %-20s %-40s %-10s\n",$item->id, $item->name, $item->username, $item->email, $item->block);
			}
		}
	}
}

JApplicationCli::getInstance('ListUsers')->execute();