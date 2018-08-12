<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Listener;

use Joomla\Event\EventInterface;

/**
 * A listener listening to content manipulation events.
 */
class ContentListener
{
	/**
	 * Listens to the onBeforeContentSave event.
	 */
	public function onBeforeContentSave(EventInterface $event)
	{
		// Do something with the event, you might want to inspect its arguments.
	}

	/**
	 * Listens to the onAfterContentSave event.
	 */
	public function onAfterContentSave(EventInterface $event)
	{

    }

	/**
	 * Listens to the onAfterSessionStart event.
	 */
	public function onAfterSessionStart(EventInterface $event)
	{
        // just dump
    }
}