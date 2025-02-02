<?php
namespace TYPO3\FLOW3\MVC\View;

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
 * A JSON view
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @api
 */
class JsonView_Original extends \TYPO3\FLOW3\MVC\View\AbstractView {

	/**
	 * @var \TYPO3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;

	/**
	 * Only variables whose name is contained in this array will be rendered
	 *
	 * @var array
	 */
	protected $variablesToRender = array('value');

	/**
	 * The rendering configuration for this JSON view which
	 * determines which properties of each variable to render.
	 *
	 * The configuration array must have the following structure:
	 *
	 * Example 1:
	 *
	 * array(
	 *		'variable1' => array(
	 *			'_only' => array('property1', 'property2', ...)
	 *		),
	 *		'variable2' => array(
	 *	 		'_exclude' => array('property3', 'property4, ...)
	 *		),
	 *		'variable3' => array(
	 *			'_exclude' => array('secretTitle'),
	 *			'_descend' => array(
	 *				'customer' => array(
	 *					'_only' => array('firstName', 'lastName')
	 *				)
	 *			)
	 *		),
	 *		'somearrayvalue' => array(
	 *			'_descendAll' => array(
	 *				'_only' => array('property1')
	 *			)
	 *		)
	 * )
	 *
	 * Of variable1 only property1 and property2 will be included.
	 * Of variable2 all properties except property3 and property4
	 * are used.
	 * Of variable3 all properties except secretTitle are included.
	 *
	 * If a property value is an array or object, it is not included
	 * by default. If, however, such a property is listed in a "_descend"
	 * section, the renderer will descend into this sub structure and
	 * include all its properties (of the next level).
	 *
	 * The configuration of each property in "_descend" has the same syntax
	 * like at the top level. Therefore - theoretically - infinitely nested
	 * structures can be configured.
	 *
	 * To export indexed arrays the "_descendAll" section can be used to
	 * include all array keys for the output. The configuration inside a
	 * "_descendAll" will be applied to each array element.
	 *
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * Specifies which variables this JsonView should render
	 * By default only the variable 'value' will be rendered
	 *
	 * @param array $variablesToRender
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @api
	 */
	public function setVariablesToRender(array $variablesToRender) {
		$this->variablesToRender = $variablesToRender;
	}

	/**
	 * @param array $configuration The rendering configuration for this JSON view
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setConfiguration(array $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Transforms the value view variable to a serializable
	 * array represantion using a YAML view configuration and JSON encodes
	 * the result.
	 *
	 * @return string The JSON encoded variables
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	public function render() {
		$this->controllerContext->getResponse()->setHeader('Content-Type', 'application/json');
		$propertiesToRender = $this->renderArray();
		return json_encode($propertiesToRender);
	}

	/**
	 * Loads the configuration and transforms the value to a serializable
	 * array.
	 *
	 * @return array An array containing the values, ready to be JSON encoded
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @api
	 */
	protected function renderArray() {
		if (count($this->variablesToRender) === 1) {
			$variableName = current($this->variablesToRender);
			$valueToRender = isset($this->variables[$variableName]) ? $this->variables[$variableName] : NULL;
			$configuration = isset($this->configuration[$variableName]) ? $this->configuration[$variableName] : array();
		} else {
			$valueToRender = array();
			foreach($this->variablesToRender as $variableName) {
				$valueToRender[$variableName] = isset($this->variables[$variableName]) ? $this->variables[$variableName] : NULL;
			}
			$configuration = $this->configuration;
		}
		return $this->transformValue($valueToRender, $configuration);
	}

	/**
	 * Transforms a value depending on type recursively using the
	 * supplied configuration.
	 *
	 * @param mixed $value The value to transform
	 * @param mixed $configuration Configuration for transforming the value or NULL
	 * @return array The transformed value
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function transformValue($value, $configuration) {
		if (is_array($value) || $value instanceof \ArrayAccess) {
			$array = array();
			foreach ($value as $key => $element) {
				if (isset($configuration['_descendAll']) && is_array($configuration['_descendAll'])) {
					$array[] = $this->transformValue($element, $configuration['_descendAll']);
				} else {
					if (isset($configuration['_only']) && is_array($configuration['_only']) && !in_array($key, $configuration['_only'])) continue;
					if (isset($configuration['_exclude']) && is_array($configuration['_exclude']) && in_array($key, $configuration['_exclude'])) continue;
					$array[$key] = $this->transformValue($element, isset($configuration[$key]) ? $configuration[$key] : array());
				}
			}
			return $array;
		} elseif (is_object($value)) {
			return $this->transformObject($value, $configuration);
		} else {
			return $value;
		}
	}

	/**
	 * Traverses the given object structure in order to transform it into an
	 * array structure.
	 *
	 * @param object $object Object to traverse
	 * @param mixed $configuration Configuration for transforming the given object or NULL
	 * @return array Object structure as an aray
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function transformObject($object, $configuration) {
		if ($object instanceof \DateTime) {
			return $object->format('Y-m-d\TH:i:s');
		} else {
			$propertyNames = \TYPO3\FLOW3\Reflection\ObjectAccess::getGettablePropertyNames($object);

			$propertiesToRender = array();
			foreach ($propertyNames as $propertyName) {
				if (isset($configuration['_only']) && is_array($configuration['_only']) && !in_array($propertyName, $configuration['_only'])) continue;
				if (isset($configuration['_exclude']) && is_array($configuration['_exclude']) && in_array($propertyName, $configuration['_exclude'])) continue;

				$propertyValue = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName);

				if (!is_array($propertyValue) && !is_object($propertyValue)) {
					$propertiesToRender[$propertyName] = $propertyValue;
				} elseif (isset($configuration['_descend']) && array_key_exists($propertyName, $configuration['_descend'])) {
					$propertiesToRender[$propertyName] = $this->transformValue($propertyValue, $configuration['_descend'][$propertyName]);
				}
			}
			if (isset($configuration['_exposeObjectIdentifier']) && $configuration['_exposeObjectIdentifier'] === TRUE) {
				$propertiesToRender['__identity'] = $this->persistenceManager->getIdentifierByObject($object);
			}
			return $propertiesToRender;
		}
	}
}


#0             %CLASS%TYPO3_FLOW3_MVC_View_JsonView8713      