<?php
namespace TYPO3\FLOW3\Reflection;

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
 * A service for aquiring reflection based information in a performant way.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope singleton
 * @proxy disable
 */
class ReflectionService {

	/**
	 * @var array
	 */
	protected $availableClassNames = array();

	/**
	 * @var \TYPO3\FLOW3\Cache\Frontend\StringFrontend
	 */
	protected $statusCache;

	/**
	 * @var \TYPO3\FLOW3\Cache\Frontend\VariableFrontend
	 */
	protected $dataCache;

	/**
	 * @var \TYPO3\FLOW3\Log\SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * Settings of the FLOW3 package
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * All available class names to consider. Class name = key, value is always TRUE
	 *
	 * @var array
	 */
	protected $reflectedClassNames = array();

	/**
	 * Class names of all reflections which have been loaded from the cache.
	 *
	 * @var array
	 */
	protected $cachedClassNames = array();

	/**
	 * Names of interfaces and an array of class names implementing these
	 *
	 * @var array
	 */
	protected $interfaceImplementations = array();

	/**
	 * Names of classes and an array of interfaces implemented by them
	 *
	 * @var array
	 */
	protected $interfacesImplementedByClass = array();

	/**
	 * Names of classes which are abstract
	 *
	 * @var array
	 */
	protected $abstractClasses = array();

	/**
	 * Names of classes which are final
	 *
	 * @var array
	 */
	protected $finalClasses = array();

	/**
	 * Names of methods (in the form classname::methodname) which are final
	 *
	 * @var array
	 */
	protected $finalMethods = array();

	/**
	 * Names of methods (in the form classname::methodname) which are static
	 *
	 * @var array
	 */
	protected $staticMethods = array();

	/**
	 * Array of class names and names of their sub classes as $className => TRUE
	 *
	 * @var array
	 */
	protected $subClasses = array();

	/**
	 * Array of tags and the names of classes which are tagged with them
	 *
	 * @var array
	 */
	protected $taggedClasses = array();

	/**
	 * Array of class names and their tags and values
	 *
	 * @var array
	 */
	protected $classTagsValues = array();

	/**
	 * Array of class names, method names, their parameters and additional information about the parameters
	 *
	 * @var array
	 */
	protected $methodParameters = array();

	/**
	 * Array of class names, method names and their visibility (' ' = public, '*' = protected, '-' = private)
	 *
	 * @var array
	 */
	protected $methodVisibilities = array();

	/**
	 * Array of class names and names of their properties
	 *
	 * @var array
	 */
	protected $classPropertyNames = array();

	/**
	 * Array of class names, property names and their tags and values
	 *
	 * @var array
	 */
	protected $propertyTagsValues = array();

	/**
	 * List of tags which are ignored while reflecting class and method annotations
	 *
	 * @var array
	 */
	protected $ignoredTags = array('api', 'package', 'subpackage', 'license', 'copyright', 'author', 'version', 'const', 'see', 'todo', 'throws');

	/**
	 * Schemata of all classes which need to be persisted
	 *
	 * @var array<\TYPO3\FLOW3\Reflection\ClassSchema>
	 */
	protected $classSchemata = array();

	/**
	 * An array of class names which are currently being forgotten by forgetClass(). Acts as a safeguard against infinite loops.
	 *
	 * @var array
	 */
	protected $classesCurrentlyBeingForgotten = array();

