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

// Import namespaced Framework classes
use Joomla\Application\Cli\Output\Stdout;
use Joomla\Application\Cli\Output\Processor\ColorProcessor;

/**
 * Get the version number
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class VersionInfo extends JApplicationCli
{
	/**
	 * Class constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		/*
		 * From here we're going to highlight a new CMS 3.3 feature - use of the Framework's CliOutput object
		 * to display data in different colors
		 */

		// We're going to use a Stdout object for output and inject the ColorProcessor object into it to use colored output
		$output = new Stdout;
		$output->setProcessor(new ColorProcessor);

		$this->setOutput($output);
	}

	public function doExecute()
	{
		$this->out("<info>The Joomla! version installed on the specified server is: " . JVERSION . "</info>");
	}
}

JApplicationCli::getInstance('VersionInfo')->execute();