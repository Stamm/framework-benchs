<?php
namespace TYPO3\FLOW3\AOP;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
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
 * Implementation of the interface introduction declaration.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class InterfaceIntroduction_Original {

	/**
	 * Name of the aspect declaring this introduction
	 * @var string
	 */
	protected $declaringAspectClassName;

	/**
	 * Name of the introduced interface
	 * @var string
	 */
	protected $interfaceName;

	/**
	 * The poincut this introduction applies to
	 * @var \TYPO3\FLOW3\AOP\Pointcut\Pointcut
	 */
	protected $pointcut;

	/**
	 * Constructor
	 *
	 * @param string $declaringAspectClassName Name of the aspect containing the declaration for this introduction
	 * @param string $interfaceName Name of the interface to introduce
	 * @param \TYPO3\FLOW3\AOP\Pointcut\Pointcut $pointcut The pointcut for this introduction
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($declaringAspectClassName, $interfaceName, \TYPO3\FLOW3\AOP\Pointcut\Pointcut $pointcut) {
		$this->declaringAspectClassName = $declaringAspectClassName;
		$this->interfaceName = $interfaceName;
		$this->pointcut = $pointcut;
	}

	/**
	 * Returns the name of the introduced interface
	 *
	 * @return string Name of the introduced interface
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getInterfaceName() {
		return $this->interfaceName;
	}

	/**
	 * Returns the poincut this introduction applies to
	 *
	 * @return \TYPO3\FLOW3\AOP\Pointcut\Pointcut The pointcut
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPointcut() {
		return $this->pointcut;
	}

	/**
	 * Returns the object name of the aspect which declared this introduction
	 *
	 * @return string The aspect object name
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getDeclaringAspectClassName() {
		return $this->declaringAspectClassName;
	}
}

#0             %CLASS%TYPO3_FLOW3_AOP_InterfaceIntroduction3388      