	/**
	 * Sets the status cache
	 *
	 * The cache must be set before initializing the Reflection Service
	 *
	 * @param \TYPO3\FLOW3\Cache\Frontend\StringFrontend $cache Cache for the reflection service
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setStatusCache(\TYPO3\FLOW3\Cache\Frontend\StringFrontend $cache) {
		$this->statusCache = $cache;
	}

	/**
	 * Sets the data cache
	 *
	 * The cache must be set before initializing the Reflection Service
	 *
	 * @param \TYPO3\FLOW3\Cache\Frontend\VariableFrontend $cache Cache for the reflection service
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setDataCache(\TYPO3\FLOW3\Cache\Frontend\VariableFrontend $cache) {
		$this->dataCache = $cache;
	}

	/**
	 * @param array $settings Settings of the FLOW3 package
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param \TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectSystemLogger(\TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger) {
		$this->systemLogger = $systemLogger;
	}

	/**
	 * Initializes this service after dependencies have been injected.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function initializeObject() {
		$this->loadFromCache();
	}

	/**
	 * Builds the reflection data cache during compile time.
	 *
	 * @param array $availableClassNames
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function buildReflectionData(array $availableClassNames) {
		$this->availableClassNames = $availableClassNames;

		$this->forgetChangedClasses();
		$this->reflectEmergedClasses();
	}

	/**
	 * Returns an array with annotations that are ignored while reflecting class
	 * and method annotations.
	 *
	 * @return array
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIgnoredTags() {
		return $this->ignoredTags;
	}

	/**
	 * Tells if the specified class is known to this reflection service and
	 * reflection information is available.
	 *
	 * @param string $className Name of the class
	 * @return boolean If the class is reflected by this service
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isClassReflected($className) {
		return isset($this->reflectedClassNames[trim($className, '\\')]);
	}

	/**
	 * Returns the names of all classes known to this reflection service.
	 *
	 * @return array Class names
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getAllClassNames() {
		return array_keys($this->reflectedClassNames);
	}

	/**
	 * Searches for and returns the class name of the default implementation of the given
	 * interface name. If no class implementing the interface was found or more than one
	 * implementation was found in the package defining the interface, FALSE is returned.
	 *
	 * @param string $interfaceName Name of the interface
	 * @return mixed Either the class name of the default implementation for the object type or FALSE
	 * @throws \InvalidArgumentException if the given interface does not exist
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getDefaultImplementationClassNameForInterface($interfaceName) {
		$interfaceName = trim($interfaceName, '\\');
		if (interface_exists($interfaceName) === FALSE) throw new \InvalidArgumentException('"' . $interfaceName . '" does not exist or is not the name of an interface.', 1238769559);

		$classNamesFound = isset($this->interfaceImplementations[$interfaceName]) ? $this->interfaceImplementations[$interfaceName] : array();
		if (count($classNamesFound) === 1) return current($classNamesFound);
		if (count($classNamesFound) === 2 && isset($this->interfaceImplementations['TYPO3\FLOW3\Object\Proxy\ProxyInterface'])) {
			if (array_search(current($classNamesFound), $this->interfaceImplementations['TYPO3\FLOW3\Object\Proxy\ProxyInterface']) !== FALSE) return current($classNamesFound);
			next($classNamesFound);
			if (array_search(current($classNamesFound), $this->interfaceImplementations['TYPO3\FLOW3\Object\Proxy\ProxyInterface']) !== FALSE) return current($classNamesFound);
		}
		return FALSE;
	}

	/**
	 * Searches for and returns all class names of implementations of the given object type
	 * (interface name). If no class implementing the interface was found, an empty array is returned.
	 *
	 * @param string $interfaceName Name of the interface
	 * @return array An array of class names of the default implementation for the object type
	 * @author Robert Lemke <robert@typo3.org>
	 * @throws \InvalidArgumentException if the given interface does not exist
	 * @api
	 */
	public function getAllImplementationClassNamesForInterface($interfaceName) {
		$interfaceName = trim($interfaceName, '\\');
		if (interface_exists($interfaceName) === FALSE) throw new \InvalidArgumentException('"' . $interfaceName . '" does not exist or is not the name of an interface.', 1238769560);
		return (isset($this->interfaceImplementations[$interfaceName])) ? $this->interfaceImplementations[$interfaceName] : array();
	}

	/**
	 * Returns the names of all interfaces implemented by the specified class
	 *
	 * @param string $className Name of the class
	 * @return array An array of interface names
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @deprecated since 1.0.0beta1 – use the PHP function class_implements() instead
	 */
	public function getInterfaceNamesImplementedByClass($className) {
		$className = trim($className, '\\');
		if (!isset($this->interfacesImplementedByClass[$className])) {
			$class = new \TYPO3\FLOW3\Reflection\ClassReflection($className);
			$this->interfacesImplementedByClass[$className] = $class->getInterfaceNames();
		}
		return $this->interfacesImplementedByClass[$className];
	}

	/**
	 * Searches for and returns all names of classes inheriting the specified class.
	 * If no class inheriting the given class was found, an empty array is returned.
	 *
	 * @param string $className Name of the parent class
	 * @return array An array of names of those classes being a direct or indirect subclass of the specified class
	 * @author Robert Lemke <robert@typo3.org>
	 * @throws \InvalidArgumentException if the given class does not exist
	 * @api
	 */
	public function getAllSubClassNamesForClass($className) {
		$className = trim($className, '\\');
		if (class_exists($className) === FALSE) throw new \InvalidArgumentException('"' . $className . '" does not exist or is not the name of a class.', 1257168042);
		return (isset($this->subClasses[$className])) ? array_keys($this->subClasses[$className]) : array();
	}

