<?php
namespace TYPO3\Fluid\Core\Widget;

/*
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This object stores the WidgetContext for the currently active widgets
 * of the current user, to make sure the WidgetContext is available in
 * Widget AJAX requests.
 *
 * This class is only used internally by the widget framework.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope session
 */
class AjaxWidgetContextHolder_Original {

	/**
	 * Counter which points to the next free Ajax Widget ID which
	 * can be used.
	 *
	 * @var integer
	 */
	protected $nextFreeAjaxWidgetId = 0;

	/**
	 * An array $ajaxWidgetIdentifier => $widgetContext
	 * which stores the widget context.
	 *
	 * @var array
	 */
	protected $widgetContexts = array();

	/**
	 * Get the widget context for the given $ajaxWidgetId.
	 *
	 * @param integer $ajaxWidgetId
	 * @return \TYPO3\Fluid\Core\Widget\WidgetContext
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function get($ajaxWidgetId) {
		$ajaxWidgetId = (int) $ajaxWidgetId;
		if (!isset($this->widgetContexts[$ajaxWidgetId])) {
			throw new \TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException('No widget context was found for the Ajax Widget Identifier "' . $ajaxWidgetId . '". This only happens if AJAX URIs are called without including the widget on a page.', 1284793775);
		}
		return $this->widgetContexts[$ajaxWidgetId];
	}

	/**
	 * Stores the WidgetContext inside the Context, and sets the
	 * AjaxWidgetIdentifier inside the Widget Context correctly.
	 *
	 * @param \TYPO3\Fluid\Core\Widget\WidgetContext $widgetContext
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function store(\TYPO3\Fluid\Core\Widget\WidgetContext $widgetContext) {
		$ajaxWidgetId = $this->nextFreeAjaxWidgetId++;
		$widgetContext->setAjaxWidgetIdentifier($ajaxWidgetId);
		$this->widgetContexts[$ajaxWidgetId] = $widgetContext;
	}
}


#0             %CLASS%TYPO3_Fluid_Core_Widget_AjaxWidgetContextHolder3314      