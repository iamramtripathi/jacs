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
class AddUser extends JApplicationCli
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
		// username, name, email, groups are required values.
		// password is optional
		// Groups is the array of groups

		// Long args
		$username = $this->input->get('u', null,'STRING');
		$name = $this->input->get('n', null, 'STRING');
		$email = $this->input->get('e', null, 'EMAIL');
		$password = $this->input->get('p', null, 'STRING');
		$groups = $this->input->get('o', null, 'STRING');

		// Short args
		if (!$username)
		{
			$this->out("Enter username of the user...");
			$username = $this->in();
		}
		if (!$name)
		{
			$this->out("Enter name of the user...");
			$name = $this->in();
		}
		if (!$email)
		{
			$this->out("Enter E-Mail ID of the user...");
			$email = $this->in();
		}
		if (!$password)
		{
			$this->out("Enter password of the user...");
			$password = $this->in();
		}
		if (!$groups)
		{
			$this->out("Enter the groups...");
			$groups = $this->in();
		}

		$this->createSession();

		$user = new JUser();

		$groupArray = explode(',', $groups);

		$array = array(
			'username'	=> $username,
			'name'		=> $name,
			'email'		=> $email,
			'password'	=> $password,
			'password2'	=> $password,
			'block'		=> 0,
			'groups'	=> $groupArray,
		);

		if (!$user->bind($array))
		{
			$this->out("Problem with bind");
		}
		if (!$user->save())
		{
			$this->out("Problem with save: " . $user->getError());
		}
		else
		{
			$this->out("The user with username " . $username . " was created successfully.");
			$this->out("The ID of the newly created user is " . $user->id);
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

JApplicationCli::getInstance('AddUser')->execute();