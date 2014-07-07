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

// Get the legacy code
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Get the framework.
require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_installer');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/languages.php';

/**
 * Get the version number
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class InstallLanguages extends JApplicationCli
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

	public function doExecute()
	{

		$this->createSession();
		$model = JModelLegacy::getInstance('InstallerModelLanguages');

		// Get the language IDs
		$ids = $this->input->get('i', null,'STRING');

		if (!$ids)
		{
			$this->out("Enter the comma separated list of language IDs...");
			$ids = $this->in();
		}

		$idArray = explode(',', $ids);

		// Install only if all the languages exist
		foreach ($idArray as $id)
		{
			$db   = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the updates table
			$query->select('update_id')

				->from('#__updates');

			// This Where clause will avoid to list languages already installed.
			$query->where('update_id = ' . $id);
			$db->setQuery($query);

			try
			{
				$result = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$this->out($e->getMessage());
			}

			if(empty($result))
			{
				die("The language with ID " . $id . " does not exist.\n");
			}
		}

		$this->out("Installing language package...");

		// Install the languages
		$model->install($idArray);

		// Check if the languages have been installed
		foreach ($idArray as $id)
		{
			$db   = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the updates table
			$query->select('update_id')

				->from('#__updates');

			// This Where clause will avoid to list languages already installed.
			$query->where('update_id = ' . $id);
			$db->setQuery($query);

			try
			{
				$result = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$this->out($e->getMessage());
			}

			if(empty($result))
			{
				$this->out("The language with ID " . $id . " has been successfully installed.");
			}
			else
			{
				$this->out("Some error occurred while installing language with language ID " . $id);
			}
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

JApplicationCli::getInstance('InstallLanguages')->execute();