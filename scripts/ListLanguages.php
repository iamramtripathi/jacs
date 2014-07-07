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

// Get the legacy code
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Get the framework.
require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

/**
 * Get the version number
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class ListLanguages extends JApplicationCli
{

	public function doExecute()
	{
		$db   = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the updates table
		$query->select('update_id, name, version')

			->from('#__updates');

		// This Where clause will avoid to list languages already installed.
		$query->where('extension_id = 0')
			->order('name ASC');
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
			$this->out('Some error occurred while retrieving the languages');
		}
		else
		{
			echo <<<ENDWARNING
===========================================================
ENDWARNING;
			printf("\n");
			printf("%-10s %-40s %-10s\n","ID", "Name", "Version");
			echo <<<ENDWARNING
===========================================================
ENDWARNING;

			printf("\n");

			foreach ($result as $item)
			{
				printf("%-10s %-40s %-10s\n",$item->update_id, $item->name, $item->version);
			}
		}
	}
}

JApplicationCli::getInstance('ListLanguages')->execute();