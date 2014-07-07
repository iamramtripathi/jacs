<?php
/**
 * @package    Joomla.Shell
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
 * Take an application offline
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class TakeOffline extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		jimport('joomla.filesystem.file');

		if (file_exists(JPATH_BASE . '/configuration.php') || file_exists(JPATH_BASE . '/config.php'))
		{
			$configfile = file_exists(JPATH_BASE . 'configuration.php') ? JPATH_BASE . '/config.php' : JPATH_BASE . '/configuration.php';

			if (is_writable($configfile))
			{
				$config = file_get_contents($configfile);

				//Do a simple replace for the CMS and old school applications
				$newconfig = str_replace('public $offline = \'0\'', 'public $offline = \'1\'', $config);

				// Newer applications generally use JSON instead.
				if (!$newconfig)
				{
					$newconfig = str_replace('"public $offline":"0"', '"public $offline":"1"', $config);
				}
				if (!$newconfig)
				{
					$this->out('This application does not have an offline configuration setting.');
				}
				else
				{
					JFile::Write($configfile, $newconfig);
					$this->out('Site is offline');
				}
			}
			else
			{
				$this->out('The file is not writable, you need to change the file permissions first.');
				$this->out();
			}
		}
		else
		{
			$this->out('This application does not have a configuration file');
		}
		$this->out();
	}
}

JApplicationCli::getInstance('TakeOffline')->execute();
