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
class ToggleExtension extends JApplicationCli
{
	public function doExecute()
	{

		// Get the Article ID from argument
		$id	= $this->input->get('i', null, 'STRING');

		if (!$id)
		{
			$this->out("Enter article ID...");
			$id = $this->in();
		}

		$db  = JFactory::getDbo();

		// Get the articles from the database.
		$query = $db->getQuery(true)
			->select('state')
			->from('#__content')
			->where($db->quoteName('id') . ' = ' . $id);
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
			$this->out('No article was found having the ID ' . $id);
		}
		else
		{
			$obj = $result[0];
			$state = $obj->state;

			if ($state == 0)
			{
				$newState = 1;
				$message = "The article with ID " . $id . " was published.";
			}
			else
			{
				$newState = 0;
				$message = "The article with ID " . $id . " was unpublished.";
			}

			$object = new stdClass();

			$object->id = $id;
			$object->state = $newState;

			JFactory::getDbo()->updateObject('#__content', $object, 'id');

			$this->out($message);

		}
	}
}

JApplicationCli::getInstance('ToggleExtension')->execute();