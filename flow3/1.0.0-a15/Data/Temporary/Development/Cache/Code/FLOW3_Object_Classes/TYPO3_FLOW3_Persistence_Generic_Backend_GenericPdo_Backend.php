<?php
namespace TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo;

/**
 * Autogenerated Proxy Class
 */
class Backend extends Backend_Original implements \TYPO3\FLOW3\Object\Proxy\ProxyInterface {

	private $FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array();

	private $FLOW3_AOP_Proxy_groupedAdviceChains = array();

	private $FLOW3_AOP_Proxy_methodIsInAdviceMode = array();


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {

			$this->FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray();
		parent::__construct();
		$this->FLOW3_Proxy_injectProperties();
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray() {

		$objectManager = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager;
		$this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array(
			'emitRemovedObject' => array(
				'TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice' => array(
					new \TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice('TYPO3\FLOW3\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
				),
			),
			'emitPersistedObject' => array(
				'TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice' => array(
					new \TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice('TYPO3\FLOW3\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
				),
			),
		);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		$this->FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray();

	if (property_exists($this, 'FLOW3_Persistence_RelatedEntities') && is_array($this->FLOW3_Persistence_RelatedEntities)) {
		$persistenceManager = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');
		foreach ($this->FLOW3_Persistence_RelatedEntities as $entityInformation) {
			$this->$entityInformation['propertyName'] = $persistenceManager->getObjectByIdentifier($entityInformation['identifier'], $entityInformation['entityType']);
		}
		unset($this->FLOW3_Persistence_RelatedEntities);
	}
				$this->FLOW3_Proxy_injectProperties();
		$result = NULL;
		if (is_callable('parent::__wakeup')) parent::__wakeup();
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function FLOW3_AOP_Proxy_getAdviceChains($methodName) {
		$adviceChains = NULL;
		if (is_array($this->FLOW3_AOP_Proxy_groupedAdviceChains)) {
			if (isset($this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName])) {
				$adviceChains = $this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName];
			} else {
				if (isset($this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices[$methodName])) {
					$groupedAdvices = $this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices[$methodName];
					if (isset($groupedAdvices['TYPO3\FLOW3\AOP\Advice\AroundAdvice'])) {
						$this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName]['TYPO3\FLOW3\AOP\Advice\AroundAdvice'] = new \TYPO3\FLOW3\AOP\Advice\AdviceChain($groupedAdvices['TYPO3\FLOW3\AOP\Advice\AroundAdvice'], $this);
						$adviceChains = $this->FLOW3_AOP_Proxy_groupedAdviceChains[$methodName];
					}
				}
			}
		}
		return $adviceChains;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function FLOW3_AOP_Proxy_invokeJoinPoint(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode[$joinPoint->getMethodName()])) {
			if (__CLASS__ !== $joinPoint->getClassName()) return parent::FLOW3_AOP_Proxy_invokeJoinPoint($joinPoint);
			return call_user_func_array(array('self', $joinPoint->getMethodName()), $joinPoint->getMethodArguments());
		}
	}

	/**
	 * Autogenerated Proxy Method
	 * @param object $object The object that will be removed
	 * @return void
	 * @signal
	 */
	 protected function emitRemovedObject($object) {

		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitRemovedObject'])) {
		$result = parent::emitRemovedObject($object);

		} else {
			$this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitRemovedObject'] = TRUE;
			try {
			
					$methodArguments = array();

				$methodArguments['object'] = $object;
			
					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend', 'emitRemovedObject', $methodArguments);
					$result = $this->FLOW3_AOP_Proxy_invokeJoinPoint($joinPoint);

					$advices = $this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices['emitRemovedObject']['TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend', 'emitRemovedObject', $methodArguments, NULL, $result);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

			} catch(\Exception $e) {
				unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitRemovedObject']);
				throw $e;
			}
			unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitRemovedObject']);
		}
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 * @param object $object The object that will be persisted
	 * @param integer $objectState The state, see self::OBJECTSTATE_*
	 * @return void
	 * @signal
	 */
	 protected function emitPersistedObject($object, $objectState) {

		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitPersistedObject'])) {
		$result = parent::emitPersistedObject($object, $objectState);

		} else {
			$this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitPersistedObject'] = TRUE;
			try {
			
					$methodArguments = array();

				$methodArguments['object'] = $object;
				$methodArguments['objectState'] = $objectState;
			
					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend', 'emitPersistedObject', $methodArguments);
					$result = $this->FLOW3_AOP_Proxy_invokeJoinPoint($joinPoint);

					$advices = $this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices['emitPersistedObject']['TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend', 'emitPersistedObject', $methodArguments, NULL, $result);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

			} catch(\Exception $e) {
				unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitPersistedObject']);
				throw $e;
			}
			unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['emitPersistedObject']);
		}
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$result = array();
	$reflectionService = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices', 'FLOW3_AOP_Proxy_groupedAdviceChains', 'FLOW3_AOP_Proxy_methodIsInAdviceMode'))) continue;
		if ($reflectionService->isPropertyTaggedWith('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend', $propertyName, 'transient')) continue;
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

	/**
	 * Autogenerated Proxy Method
	 */
	 private function FLOW3_Proxy_injectProperties() {
		$this->injectObjectManager(\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Object\ObjectManagerInterface'));
		$this->injectReflectionService(\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Reflection\ReflectionService'));
		$this->injectPersistenceSession(\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Persistence\Generic\Session'));
		$this->injectValidatorResolver(\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Validation\ValidatorResolver'));
		$this->injectSystemLogger(\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Log\SystemLoggerInterface'));
	}
}
#0             %CLASS%TYPO3_FLOW3_Persistence_Generic_Backend_GenericPdo_Backend %CLASS%TYPO3_FLOW3_Persistence_Generic_Backend_AbstractSqlBackend %CLASS%TYPO3_FLOW3_Persistence_Generic_Backend_AbstractBackend9747      