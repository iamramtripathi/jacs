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

/**
 * Clear cache all data
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class CreateThumbnails extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{
		// path and size are required values.
		// path is /path/to/image
		// size is size of the thumbnail a string or array: '150x75' or  array('150x75','250x150')

		// Short args
		$path = $this->input->get('p', null,'STRING');

		if (!$path)
		{
			$this->out("Enter the path of the folder (e.g /relative/path/to/folder/)...");
			$path = $this->in();
		}

		$fullPath = JPATH_BASE . $path;

		$dimensions = $this->input->get('d', null,'STRING');

		if (!$dimensions)
		{
			$this->out("Enter the size of the thumbnail (e.g 150x150)...");
			$dimensions = $this->in();
		}

		jimport('joomla.filesystem.folder');

		$files = JFolder::files($fullPath);

		foreach ($files as $file)
		{
			$explodeArray = explode('.', $file);
			$ext = end($explodeArray);

			if (in_array($ext, array('png', 'jpg', 'bmp', 'jpeg', 'gif')))
			{
				$image = new JImage($fullPath . $file);

				$image->createThumbs($dimensions, JImage::CROP_RESIZE, $fullPath);
				$this->out('Thumbnails created');
			}

		}

	}
}

JApplicationCli::getInstance('CreateThumbnails')->execute();