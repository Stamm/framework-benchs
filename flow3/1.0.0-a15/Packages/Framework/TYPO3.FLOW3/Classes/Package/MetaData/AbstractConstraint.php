<?php
namespace TYPO3\FLOW3\Package\MetaData;

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
 * Constraint meta data model
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractConstraint {

	/**
	 * One of depends, conflicts or suggests
	 * @var string
	 */
	protected $constraintType;

	/**
	 * The constraint name or value
	 * @var string
	 */
	protected $value;

	/**
	 * The minimum version
	 * @var string
	 */
	protected $minVersion;

	/**
	 * The maximum version
	 * @var string
	 */
	protected $maxVersion;

	/**
	 * Meta data constraint constructor
	 *
	 * @param string $constraintType
	 * @param string $value
	 * @param string $minVersion
	 * @param string $maxVersion
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function __construct($constraintType, $value, $minVersion = null, $maxVersion = null) {
		$this->constraintType = $constraintType;
		$this->value = $value;
		$this->minVersion = $minVersion;
		$this->maxVersion = $maxVersion;
	}

	/**
	 * @return string The constraint name or value
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return string The minimum version
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getMinVersion() {
		return $this->minVersion;
	}

	/**
	 * @return string The maximum version
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getMaxVersion() {
		return $this->maxVersion;
	}

	/**
	 * @return string The constraint type (depends, conflicts, suggests)
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getConstraintType() {
		return $this->constraintType;
	}

	/**
	 * @return string The constraint scope (package, system)
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	abstract public function getConstraintScope();
}
?>