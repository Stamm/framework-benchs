<?php
namespace TYPO3\FLOW3\AOP;

/**
 * Autogenerated Proxy Class
 * @scope prototype
 */
class InterfaceIntroduction extends InterfaceIntroduction_Original implements \TYPO3\FLOW3\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 * @param string $declaringAspectClassName Name of the aspect containing the declaration for this introduction
	 * @param string $interfaceName Name of the interface to introduce
	 * @param \TYPO3\FLOW3\AOP\Pointcut\Pointcut $pointcut The pointcut for this introduction
	 */
	public function __construct() {
		$arguments = func_get_args();

		if (!isset($arguments[2])) $arguments[2] = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\AOP\Pointcut\Pointcut');
		if (!isset($arguments[0])) throw new \TYPO3\FLOW3\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $declaringAspectClassName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!isset($arguments[1])) throw new \TYPO3\FLOW3\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $interfaceName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!isset($arguments[2])) throw new \TYPO3\FLOW3\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $pointcut in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		call_user_func_array('parent::__construct', $arguments);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {

	if (property_exists($this, 'FLOW3_Persistence_RelatedEntities') && is_array($this->FLOW3_Persistence_RelatedEntities)) {
		$persistenceManager = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');
		foreach ($this->FLOW3_Persistence_RelatedEntities as $entityInformation) {
			$this->$entityInformation['propertyName'] = $persistenceManager->getObjectByIdentifier($entityInformation['identifier'], $entityInformation['entityType']);
		}
		unset($this->FLOW3_Persistence_RelatedEntities);
	}
			}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$result = array();
	$reflectionService = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('TYPO3\FLOW3\AOP\InterfaceIntroduction');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices', 'FLOW3_AOP_Proxy_groupedAdviceChains', 'FLOW3_AOP_Proxy_methodIsInAdviceMode'))) continue;
		if ($reflectionService->isPropertyTaggedWith('TYPO3\FLOW3\AOP\InterfaceIntroduction', $propertyName, 'transient')) continue;
		if (is_object($this->$propertyName) && !$this->$propertyName instanceof \Doctrine\Common\Collections\Collection) {
			if ($this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($this->$propertyName);
			} else {
				$className = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($this->$propertyName));
			}
			if ($this->$propertyName instanceof \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Persistence\PersistenceManagerInterface')->isNewObject($this->$propertyName) || $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				if (!property_exists($this, 'FLOW3_Persistence_RelatedEntities') || !is_array($this->FLOW3_Persistence_RelatedEntities)) {
					$this->FLOW3_Persistence_RelatedEntities = array();
					$result[] = 'FLOW3_Persistence_RelatedEntities';
				}
				$identifier = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Persistence\PersistenceManagerInterface')->getIdentifierByObject($this->$propertyName);
				if (!$identifier && $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
					$identifier = current(\TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($this->$propertyName, '_identifier', TRUE));
				}
				$this->FLOW3_Persistence_RelatedEntities[] = array(
					'propertyName' => $propertyName,
					'entityType' => $className,
					'identifier' => $identifier
				);
				continue;
			}
			if ($className !== FALSE && \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->getScope($className) === \TYPO3\FLOW3\Object\Configuration\Configuration::SCOPE_SINGLETON) {
				continue;
			}
		}
		$result[] = $propertyName;
	}
		return $result;
	}
}
#0             %CLASS%TYPO3_FLOW3_AOP_InterfaceIntroduction5161      