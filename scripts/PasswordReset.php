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

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

/**
 * This script resets the password of the users who have not updated their password for x number of days
 *
 * @since  1.0
 */
class PasswordReset extends JApplicationCli
{

	public function doExecute()
	{
		// Load the database
		$db = JFactory::getDbo();

		if ($this->input->getBool('d', false))
		{
			$days = $this->input->getUint('d');
		}
		else
		{
			$this->out("Specify the number of days...");

			$days = (int) rtrim($this->in(), "\n\r");
		}

		// Get the date
		$date = JFactory::getDate()->sub(new DateInterval('P' . $days . 'D'));
		$date = JFactory::getDate((string) $date)->toSql();

		// Update the table
		$db->setQuery(
			$db->getQuery(true)
				->update($db->quoteName('#__users'))
				->set($db->quoteName('requireReset') . ' = 1')
				->where($db->quoteName('lastResetTime') . ' <= ' . $db->quote($date))
		)->execute();

		// Get the number of updated records
		$updated = $db->getAffectedRows();

		// Let the user know we're done
		$this->out(
			sprintf("Update complete, %s records updated.", $updated)
		);
	}
}

// Global exception handler, this way we can get decent messages if need be
try
{
	JApplicationCli::getInstance('PasswordReset')->execute();
}
catch (Exception $e)
{
	fwrite(STDOUT, "\nERROR: " . $e->getMessage() . "\n");
	fwrite(STDOUT, "\n" . $e->getTraceAsString() . "\n");

	exit($e->getCode() ? : 255);
}