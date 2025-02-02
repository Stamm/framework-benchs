<?php
namespace TYPO3\FLOW3\MVC\Web\Routing;

/**
 * Autogenerated Proxy Class
 * @scope singleton
 */
class Router extends Router_Original implements \TYPO3\FLOW3\Object\Proxy\ProxyInterface {

	private $FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array();

	private $FLOW3_AOP_Proxy_groupedAdviceChains = array();

	private $FLOW3_AOP_Proxy_methodIsInAdviceMode = array();


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {

			$this->FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray();
		\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\FLOW3\MVC\Web\Routing\Router', $this);
		\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\FLOW3\MVC\Web\Routing\RouterInterface', $this);
		$this->FLOW3_Proxy_injectProperties();
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray() {

		$objectManager = \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager;
		$this->FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices = array(
			'findMatchResults' => array(
				'TYPO3\FLOW3\AOP\Advice\AroundAdvice' => array(
					new \TYPO3\FLOW3\AOP\Advice\AroundAdvice('TYPO3\FLOW3\MVC\Web\Routing\Aspect\RouterCachingAspect', 'cacheMatchingCall', $objectManager, NULL),
				),
			),
			'resolve' => array(
				'TYPO3\FLOW3\AOP\Advice\AroundAdvice' => array(
					new \TYPO3\FLOW3\AOP\Advice\AroundAdvice('TYPO3\FLOW3\MVC\Web\Routing\Aspect\RouterCachingAspect', 'cacheResolveCall', $objectManager, NULL),
				),
			),
		);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		$this->FLOW3_AOP_Proxy_buildMethodsAndAdvicesArray();
		\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\FLOW3\MVC\Web\Routing\Router', $this);
		\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\FLOW3\MVC\Web\Routing\RouterInterface', $this);

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
	 * @param string $routePath The route path
	 * @return array results of the matching route
	 */
	 protected function findMatchResults($routePath) {

		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['findMatchResults'])) {
		$result = parent::findMatchResults($routePath);

		} else {
			$this->FLOW3_AOP_Proxy_methodIsInAdviceMode['findMatchResults'] = TRUE;
			try {
			
					$methodArguments = array();

				$methodArguments['routePath'] = $routePath;
			
					$adviceChains = $this->FLOW3_AOP_Proxy_getAdviceChains('findMatchResults');
					$adviceChain = $adviceChains['TYPO3\FLOW3\AOP\Advice\AroundAdvice'];
					$adviceChain->rewind();
					$result = $adviceChain->proceed(new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\FLOW3\MVC\Web\Routing\Router', 'findMatchResults', $methodArguments, $adviceChain));

			} catch(\Exception $e) {
				unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['findMatchResults']);
				throw $e;
			}
			unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['findMatchResults']);
		}
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 * @param array $routeValues Key/value pairs to be resolved. E.g. array('@package' => 'MyPackage', '@controller' => 'MyController');
	 * @return string
	 */
	 public function resolve(array $routeValues) {

		if (isset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['resolve'])) {
		$result = parent::resolve($routeValues);

		} else {
			$this->FLOW3_AOP_Proxy_methodIsInAdviceMode['resolve'] = TRUE;
			try {
			
					$methodArguments = array();

				$methodArguments['routeValues'] = $routeValues;
			
					$adviceChains = $this->FLOW3_AOP_Proxy_getAdviceChains('resolve');
					$adviceChain = $adviceChains['TYPO3\FLOW3\AOP\Advice\AroundAdvice'];
					$adviceChain->rewind();
					$result = $adviceChain->proceed(new \TYPO3\FLOW3\AOP\JoinPoint($this, 'TYPO3\FLOW3\MVC\Web\Routing\Router', 'resolve', $methodArguments, $adviceChain));

			} catch(\Exception $e) {
				unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['resolve']);
				throw $e;
			}
			unset($this->FLOW3_AOP_Proxy_methodIsInAdviceMode['resolve']);
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
	$reflectedClass = new \ReflectionClass('TYPO3\FLOW3\MVC\Web\Routing\Router');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('FLOW3_AOP_Proxy_targetMethodsAndGroupedAdvices', 'FLOW3_AOP_Proxy_groupedAdviceChains', 'FLOW3_AOP_Proxy_methodIsInAdviceMode'))) continue;
		if ($reflectionService->isPropertyTaggedWith('TYPO3\FLOW3\MVC\Web\Routing\Router', $propertyName, 'transient')) continue;
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
		$this->injectEnvironment(\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Utility\Environment'));
		$this->injectSystemLogger(\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get('TYPO3\FLOW3\Log\SystemLoggerInterface'));
	}
}
#0             %CLASS%TYPO3_FLOW3_MVC_Web_Routing_Router9147      