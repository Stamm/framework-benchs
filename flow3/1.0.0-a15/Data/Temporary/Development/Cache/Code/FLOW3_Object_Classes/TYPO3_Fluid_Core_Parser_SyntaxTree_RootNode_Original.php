<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
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
 * Root node of every syntax tree.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class RootNode_Original extends \TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode {

	/**
	 * Evaluate the root node, by evaluating the subtree.
	 *
	 * @param \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return mixed Evaluated subtree
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluate(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		return $this->evaluateChildNodes($renderingContext);
	}
}


#0             %CLASS%TYPO3_Fluid_Core_Parser_SyntaxTree_RootNode2159      