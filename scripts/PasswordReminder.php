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
class PasswordReminder extends JApplicationCli
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
				->select('name, email')
				->from('#__users')
				->where($db->quoteName('lastResetTime') . ' <= ' . $db->quote($date))
		)->execute();

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
			$this->out('No user found with outdated password.');
		}
		else
		{
			$mailer = JFactory::getMailer();

			$config = JFactory::getConfig();
			$sender = array(
				$config->getValue( 'config.mailfrom' ),
				$config->getValue( 'config.fromname' ) );

			$mailer->setSender($sender);

			foreach ($result as $user)
			{
				$recipientEmail = $user->email;
				$recipientName  = $user->name;
				$mailer->addRecipient($recipientEmail);

				$body   = "Dear " . $recipientName .
					"\nThis is a reminder that it has been more than " . $days .
					" since you last changed your password.\n
					In order to keep your account safe, please reset your password.\n";
				$mailer->setSubject('Outdated Password - Needs Attention');
				$mailer->setBody($body);

				$send = $mailer->Send();

				if ( $send !== true ) {
					$this->out('Error sending email: ' . $send->__toString());
				}
			}
		}

	}
}

// Global exception handler, this way we can get decent messages if need be
try
{
	JApplicationCli::getInstance('PasswordReminder')->execute();
}
catch (Exception $e)
{
	fwrite(STDOUT, "\nERROR: " . $e->getMessage() . "\n");
	fwrite(STDOUT, "\n" . $e->getTraceAsString() . "\n");

	exit($e->getCode() ? : 255);
}