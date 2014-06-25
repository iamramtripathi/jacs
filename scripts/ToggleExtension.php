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

		// Get the Extension ID from argument
		$id	= $this->input->get('i', null, 'STRING');
		$db  = JFactory::getDbo();

		// Get the extensions from the database.
		$query = $db->getQuery(true)
			->select('enabled')
			->from('#__extensions')
			->where($db->quoteName('extension_id') . '= ' . $id);
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
			$this->out('No extension was found having the ID ' . $id);
		}
		else
		{
			$obj = $result[0];
			$enabled = $obj->enabled;

			if ($enabled == 0)
			{
				$newVal = 1;
				$message = "The extension with ID " . $id . " was enabled.";
			}
			else
			{
				$newVal = 0;
				$message = "The extension with ID " . $id . " was disabled.";
			}

			$object = new stdClass();

			$object->extension_id = $id;
			$object->enabled = $newVal;

			JFactory::getDbo()->updateObject('#__extensions', $object, 'extension_id');

			$this->out($message);
		}
	}
}

JApplicationCli::getInstance('ToggleExtension')->execute();