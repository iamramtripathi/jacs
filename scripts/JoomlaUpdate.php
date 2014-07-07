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

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/default.php';

/**
 * Get the version number
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class JoomlaUpdate extends JApplicationCli
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
		$this->out('Fetching updates...');
		$updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');

		$updater->refreshUpdates();
		$updater->applyUpdateSite();

		$update = $updater->getUpdateInformation();

		if ($update['object'] == null)
		{
			die("There are no new updates available.\n");
		}

		$this->out('The present Joomla! version is ' . $update['installed']);
		$this->out('The new Joomla! version available is ' . $update['latest']);
		$this->out('Downloading Joomla! update package...');

		$baseName = $updater->download();

		if (!$baseName)
		{
			die("Some error occurred while downloading the update package.\n");
		}

		$this->app->setUserState('com_joomlaupdate.file', $baseName);

		// Create file restoration.php
		$updater->createRestorationFile($baseName);

		// Extract the update package over Joomla! installation
		if ($this->_extract())
		{
			$this->out('Joomla! update package extracted successfully. Finalising upgrade...');

			if ($updater->finaliseUpgrade())
			{
				$this->out('Joomla! has been updated to version ' . $update['latest']);
			}
			else
			{
				$this->out('Some error occurred while updating Joomla!');
			}

		}
		else
		{
			$this->out('Some error occurred while extracting Joomla! update package. Check your file permissions.');
		}

		$updater->cleanUp();

	}

	public function _extract()
	{
		$this->out('Preparing to extract update package...');

		define('KICKSTART', true);

		// Make sure Akeeba Restore is loaded
		require_once JPATH_COMPONENT_ADMINISTRATOR . '/restore.php';

		require_once JPATH_COMPONENT_ADMINISTRATOR . '/restoration.php';

		$overrides = array(
			'rename_files'	 => array('.htaccess' => 'htaccess.bak'),
			'skip_files'	 => array(),
			'reset'			 => true
		);

		// Start extraction
		$this->out('Extracting update package...');
		AKFactory::nuke();

		foreach ($restoration_setup as $key => $value)
		{
			AKFactory::set($key, $value);
		}

		AKFactory::set('kickstart.enabled', true);
		$engine	 = AKFactory::getUnarchiver($overrides);
		$engine->tick();
		$ret	 = $engine->getStatusArray();

		while ($ret['HasRun'] && !$ret['Error'])
		{
			$this->out('    Extractor tick');
			$timer	 = AKFactory::getTimer();
			$timer->resetTime();
			$engine->tick();
			$ret	 = $engine->getStatusArray();
		}

		if ($ret['Error'])
		{
			return false;
		}

		return true;
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

JApplicationCli::getInstance('JoomlaUpdate')->execute();
