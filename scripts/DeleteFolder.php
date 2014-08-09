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
class DeleteFolder extends JApplicationCli
{
	public function doExecute()
	{
		$this->out("Enter the relative path of the new folder...");
		$path = $this->in();

		if(file_exists(JPATH_BASE . "/" . $path))
		{
			jimport('joomla.filesystem.folder');
			$this->out('Creating folder...');
			$result = JFolder::delete(JPATH_BASE . "/" . $path);

			if($result)
			{
				$this->out("The folder " . JPATH_BASE . "/" . $path . " was successfully deleted.");
			}
			else
			{
				$this->out("There was some error deleting the folder.");
			}
		}
		else
		{
			$this->out("The specified folder does not exist.");
		}
	}
}

JApplicationCli::getInstance('DeleteFolder')->execute();