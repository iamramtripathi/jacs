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
class EditUser extends JApplicationCli
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

		if (!$id)
		{
			$this->out("Enter ID of the user...");
			$id = $this->in();
		}

		// Get the edit options
		$options = $this->input->get('o', null, 'STRING');

		if (!$options)
		{
			$this->out("Enter the options as a comma separated string...");
			$options = $this->in();
		}

		$optionsArray = explode(',', $options);

		$this->createSession();

		$user = new JUser($id);

		if(!$user->username == null)
		{
			foreach ($optionsArray as $option)
			{
				switch ($option)
				{
					case 'u':
						$this->out("Enter the new Username of the user...");
						$user->username = $this->in();
						break;
					case 'p':
						$this->out("Enter the new Password of the user...");
						$user->password = md5($this->in());
						break;
					case 'n':
						$this->out("Enter the new Name of the user...");
						$user->name = $this->in();
						break;
					case 'e':
						$this->out("Enter the new E-Mail of the user...");
						$user->email = $this->in();
						break;
					case 'g':
						$this->out("Enter the new Groups of the user...");
						$groups = $this->in();
						$user->groups = explode(',', $groups);
						break;
					default:
						die("No or wrong options were provided.");
				}
			}

			if (!$user->save())
			{
				$this->out("Problem with save: " . $user->getError());
			}
			else
			{
				$this->out("The user with ID " . $id . " was updated successfully.");
				$this->out("The new user details corresponding the ID " . $user->id . " are...");
				$this->out("Username: " . $user->username);
				$this->out("Name: " . $user->name);
				$this->out("Email: " . $user->email);
				$this->out("Groups: ");
				$this->out(print_r($user->groups));
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

JApplicationCli::getInstance('EditUser')->execute();