	/**
	 * Searches for and returns all names of classes which are tagged by the specified tag.
	 * If no classes were found, an empty array is returned.
	 *
	 * @param string $tag Tag to search for
	 * @return array An array of class names tagged by the tag
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getClassNamesByTag($tag) {
		return (isset($this->taggedClasses[$tag])) ? $this->taggedClasses[$tag] : array();
	}

	/**
	 * Returns all tags and their values the specified class is tagged with
	 *
	 * @param string $className The class name to reflect
	 * @return array An array of tags and their values or an empty array if no tags were found
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getClassTagsValues($className) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return (isset($this->classTagsValues[$className])) ? $this->classTagsValues[$className] : array();
	}

	/**
	 * Returns values of the specified class tag.
	 *
	 * @param string $className The class name to reflect
	 * @param string $tag The tag to return values of
	 * @return array An array of values or an empty array of the class is not tagged with the tag or the tag has no values
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getClassTagValues($className, $tag) {
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->classTagsValues[$className])) return array();
		return (isset($this->classTagsValues[$className][$tag])) ? $this->classTagsValues[$className][$tag] : array();
	}

	/**
	 * Tells if the specified class is tagged with the given tag
	 *
	 * @param string $className Name of the class
	 * @param string $tag Tag to check for
	 * @return boolean TRUE if the class is tagged with $tag, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isClassTaggedWith($className, $tag) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->classTagsValues[$className])) return FALSE;
		return isset($this->classTagsValues[$className][$tag]);
	}

	/**
	 * Tells if the specified class implements the given interface
	 *
	 * @param string $className Name of the class
	 * @param string $interfaceName interface to check for
	 * @return boolean TRUE if the class implements $interfaceName, otherwise FALSE
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function isClassImplementationOf($className, $interfaceName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->interfaceImplementations[$interfaceName])) return FALSE;
		return (array_search($className, $this->interfaceImplementations[$interfaceName]) !== FALSE);
	}

	/**
	 * Tells if the specified class is abstract or not
	 *
	 * @param string $className Name of the class to analyze
	 * @return boolean TRUE if the class is abstract, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isClassAbstract($className) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return isset($this->abstractClasses[$className]);
	}

	/**
	 * Tells if the specified class is final or not
	 *
	 * @param string $className Name of the class to analyze
	 * @return boolean TRUE if the class is final, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isClassFinal($className) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return isset($this->finalClasses[$className]);
	}

	/**
	 * Tells if the specified method is final or not
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to analyze
	 * @return boolean TRUE if the method is final, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isMethodFinal($className, $methodName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return isset($this->finalMethods[$className . '::' . $methodName]);
	}

	/**
	 * Tells if the specified method is declared as static or not
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to analyze
	 * @return boolean TRUE if the method is static, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isMethodStatic($className, $methodName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return isset($this->staticMethods[$className . '::' . $methodName]);
	}

	/**
	 * Tells if the specified method is public
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to analyze
	 * @return boolean TRUE if the method is public, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isMethodPublic($className, $methodName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return (isset($this->methodVisibilities[$className][$methodName]) && $this->methodVisibilities[$className][$methodName] === ' ');
	}

	/**
	 * Tells if the specified method is protected
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to analyze
	 * @return boolean TRUE if the method is protected, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isMethodProtected($className, $methodName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return (isset($this->methodVisibilities[$className][$methodName]) && $this->methodVisibilities[$className][$methodName] === '*');
	}

	/**
	 * Tells if the specified method is private
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to analyze
	 * @return boolean TRUE if the method is private, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isMethodPrivate($className, $methodName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return (isset($this->methodVisibilities[$className][$methodName]) && $this->methodVisibilities[$className][$methodName] === '-');
	}

	/**
	 * Tells if the specified method is tagged with the given tag
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to analyze
	 * @param string $tag Tag to check for
	 * @return boolean TRUE if the method is tagged with $tag, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isMethodTaggedWith($className, $methodName, $tag) {
		$method = new \TYPO3\FLOW3\Reflection\MethodReflection(trim($className, '\\'), $methodName);
		$tagsValues = $method->getTagsValues();
		return isset($tagsValues[$tag]);
	}

	/**
	 * Returns the names of all properties of the specified class
	 *
	 * @param string $className Name of the class to return the property names of
	 * @return array An array of property names or an empty array if none exist
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getClassPropertyNames($className) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		return (isset($this->classPropertyNames[$className])) ? $this->classPropertyNames[$className] : array();
	}

	/**
	 * Wrapper for method_exists() which tells if the given method exists.
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method
	 * @return boolean
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function hasMethod($className, $methodName) {
		return isset($this->methodVisibilities[$className][$methodName]);
	}

	/**
	 * Returns all tags and their values the specified method is tagged with
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to return the tags and values of
	 * @return array An array of tags and their values or an empty array of no tags were found
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getMethodTagsValues($className, $methodName) {
		$method = new \TYPO3\FLOW3\Reflection\MethodReflection(trim($className, '\\'), $methodName);
		return $method->getTagsValues();
	}

	/**
	 * Returns an array of parameters of the given method. Each entry contains
	 * additional information about the parameter position, type hint etc.
	 *
	 * @param string $className Name of the class containing the method
	 * @param string $methodName Name of the method to return parameter information of
	 * @return array An array of parameter names and additional information or an empty array of no parameters were found
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getMethodParameters($className, $methodName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) {
			$this->reflectClass($className);
		}
		if (isset($this->reflectedClassNames[$className])) {
			if (!isset($this->methodParameters[$className])) {
				return array();
			}
			$parametersInformation = (isset($this->methodParameters[$className][$methodName])) ? $this->methodParameters[$className][$methodName] : array();
		} else {
			$method = new \TYPO3\FLOW3\Reflection\MethodReflection($className, $methodName);
			$parametersInformation = array();
			foreach($method->getParameters() as $parameter) {
				$parametersInformation[$parameter->getName()] = $this->convertParameterReflectionToArray($parameter, $method);
			}
		}
		return $parametersInformation;
	}

	/**
	 * Searches for and returns all names of class properties which are tagged by the specified tag.
	 * If no properties were found, an empty array is returned.
	 *
	 * @param string $className Name of the class containing the properties
	 * @param string $tag Tag to search for
	 * @return array An array of property names tagged by the tag
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getPropertyNamesByTag($className, $tag) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->propertyTagsValues[$className])) return array();
		$propertyNames = array();
		foreach ($this->propertyTagsValues[$className] as $propertyName => $tagsValues) {
			if (isset($tagsValues[$tag])) $propertyNames[$propertyName] = TRUE;
		}
		return array_keys($propertyNames);
	}

	/**
	 * Returns all tags and their values the specified class property is tagged with
	 *
	 * @param string $className Name of the class containing the property
	 * @param string $propertyName Name of the property to return the tags and values of
	 * @return array An array of tags and their values or an empty array of no tags were found
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getPropertyTagsValues($className, $propertyName) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->propertyTagsValues[$className])) return array();
		return (isset($this->propertyTagsValues[$className][$propertyName])) ? $this->propertyTagsValues[$className][$propertyName] : array();
	}

	/**
	 * Returns the values of the specified class property tag
	 *
	 * @param string $className Name of the class containing the property
	 * @param string $propertyName Name of the tagged property
	 * @param string $tag Tag to return the values of
	 * @return array An array of values or an empty array if the tag was not found
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function getPropertyTagValues($className, $propertyName, $tag) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->propertyTagsValues[$className])) return array();
		if (!isset($this->propertyTagsValues[$className][$propertyName])) return array();
		return (isset($this->propertyTagsValues[$className][$propertyName][$tag])) ? $this->propertyTagsValues[$className][$propertyName][$tag] : array();
	}

	/**
	 * Tells if the specified class property is tagged with the given tag
	 *
	 * @param string $className Name of the class
	 * @param string $propertyName Name of the property
	 * @param string $tag Tag to check for
	 * @return boolean TRUE if the class property is tagged with $tag, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function isPropertyTaggedWith($className, $propertyName, $tag) {
		$className = trim($className, '\\');
		if (!isset($this->reflectedClassNames[$className])) $this->reflectClass($className);
		if (!isset($this->propertyTagsValues[$className])) return FALSE;
		if (!isset($this->propertyTagsValues[$className][$propertyName])) return FALSE;
		return isset($this->propertyTagsValues[$className][$propertyName][$tag]);
	}

	/**
	 * Returns the available class schemata.
	 *
	 * @return array<\TYPO3\FLOW3\Reflection\ClassSchema>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getClassSchemata() {
		return $this->classSchemata;
	}

	/**
	 * Returns the class schema for the given class
	 *
	 * @param mixed $classNameOrObject The class name or an object
	 * @return \TYPO3\FLOW3\Reflection\ClassSchema
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getClassSchema($classNameOrObject) {
		$className = trim(is_object($classNameOrObject) ? get_class($classNameOrObject) : $classNameOrObject, '\\');
		return isset($this->classSchemata[$className]) ? $this->classSchemata[$className] : NULL;
	}

	/**
	 * Checks if the given class names match those which already have been
	 * reflected. If the given array contains class names not yet known to
	 * this service, these classes will be reflected.
	 *
	 * @param array $classNamesToReflect Names of all classes which should be known by this service. Class names = values of the array
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function reflectEmergedClasses() {
		$classNamesToReflect = array();
		foreach ($this->availableClassNames as $classNamesInOnePackage) {
			$classNamesToReflect = array_merge($classNamesToReflect, $classNamesInOnePackage);
		}
		$reflectedClassNames = array_keys($this->reflectedClassNames);
		sort($classNamesToReflect);
		sort($reflectedClassNames);
		if ($this->reflectedClassNames != $classNamesToReflect) {
			$classNamesToBuildSchemaFor = array();
			$count = 0;
			foreach (array_diff($classNamesToReflect, $reflectedClassNames) as $className) {
				$count ++;
				$this->reflectClass($className);
				if ($this->isClassTaggedWith($className, 'entity') || $this->isClassTaggedWith($className, 'valueobject')) {
					$classTagValues = $this->getClassTagValues($className, 'scope');
					if ($classTagValues !== array() && current($classTagValues) !== 'prototype') {
						throw new \TYPO3\FLOW3\Reflection\Exception(sprintf('Classes tagged as @entity or @valueobject must be of @scope prototype, however, %s is declared as %s.', $className, current($classTagValues)), 1264103349);
					}
					$classNamesToBuildSchemaFor[] = $className;
				}
			}

			$this->buildClassSchemata($classNamesToBuildSchemaFor);
			if ($count > 0) {
				$this->log(sprintf('Reflected %s emerged classes.', $count), LOG_INFO);
			}
		}
	}

	/**
	 * Reflects the given class and stores the results in this service's properties.
	 *
	 * @param string $className Full qualified name of the class to reflect
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function reflectClass($className) {
		$this->log(sprintf('Reflecting class %s', $className), LOG_DEBUG);

		$className = trim($className, '\\');

		$class = new ClassReflection($className);

		$this->reflectedClassNames[$className] = $_SERVER['REQUEST_TIME'];

		if ($class->isAbstract()) $this->abstractClasses[$className] = TRUE;
		if ($class->isFinal()) $this->finalClasses[$className] = TRUE;

		foreach($this->getParentClasses($class) as $parentClass) {
			$this->subClasses[$parentClass->getName()][$className] = TRUE;
		}

		foreach ($class->getInterfaces() as $interface) {
			if (!isset($this->abstractClasses[$className])) {
				$this->interfaceImplementations[$interface->getName()][] = $className;
			}
		}
		foreach ($class->getTagsValues() as $tag => $values) {
			if (array_search($tag, $this->ignoredTags) === FALSE) {
				$this->taggedClasses[$tag][] = $className;
				$this->classTagsValues[$className][$tag] = $values;
			}
		}

		foreach ($class->getProperties() as $property) {
			$propertyName = $property->getName();
			$this->classPropertyNames[$className][] = $propertyName;

			foreach ($property->getTagsValues() as $tag => $values) {
				if (array_search($tag, $this->ignoredTags) === FALSE) {
					$this->propertyTagsValues[$className][$propertyName][$tag] = $values;
				}
			}
		}

		foreach ($class->getMethods() as $method) {
			$methodName = $method->getName();
			if ($method->isFinal()) $this->finalMethods[$className . '::' . $methodName] = TRUE;
			if ($method->isStatic()) $this->staticMethods[$className . '::' . $methodName] = TRUE;
			if ($method->isPublic()) $this->methodVisibilities[$className][$methodName] = ' ';
			if ($method->isProtected()) $this->methodVisibilities[$className][$methodName] = '*';
			if ($method->isPrivate()) $this->methodVisibilities[$className][$methodName] = '-';

			$paramAnnotations = $method->isTaggedWith('param') ? $method->getTagValues('param') : array();
			foreach ($method->getParameters() as $parameter) {
				$this->methodParameters[$className][$methodName][$parameter->getName()] = $this->convertParameterReflectionToArray($parameter, $method);
				if ($this->settings['reflection']['logIncorrectDocCommentHints'] === TRUE) {
					if (isset($paramAnnotations[$parameter->getPosition()])) {
						$parameterAnnotation = explode(' ', $paramAnnotations[$parameter->getPosition()], 3);
						if (count($parameterAnnotation) < 2) {
							$this->log('  Wrong @param use for "' . $method->getName() . '::' . $parameter->getName() . '": "' . implode(' ', $parameterAnnotation) . '"', LOG_DEBUG);
						} else {
							if (isset($this->methodParameters[$className][$methodName][$parameter->getName()]['type']) && ($this->methodParameters[$className][$methodName][$parameter->getName()]['type'] !== ltrim($parameterAnnotation[0], '\\'))) {
								$this->log('  Wrong type in @param for "' . $method->getName() . '::' . $parameter->getName() . '": "' . $parameterAnnotation[0] . '"', LOG_DEBUG);
							}
							if ($parameter->getName() !== ltrim($parameterAnnotation[1], '$&')) {
								$this->log('  Wrong name in @param for "' . $method->getName() . '::$' . $parameter->getName() . '": "' . $parameterAnnotation[1] . '"', LOG_DEBUG);
							}
						}
					} else {
						$this->log('  Missing @param for "' . $method->getName() . '::$' . $parameter->getName(), LOG_DEBUG);
					}
				}
			}
		}
		ksort($this->reflectedClassNames);
	}

	/**
	 * Finds all parent classes of the given class
	 *
	 * @param \ReflectionClass $class The class to reflect
	 * @param array $parentClasses Array of parent classes
	 * @return array<\TYPO3\FLOW3\Reflection\ClassReflection>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getParentClasses(\TYPO3\FLOW3\Reflection\ClassReflection $class, array $parentClasses = array()) {
		$parentClass = $class->getParentClass();
		if ($parentClass !== FALSE) {
			$parentClasses[] = $parentClass;
			$parentClasses = $this->getParentClasses($parentClass, $parentClasses);
		}
		return $parentClasses;
	}

	/**
	 * Builds class schemata from classes annotated as entities or value objects
	 *
	 * @param array $classNames
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function buildClassSchemata(array $classNames) {
		foreach ($classNames as $className) {
			$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($className);
			if ($this->isClassTaggedWith($className, 'entity')) {
				$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
				$classSchema->setLazyLoadableObject($this->isClassTaggedWith($className, 'lazy'));

				$possibleRepositoryClassName = str_replace('\\Model\\', '\\Repository\\', $className) . 'Repository';
				if ($this->isClassReflected($possibleRepositoryClassName) === TRUE) {
					$classSchema->setRepositoryClassName($possibleRepositoryClassName);
				}
			} elseif ($this->isClassTaggedWith($className, 'valueobject')) {
				$this->checkValueObjectRequirements($className);
				$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);
			}

				// those are added as property even if not tagged with entity/valueobject
			$propertyTypeWhiteList = array('DateTime', 'SplObjectStorage', 'Doctrine\Common\Collections\ArrayCollection');
			$needsArtificialIdentity = TRUE;
			foreach ($this->getClassPropertyNames($className) as $propertyName) {
				if ($this->isPropertyTaggedWith($className, $propertyName, 'var') && !$this->isPropertyTaggedWith($className, $propertyName, 'transient')) {
					$declaredType = trim(implode(' ', $this->getPropertyTagValues($className, $propertyName, 'var')), ' \\');
					if (preg_match('/\s/', $declaredType) === 1) {
						throw new \TYPO3\FLOW3\Reflection\Exception\InvalidPropertyTypeException('The @var annotation for "' . $className . '::$' . $propertyName . '" seems to be invalid.', 1284132314);
					}
					if ($this->isPropertyTaggedWith($className, $propertyName, 'Id')) {
						$needsArtificialIdentity = FALSE;
					}

					$parsedType = \TYPO3\FLOW3\Utility\TypeHandling::parseType($declaredType);
					if (!in_array($parsedType['type'], $propertyTypeWhiteList)
							&& (class_exists($parsedType['type']) || interface_exists($parsedType['type']))
							&& !($this->isClassTaggedWith($parsedType['type'], 'entity') || $this->isClassTaggedWith($parsedType['type'], 'valueobject'))) {
						continue;
					}

					$classSchema->addProperty($propertyName, $declaredType, $this->isPropertyTaggedWith($className, $propertyName, 'lazy'));
					if ($this->isPropertyTaggedWith($className, $propertyName, 'uuid')) {
						$classSchema->setUuidPropertyName($propertyName);
					}
					if ($this->isPropertyTaggedWith($className, $propertyName, 'identity')) {
						$classSchema->markAsIdentityProperty($propertyName);
					}
				}
			}
			if ($needsArtificialIdentity === TRUE) {
				$classSchema->addProperty('FLOW3_Persistence_Identifier', 'string');
			}
			$this->classSchemata[$className] = $classSchema;
		}

			// now look for repositories that declare themselves responsible for a specific model
		foreach ($this->getAllImplementationClassNamesForInterface('TYPO3\FLOW3\Persistence\RepositoryInterface') as $repositoryClassname) {
				// need to be extra careful because this code could be called during a cache:flush run with corrupted reflection cache
			if (class_exists($repositoryClassname)) {
				$claimedObjectType = $repositoryClassname::ENTITY_CLASSNAME;
				if ($claimedObjectType !== NULL && isset($this->classSchemata[$claimedObjectType])) {
					$this->classSchemata[$claimedObjectType]->setRepositoryClassName($repositoryClassname);
				}
			}
		}
	}

	/**
	 * Checks if the given class meets the requirements for a value object, i.e.
	 * does have a constructor and does not have any setter methods.
	 *
	 * @param string $className
	 * @return void
	 * @throws \TYPO3\FLOW3\Reflection\Exception\InvalidValueObjectException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function checkValueObjectRequirements($className) {
		$methods = get_class_methods($className);
		if (array_search('__construct', $methods) === FALSE) {
			throw new \TYPO3\FLOW3\Reflection\Exception\InvalidValueObjectException('A value object must have a constructor, "' . $className . '" does not have one.', 1268740874);
		}
		foreach ($methods as $method) {
			if (substr($method, 0, 3) === 'set') {
				throw new \TYPO3\FLOW3\Reflection\Exception\InvalidValueObjectException('A value object must not have setters, "' . $className . '" does.', 1268740878);
			}
		}
	}

	/**
	 * Converts the given parameter reflection into an information array
	 *
	 * @param \TYPO3\FLOW3\Reflection\ParameterReflection $parameter The parameter to reflect
	 * @param \TYPO3\FLOW3\Reflection\MethodReflection $method The parameter's method
	 * @return array Parameter information array
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function convertParameterReflectionToArray(\TYPO3\FLOW3\Reflection\ParameterReflection $parameter, \TYPO3\FLOW3\Reflection\MethodReflection $method = NULL) {
		$parameterInformation = array(
			'position' => $parameter->getPosition(),
			'byReference' => $parameter->isPassedByReference() ? TRUE : FALSE,
			'array' => $parameter->isArray() ? TRUE : FALSE,
			'optional' => $parameter->isOptional() ? TRUE : FALSE,
			'allowsNull' => $parameter->allowsNull() ? TRUE : FALSE
		);

		$parameterClass = $parameter->getClass();
		$parameterInformation['class'] = ($parameterClass !== NULL) ? $parameterClass->getName() : NULL;
		if ($parameter->isDefaultValueAvailable()) {
			$parameterInformation['defaultValue'] = $parameter->getDefaultValue();
		}
		if ($method !== NULL) {
			$paramAnnotations = $method->isTaggedWith('param') ? $method->getTagValues('param') : array();
			if (isset($paramAnnotations[$parameter->getPosition()])) {
				$explodedParameters = explode(' ', $paramAnnotations[$parameter->getPosition()]);
				if (count($explodedParameters) >= 2) {
					$parameterInformation['type'] = ltrim($explodedParameters[0], '\\');
				}
			}
		}
		if (!isset($parameterInformation['type']) && $parameterClass !== NULL) {
			$parameterInformation['type'] = ltrim($parameterClass->getName(), '\\');
		}
		return $parameterInformation;
	}

	/**
	 * Checks which classes lack a cache entry and removes their reflection data
	 * accordingly.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function forgetChangedClasses() {
		foreach (array_keys($this->reflectedClassNames) as $className) {
			if (!$this->statusCache->has(str_replace('\\', '_', $className))) {
				$this->forgetClass($className);
			}
		}
	}

	/**
	 * Forgets all reflection data related to the specified class
	 *
	 * @param string $className Name of the class to forget
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function forgetClass($className) {
		if (isset($this->classesCurrentlyBeingForgotten[$className])) {
			$this->systemLogger->log('Detected recursion while forgetting class ' . $className, LOG_WARNING);
			return;
		}
		$this->classesCurrentlyBeingForgotten[$className] = TRUE;

		foreach ($this->interfaceImplementations as $interfaceName => $interfaceImplementations) {
			$index = array_search($className, $interfaceImplementations);
			if ($index !== FALSE) unset($this->interfaceImplementations[$interfaceName][$index]);
		}
		if (isset($this->subClasses[$className])) {
			foreach (array_keys($this->subClasses[$className]) as $subClassName) {
				$this->forgetClass($subClassName);
			}
		}

		foreach ($this->taggedClasses as $tag => $classNames) {
			$index = array_search($className, $classNames);
			if ($index !== FALSE) unset($this->taggedClasses[$tag][$index]);
		}

		$propertyNames = array(
			'abstractClasses',
			'classPropertyNames',
			'classTagsValues',
			'subClasses',
			'finalClasses',
			'finalMethods',
			'staticMethods',
			'methodParameters',
			'methodVisibilities',
			'propertyTagsValues',
		);
		foreach ($propertyNames as $propertyName) {
			if (isset($this->{$propertyName}[$className])) {
				unset($this->{$propertyName}[$className]);
			}
		}
		unset($this->reflectedClassNames[$className]);
		unset($this->classesCurrentlyBeingForgotten[$className]);
	}

	/**
	 * Tries to load the reflection data from this service's cache.
	 *
	 * @return boolean TRUE if reflection data could be loaded, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function loadFromCache() {
		$data = $this->dataCache->get('ReflectionData');
		if ($data === FALSE) {
			return FALSE;
		}

		foreach ($data as $propertyName => $propertyValue) {
			$this->$propertyName = $propertyValue;
		}
		$this->cachedClassNames = $this->reflectedClassNames;
		return TRUE;
	}

	/**
	 * Exports the internal reflection data into the ReflectionData cache
	 *
	 * @return void
	 * @throws \TYPO3\FLOW3\Reflection\Exception if no cache has been injected
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function saveToCache() {
		if (!is_object($this->dataCache)) throw new \TYPO3\FLOW3\Reflection\Exception('A cache must be injected before initializing the Reflection Service.', 1232044697);

		$nonCachedClassNames = array_diff_assoc($this->reflectedClassNames, $this->cachedClassNames);
		$emergedClassesCount = count($nonCachedClassNames);
		if ($emergedClassesCount > 0) {
			$this->log(sprintf('Found %s classes whose reflection data was not cached previously.', $emergedClassesCount), LOG_DEBUG);

			foreach (array_keys($nonCachedClassNames) as $className) {
				$this->statusCache->set(str_replace('\\', '_', $className), '', array(\TYPO3\FLOW3\Cache\CacheManager::getClassTag($className)));
			}

			$data = array();
			$propertyNames = array(
				'reflectedClassNames',
				'abstractClasses',
				'classPropertyNames',
				'classSchemata',
				'classTagsValues',
				'subClasses',
				'finalClasses',
				'finalMethods',
				'staticMethods',
				'interfaceImplementations',
				'methodParameters',
				'methodVisibilities',
				'propertyTagsValues',
				'taggedClasses'
			);
			foreach ($propertyNames as $propertyName) {
				$data[$propertyName] = $this->$propertyName;
			}
			$this->dataCache->set('ReflectionData', $data);
			$this->cachedClassNames = $this->reflectedClassNames;
		}
	}

	/**
	 * Writes the given message along with the additional information into the log.
	 *
	 * @param string $message The message to log
	 * @param integer $severity An integer value, one of the SEVERITY_* constants
	 * @param mixed $additionalData A variable containing more information about the event to be logged
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function log($message, $severity = 6, $additionalData = NULL) {
		if (is_object($this->systemLogger)) {
			$this->systemLogger->log($message, $severity, $additionalData);
		}
	}
}
?>
