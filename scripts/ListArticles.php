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
class ListArticles extends JApplicationCli
{
	public function doExecute()
	{
		$db  = JFactory::getDbo();

		// Get the extensions from the database.
		$query = $db->getQuery(true)
			->select('id, title, state, created_by_alias')
			->from('#__content')
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
			$this->out('Some error occurred while retrieving the extensions');
		}
		else
		{
			printf("%-10s %-10s %-10s %-80s\n","ID", "Created By", "Status", "Title");
			printf("\n");

			foreach ($result as $item)
			{
				printf("%-10s %-10s %-10s %-80s\n",$item->id, $item->created_by_alias, $item->state, $item->title);
			}
		}
	}
}

JApplicationCli::getInstance('ListArticles')->execute();