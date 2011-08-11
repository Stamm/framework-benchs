<?php
namespace TYPO3\Party\Domain\Model;

/**
 * Autogenerated Proxy Class
 * @scope prototype
 * @entity 
 */
class ElectronicAddress extends ElectronicAddress_Original implements \TYPO3\FLOW3\Object\Proxy\ProxyInterface, \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {

	/**
	 * @var string
	 * @Id
	 * @Column(length="40")
	 * introduced by TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicAspect
	 */
	protected $FLOW3_Persistence_Identifier = NULL;

	private $FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array();

	private $FLOW3_AOP_Proxy_groupedAdviceChains = array();

	private $FLOW3_AOP_Proxy_methodIsInAdviceMode = array();


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {

			$this->FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray();

			if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__construct'])) {

			} else {
				$this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__construct'] = TRUE;
				try {
				
					$methodArguments = array();

					$advices = $this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices['__construct']['TYPO3\FLOW3\AOP\Advice\BeforeAdvice'];
					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\Party\Domain\Model\ElectronicAddress', '__construct', $methodArguments);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\Party\Domain\Model\ElectronicAddress', '__construct', $methodArguments);
					$result = $this->FLOW3_AOP_Proxy_invokeJoinPoint($joinPoint);

				} catch(\Exception $e) {
					unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__construct']);
					throw $e;
				}
				unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__construct']);
				return;
			}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray() {

		$objectManager = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager;
		$this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array(
			'__construct' => array(
				'TYPO3\FLOW3\AOP\Advice\BeforeAdvice' => array(
					new \TYPO3\FLOW3\AOP\Advice\BeforeAdvice('TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicAspect', 'generateUUID', $objectManager, NULL),
				),
			),
			'__clone' => array(
				'TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice' => array(
					new \TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice('TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
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
	 */
	 public function __clone() {

		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__clone'])) {
		$result = NULL;

		} else {
			$this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__clone'] = TRUE;
			try {
			
					$methodArguments = array();

					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\Party\Domain\Model\ElectronicAddress', '__clone', $methodArguments);
					$result = $this->FLOW3_AOP_Proxy_invokeJoinPoint($joinPoint);

					$advices = $this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\FLOW3\AOP\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\Party\Domain\Model\ElectronicAddress', '__clone', $methodArguments, NULL, $result);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

			} catch(\Exception $e) {
				unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__clone']);
				throw $e;
			}
			unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['__clone']);
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
	$reflectedClass = new \ReflectionClass('TYPO3\Party\Domain\Model\ElectronicAddress');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices', 'FLOW3_AOP_Proxy_groupedAdviceChains', 'FLOW3_AOP_Proxy_methodIsInAdviceMode'))) continue;
		if ($reflectionService->isPropertyTaggedWith('TYPO3\Party\Domain\Model\ElectronicAddress', $propertyName, 'transient')) continue;
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
#0             %CLASS%TYPO3_Party_Domain_Model_ElectronicAddress %CLASS%_TYPO3_FLOW3_Persistence_Aspect_PersistenceMagicInterface8273      