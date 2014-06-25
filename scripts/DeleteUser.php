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
 * Put an application online
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class DeleteUser extends JApplicationCli
{
	public function __construct()
	{
		// We're a cli; we don't have a request_uri or a http_host so we have to fake it.
		$_SERVER['HTTP_HOST'] = 'domain.com';
		$_SERVER['REQUEST_URI'] = '/request/';

		// Fool the system into thinking we are running as JSite
		$this->app = JFactory::getApplication('site');

		// Call the parent __construct method so it bootstraps the application class.
		parent::__construct();
	}

	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{

		// Get the user ID
		$id = $this->input->get('i', null,'STRING');

		// Short args
		if (!$id)
		{
			$this->out("Enter ID of the user...");
			$id = $this->in();
		}

		$this->createSession();

		$user = new JUser($id);

		if(!$user->username == null)
		{
			if (!$user->delete())
			{
				$this->out("Problem with delete");
			}
			else
			{
				$this->out("User with ID " . $id . " was deleted successfully.");
			}
		}
		else
		{
			$this->out("No user with ID " . $id . " exists.");
		}

	}

	public function createSession()
	{

		// Get a valid userId
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from('#__users');
		$db->setQuery($query);

		$userId = $db->loadResult();

		$superUser = new JUser($userId);

		// Make the user a root user
		if (!$superUser->get('isRoot'))
		{
			$superUser->set('isRoot', 1);
		}

		// Replace with the admin user
		$session = JFactory::getSession();
		$session->set('user', $superUser);

	}

}

JApplicationCli::getInstance('DeleteUser')->execute();