<?php
namespace TYPO3\FLOW3\AOP\Pointcut;

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
 * The pointcut expression parser parses the definition of the place and circumstances
 * where advices can be inserted later on. The input of the parse() function is a string
 * from a pointcut- or advice annotation and returns a pointcut filter composite.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @see \TYPO3\FLOW3\AOP\Pointcut, PointcutFilterComposite
 * @scope singleton
 * @proxy disable
 */
class PointcutExpressionParser {

	const PATTERN_SPLITBYOPERATOR = '/\s*(\&\&|\|\|)\s*/';
	const PATTERN_MATCHPOINTCUTDESIGNATOR = '/^\s*(classTaggedWith|class|methodTaggedWith|method|within|filter|setting|evaluate)/';
	const PATTERN_MATCHVISIBILITYMODIFIER = '/^(public|protected) +/';
	const PATTERN_MATCHRUNTIMEEVALUATIONSDEFINITION = '/(?:
														(?:
															\s*(   "(?:\\\"|[^"])*"
																|\(.*?\)
																|\'(?:\\\\\'|[^\'])*\'
																|[a-zA-Z0-9\-_.]+
															)
															\s*(===?|!==?|<=|>=|<|>|in|contains|matches)\s*
															(   "(?:\\\"|[^"])*"
																|\(.*?\)
																|\'(?:\\\\\'|[^\'])*\'
																|[a-zA-Z0-9\-_.]+
															)
														 )
														 \s*,{0,1}?
													 )+
												   /x';
	const PATTERN_MATCHRUNTIMEEVALUATIONSVALUELIST = '/(?:
																	\s*(
																		"(?:\\\"|[^"])*"
																		|\'(?:\\\\\'|[^\'])*\'
																		|(?:[a-zA-Z0-9\-_.])+
																	)
																	\s*,{0,1}?
																)+
																/x';
	const PATTERN_MATCHMETHODNAMEANDARGUMENTS = '/^(?P<MethodName>.*)\((?P<MethodArguments>.*)\)$/';

	/**
	 * @var \TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder
	 */
	protected $proxyClassBuilder;

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var string
	 */
	protected $sourceHint = '';

	/**
	 * @param \TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder $proxyClassBuilder
	 * @return void
	 */
	public function injectProxyClassBuilder(\TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder $proxyClassBuilder) {
		$this->proxyClassBuilder = $proxyClassBuilder;
	}

	/**
	 * @param \TYPO3\FLOW3\Reflection\ReflectionService $reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectObjectManager(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Parses a string pointcut expression and returns the pointcut
	 * objects accordingly
	 *
	 * @param string $pointcutExpression The expression defining the pointcut
	 * @param string $sourceHint A message giving a hint on where the expression was defined. This is used in error messages.
	 * @return PointcutFilterComposite A composite of class-filters, method-filters and pointcuts
	 * @throws \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function parse($pointcutExpression, $sourceHint) {
		$this->sourceHint = $sourceHint;

		if (!is_string($pointcutExpression) || strlen($pointcutExpression) === 0) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Pointcut expression must be a valid string, ' . gettype($pointcutExpression) . ' given, defined in ' . $this->sourceHint, 1168874738);
		}
		$pointcutFilterComposite = new PointcutFilterComposite();
		$pointcutExpressionParts = preg_split(self::PATTERN_SPLITBYOPERATOR, $pointcutExpression, -1, PREG_SPLIT_DELIM_CAPTURE);

		for ($partIndex = 0; $partIndex < count($pointcutExpressionParts); $partIndex += 2) {
			$operator = ($partIndex > 0) ? trim($pointcutExpressionParts[$partIndex - 1]) : '&&';
			$expression = trim($pointcutExpressionParts[$partIndex]);

			if ($expression[0] === '!') {
				$expression = trim(substr($expression, 1));
				$operator .= '!';
			}

			if (strpos($expression, '(') === FALSE) {
				$this->parseDesignatorPointcut($operator, $expression, $pointcutFilterComposite);
			} else {
				$matches = array();
				$numberOfMatches = preg_match(self::PATTERN_MATCHPOINTCUTDESIGNATOR, $expression, $matches);
				if ($numberOfMatches !== 1) {
					throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Syntax error: Pointcut designator expected near "' . $expression . '", defined in ' . $this->sourceHint, 1168874739);
				}
				$pointcutDesignator = $matches[0];
				$signaturePattern = $this->getSubstringBetweenParentheses($expression);
				switch ($pointcutDesignator) {
					case 'classTaggedWith' :
					case 'class' :
					case 'methodTaggedWith' :
					case 'method' :
					case 'within' :
					case 'filter' :
					case 'setting' :
						$parseMethodName = 'parseDesignator' . ucfirst($pointcutDesignator);
						$this->$parseMethodName($operator, $signaturePattern, $pointcutFilterComposite);
					break;
					case 'evaluate' :
						$this->parseRuntimeEvaluations($operator, $signaturePattern, $pointcutFilterComposite);
					break;
					default :
						throw new \TYPO3\FLOW3\AOP\Exception('Support for pointcut designator "' . $pointcutDesignator . '" has not been implemented (yet), defined in ' . $this->sourceHint, 1168874740);
				}
			}
		}
		return $pointcutFilterComposite;
	}

	/**
	 * Takes a class tag filter pattern and adds a so configured class tag filter to the
	 * filter composite object.
	 *
	 * @param string $operator The operator
	 * @param string $classTagPattern The pattern expression as configuration for the class tag filter
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the class tag filter) will be added to this composite object.
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseDesignatorClassTaggedWith($operator, $classTagPattern, PointcutFilterComposite $pointcutFilterComposite) {
		$filter = new PointcutClassTaggedWithFilter($classTagPattern);
		$filter->injectReflectionService($this->reflectionService);
		$pointcutFilterComposite->addFilter($operator, $filter);
	}

	/**
	 * Takes a class filter pattern and adds a so configured class filter to the
	 * filter composite object.
	 *
	 * @param string $operator The operator
	 * @param string $classPattern The pattern expression as configuration for the class filter
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the class filter) will be added to this composite object.
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseDesignatorClass($operator, $classPattern, PointcutFilterComposite $pointcutFilterComposite) {
		$filter = new PointcutClassNameFilter($classPattern);
		$filter->injectReflectionService($this->reflectionService);
		$pointcutFilterComposite->addFilter($operator, $filter);
	}

	/**
	 * Takes a method tag filter pattern and adds a so configured method tag filter to the
	 * filter composite object.
	 *
	 * @param string $operator The operator
	 * @param string $methodTagPattern The pattern expression as configuration for the method tag filter
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the method tag filter) will be added to this composite object.
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseDesignatorMethodTaggedWith($operator, $methodTagPattern, PointcutFilterComposite $pointcutFilterComposite) {
		$filter = new PointcutMethodTaggedWithFilter($methodTagPattern);
		$filter->injectReflectionService($this->reflectionService);
		$pointcutFilterComposite->addFilter($operator, $filter);
	}

	/**
	 * Splits the parameters of the pointcut designator "method" into a class
	 * and a method part and adds the appropriately configured filters to the
	 * filter composite object.
	 *
	 * @param string $operator The operator
	 * @param string $signaturePattern The pattern expression defining the class and method - the "signature"
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the class and method filter) will be added to this composite object.
	 * @return void
	 * @throws \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException if there's an error in the pointcut expression
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseDesignatorMethod($operator, $signaturePattern, PointcutFilterComposite $pointcutFilterComposite) {
		if (strpos($signaturePattern, '->') === FALSE) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Syntax error: "->" expected in "' . $signaturePattern . '", defined in ' . $this->sourceHint, 1169027339);
		}
		$methodVisibility = $this->getVisibilityFromSignaturePattern($signaturePattern);
		list($classPattern, $methodPattern) = explode ('->', $signaturePattern, 2);
		if (strpos($methodPattern, '(') === FALSE ) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Syntax error: "(" expected in "' . $methodPattern . '", defined in ' . $this->sourceHint, 1169144299);
		}

		$matches = array();
		preg_match(self::PATTERN_MATCHMETHODNAMEANDARGUMENTS, $methodPattern, $matches);

		$methodNamePattern = $matches['MethodName'];
		$methodArgumentPattern = $matches['MethodArguments'];
		$methodArgumentConstraints = $this->getArgumentConstraintsFromMethodArgumentsPattern($methodArgumentPattern);

		$classNameFilter = new PointcutClassNameFilter($classPattern);
		$classNameFilter->injectReflectionService($this->reflectionService);
		$methodNameFilter = new PointcutMethodNameFilter($methodNamePattern, $methodVisibility, $methodArgumentConstraints);
		$methodNameFilter->injectSystemLogger($this->objectManager->get('TYPO3\FLOW3\Log\SystemLoggerInterface'));
		$methodNameFilter->injectReflectionService($this->reflectionService);

		if ($operator !== '&&') {
			$subComposite = new PointcutFilterComposite();
			$subComposite->addFilter('&&', $classNameFilter);
			$subComposite->addFilter('&&', $methodNameFilter);

			$pointcutFilterComposite->addFilter($operator, $subComposite);
		} else {
			$pointcutFilterComposite->addFilter('&&', $classNameFilter);
			$pointcutFilterComposite->addFilter('&&', $methodNameFilter);
		}
	}

	/**
	 * Adds a class type filter to the poincut filter composite
	 *
	 * @param string $operator
	 * @param string $signaturePattern The pattern expression defining the class type
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the class type filter) will be added to this composite object.
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseDesignatorWithin($operator, $signaturePattern, PointcutFilterComposite $pointcutFilterComposite) {
		$filter = new PointcutClassTypeFilter($signaturePattern);
		$filter->injectReflectionService($this->reflectionService);
		$pointcutFilterComposite->addFilter($operator, $filter);
	}

	/**
	 * Splits the value of the pointcut designator "pointcut" into an aspect
	 * class- and a pointcut method part and adds the appropriately configured
	 * filter to the composite object.
	 *
	 * @param string $operator The operator
	 * @param string $pointcutExpression The pointcut expression (value of the designator)
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the pointcut filter) will be added to this composite object.
	 * @return void
	 * @throws \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseDesignatorPointcut($operator, $pointcutExpression, PointcutFilterComposite $pointcutFilterComposite) {
		if (strpos($pointcutExpression, '->') === FALSE) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Syntax error: "->" expected in "' . $pointcutExpression . '", defined in ' . $this->sourceHint, 1172219205);
		}
		list($aspectClassName, $pointcutMethodName) = explode ('->', $pointcutExpression, 2);
		$pointcutFilter = new PointcutFilter($aspectClassName, $pointcutMethodName);
		$pointcutFilter->injectProxyClassBuilder($this->proxyClassBuilder);
		$pointcutFilterComposite->addFilter($operator, $pointcutFilter);
	}

	/**
	 * Adds a custom filter to the poincut filter composite
	 *
	 * @param string $operator The operator
	 * @param string $filterObjectName Object Name of the custom filter (value of the designator)
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the custom filter) will be added to this composite object.
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function parseDesignatorFilter($operator, $filterObjectName, PointcutFilterComposite $pointcutFilterComposite) {
		$customFilter = $this->objectManager->get($filterObjectName);
		if (!$customFilter instanceof PointcutFilterInterface) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Invalid custom filter: "' . $filterObjectName . '" does not implement the required PoincutFilterInterface, defined in ' . $this->sourceHint, 1231871755);
		}
		$pointcutFilterComposite->addFilter($operator, $customFilter);
	}

	/**
	 * Adds a setting filter to the poincut filter composite
	 *
	 * @param string $operator The operator
	 * @param string $configurationPath The path to the settings option, that should be used
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the custom filter) will be added to this composite object.
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function parseDesignatorSetting($operator, $configurationPath, PointcutFilterComposite $pointcutFilterComposite) {
		$filter = new PointcutSettingFilter($configurationPath);
		$filter->injectConfigurationManager($this->objectManager->get('TYPO3\FLOW3\Configuration\ConfigurationManager'));

		$pointcutFilterComposite->addFilter($operator, $filter);
	}

	/**
	 * Adds runtime evaluations to the poincut filter composite
	 *
	 * @param string $operator The operator
	 * @param string $runtimeEvaluations The runtime evaluations string
	 * @param PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the custom filter) will be added to this composite object.
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function parseRuntimeEvaluations($operator, $runtimeEvaluations, PointcutFilterComposite $pointcutFilterComposite) {
		$runtimeEvaluationsDefintion = array(
			$operator => array(
				'evaluateConditions' => $this->getRuntimeEvaluationConditionsFromEvaluateString($runtimeEvaluations)
			)
		);

		$pointcutFilterComposite->setGlobalRuntimeEvaluationsDefinition($runtimeEvaluationsDefintion);
	}

	/**
	 * Returns the substring of $string which is enclosed by parentheses
	 * of the first level.
	 *
	 * @param string $string The string to parse
	 * @return string The inner part between the first level of parentheses
	 * @throws \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getSubstringBetweenParentheses($string) {
		$startingPosition = 0;
		$openParentheses = 0;
		$substring = '';

		for ($i = $startingPosition; $i < strlen($string); $i++) {
			if ($string[$i] === ')') {
				$openParentheses--;
			}
			if ($openParentheses > 0) {
				$substring .= $string{$i};
			}
			if ($string[$i] === '(') {
				$openParentheses++;
			}
		}
		if ($openParentheses < 0) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Pointcut expression is in excess of ' . abs($openParentheses) . ' closing parenthese(s), defined in ' . $this->sourceHint, 1168966689);
		}
		if ($openParentheses > 0) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Pointcut expression lacks of ' . $openParentheses . ' closing parenthese(s), defined in ' . $this->sourceHint, 1168966690);
		}
		return $substring;
	}

	/**
	 * Parses the signature pattern and returns the visibility modifier if any. If a modifier
	 * was found, it will be removed from the $signaturePattern.
	 *
	 * @param string &$signaturePattern The regular expression for matching the method() signature
	 * @return string Visibility modifier or NULL of none was found
	 * @throws \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getVisibilityFromSignaturePattern(&$signaturePattern) {
		$visibility = NULL;
		$matches = array();
		$numberOfMatches = preg_match_all(self::PATTERN_MATCHVISIBILITYMODIFIER, $signaturePattern, $matches, PREG_SET_ORDER);
		if ($numberOfMatches > 1) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Syntax error: method name expected after visibility modifier in "' . $signaturePattern . '", defined in ' . $this->sourceHint, 1172492754);
		}
		if ($numberOfMatches === FALSE) {
			throw new \TYPO3\FLOW3\AOP\Exception\InvalidPointcutExpressionException('Error while matching visibility modifier in "' . $signaturePattern . '", defined in ' . $this->sourceHint, 1172492967);
		}
		if ($numberOfMatches === 1) {
			$visibility = $matches[0][1];
			$signaturePattern = trim(substr($signaturePattern, strlen($visibility)));
		}
		return $visibility;
	}

  /**
	* Parses the method arguments pattern and returns the corresponding constraints array
	*
	* @param string $methodArgumentsPattern The arguments pattern defined in the pointcut expression
	* @return array The corresponding constraints array
	* @author Andreas Förthner <andreas.foerthner@netlogix.de>
	*/
	protected function getArgumentConstraintsFromMethodArgumentsPattern($methodArgumentsPattern) {
		$matches = array();
		$argumentConstraints = array();

		preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSDEFINITION, $methodArgumentsPattern, $matches);

		for ($i = 0; $i < count($matches[0]); $i++) {
			if ($matches[2][$i] === 'in' || $matches[2][$i] === 'matches') {
				$list = array();
				$listEntries = array();

				if (preg_match('/^\s*\(.*\)\s*$/', $matches[3][$i], $list) > 0) {
					preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSVALUELIST, $list[0], $listEntries);
					$matches[3][$i] = $listEntries[1];
				}
			}

			$argumentConstraints[$matches[1][$i]]['operator'][] = $matches[2][$i];
			$argumentConstraints[$matches[1][$i]]['value'][] = $matches[3][$i];
		}
		return $argumentConstraints;
  }

	/**
	 * Parses the evaluate string for runtime evaluations and returns the corresponding conditions array
	 *
	 * @param string $evaluateString The evaluate string defined in the pointcut expression
	 * @return array The corresponding constraints array
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function getRuntimeEvaluationConditionsFromEvaluateString($evaluateString) {
		$matches = array();
		$runtimeEvaluationConditions = array();

		preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSDEFINITION, $evaluateString, $matches);

		for ($i = 0; $i < count($matches[0]); $i++) {
			if ($matches[2][$i] === 'in' || $matches[2][$i] === 'matches') {
				$list = array();
				$listEntries = array();

				if (preg_match('/^\s*\(.*\)\s*$/', $matches[3][$i], $list) > 0) {
					preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSVALUELIST, $list[0], $listEntries);
					$matches[3][$i] = $listEntries[1];
				}
			}

			$runtimeEvaluationConditions[] = array(
				'operator' => $matches[2][$i],
				'leftValue' => $matches[1][$i],
				'rightValue' => $matches[3][$i],
			);
		}
		return $runtimeEvaluationConditions;
	}
}
?>