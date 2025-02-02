<?php
namespace TYPO3\FLOW3\Tests\Functional\Object\Fixtures;

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
 * A factory which creates PrototypeClassA instances
 */
class PrototypeClassAFactory {

	/**
	 * Creates a new instance of PrototypeClassA
	 *
	 * @param string $someProperty
	 * @return \TYPO3\FLOW3\Tests\Functional\Object\Fixtures\FLOWPrototypeClassA
	 */
	public function create($someProperty) {
		$object = new PrototypeClassA();
		$object->setSomeProperty($someProperty);
		return $object;
	}

}
?>