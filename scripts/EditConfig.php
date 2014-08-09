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
class EditConfig extends JApplicationCli
{
	public function doExecute()
	{
		global $argv;

		jimport('joomla.filesystem.file');

		if (file_exists(JPATH_BASE . '/configuration.php') || file_exists(JPATH_BASE . '/config.php'))
		{
			$configFile = file_exists(JPATH_BASE . 'configuration.php') ? JPATH_BASE . '/config.php' : JPATH_BASE . '/configuration.php';

			if (is_writable($configFile))
			{
				for ($i=1; $i < count($argv); $i = $i + 2)
				{
					$config = JFactory::getConfig();

					$parameter = str_replace('--', null, $argv[$i]);
					$oldString = $config->get($parameter);
					$newString = $argv[$i + 1];

					$config = file_get_contents($configFile);

					$result = strstr($config, 'public $' . $parameter . ' = \'' . $oldString . '\'');

					if ($result)
					{
						//Do a simple replace for the CMS and old school applications
						$newConfig = str_replace('public $' . $parameter . ' = \'' . $oldString . '\'', 'public $' . $parameter . ' = \'' . $newString . '\'', $config);

						if (!$newConfig)
						{
							$this->out('There was some error while updating the parameter ' . $parameter);
						}
						else
						{
							JFile::Write($configFile, $newConfig);
							$this->out('The configuration \'' . $parameter . '\' has been updated.');
						}
					}
					else
					{
						$this->out('This application does not have \'' . $parameter . '\' as configuration setting.');
					}
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

JApplicationCli::getInstance('EditConfig')->execute();