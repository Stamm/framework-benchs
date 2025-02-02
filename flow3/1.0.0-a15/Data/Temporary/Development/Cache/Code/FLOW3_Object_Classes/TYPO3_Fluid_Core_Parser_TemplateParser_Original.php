<?php
namespace TYPO3\Fluid\Core\Parser;

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
 * Template parser building up an object syntax tree
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TemplateParser_Original {

	public static $SCAN_PATTERN_NAMESPACEDECLARATION = '/(?<!\\\\){namespace\s*([a-zA-Z]+[a-zA-Z0-9]*)\s*=\s*((?:[A-Za-z0-9\.]+|Tx)(?:FLUID_NAMESPACE_SEPARATOR\w+)+)\s*}/m';

	/**
	 * This regular expression splits the input string at all dynamic tags, AND
	 * on all <![CDATA[...]]> sections.
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS = '/
		(
			(?: <\/?                                      # Start dynamic tags
					(?:(?:NAMESPACE):[a-zA-Z0-9\\.]+)     # A tag consists of the namespace prefix and word characters
					(?:                                   # Begin tag arguments
						\s*[a-zA-Z0-9:]+                  # Argument Keys
						=                                 # =
						(?>                               # either... If we have found an argument, we will not back-track (That does the Atomic Bracket)
							"(?:\\\"|[^"])*"              # a double-quoted string
							|\'(?:\\\\\'|[^\'])*\'        # or a single quoted string
						)\s*                              #
					)*                                    # Tag arguments can be replaced many times.
				\s*
				\/?>                                      # Closing tag
			)
			|(?:                                          # Start match CDATA section
				<!\[CDATA\[.*?\]\]>
			)
		)/xs';

	/**
	 * This regular expression scans if the input string is a ViewHelper tag
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG = '/
		^<                                                # A Tag begins with <
		(?P<NamespaceIdentifier>NAMESPACE):               # Then comes the Namespace prefix followed by a :
		(?P<MethodIdentifier>                             # Now comes the Name of the ViewHelper
			[a-zA-Z0-9\\.]+
		)
		(?P<Attributes>                                   # Begin Tag Attributes
			(?:                                           # A tag might have multiple attributes
				\s*
				[a-zA-Z0-9:]+                             # The attribute name
				=                                         # =
				(?>                                       # either... # If we have found an argument, we will not back-track (That does the Atomic Bracket)
					"(?:\\\"|[^"])*"                      # a double-quoted string
					|\'(?:\\\\\'|[^\'])*\'                # or a single quoted string
				)                                         #
				\s*
			)*                                            # A tag might have multiple attributes
		)                                                 # End Tag Attributes
		\s*
		(?P<Selfclosing>\/?)                              # A tag might be selfclosing
		>$/x';

	/**
	 * This regular expression scans if the input string is a closing ViewHelper
	 * tag.
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG = '/^<\/(?P<NamespaceIdentifier>NAMESPACE):(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)\s*>$/';

	/**
	 * This regular expression splits the tag arguments into its parts
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SPLIT_PATTERN_TAGARGUMENTS = '/
		(?:                                              #
			\s*                                          #
			(?P<Argument>                                # The attribute name
				[a-zA-Z0-9:]+                            #
			)                                            #
			=                                            # =
			(?>                                          # If we have found an argument, we will not back-track (That does the Atomic Bracket)
				(?P<ValueQuoted>                         # either...
					(?:"(?:\\\"|[^"])*")                 # a double-quoted string
					|(?:\'(?:\\\\\'|[^\'])*\')           # or a single quoted string
				)
			)\s*
		)
		/xs';

	/**
	 * This pattern detects CDATA sections and outputs the text between opening
	 * and closing CDATA.
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SCAN_PATTERN_CDATA = '/^<!\[CDATA\[(.*?)\]\]>$/s';

	/**
	 * Pattern which splits the shorthand syntax into different tokens. The
	 * "shorthand syntax" is everything like {...}
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SPLIT_PATTERN_SHORTHANDSYNTAX = '/
		(
			{                                # Start of shorthand syntax
				(?:                          # Shorthand syntax is either composed of...
					[a-zA-Z0-9\->_:,.()]     # Various characters
					|"(?:\\\"|[^"])*"        # Double-quoted strings
					|\'(?:\\\\\'|[^\'])*\'   # Single-quoted strings
					|(?R)                    # Other shorthand syntaxes inside, albeit not in a quoted string
					|\s+                     # Spaces
				)+
			}                                # End of shorthand syntax
		)/x';

	/**
	 * Pattern which detects the object accessor syntax:
	 * {object.some.value}, additionally it detects ViewHelpers like
	 * {f:for(param1:bla)} and chaining like
	 * {object.some.value->f:bla.blubb()->f:bla.blubb2()}
	 *
	 * THIS IS ALMOST THE SAME AS IN $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS = '/
		^{                                                      # Start of shorthand syntax
			                                                # A shorthand syntax is either...
			(?P<Object>[a-zA-Z0-9\-_.]*)                                     # ... an object accessor
			\s*(?P<Delimiter>(?:->)?)\s*

			(?P<ViewHelper>                                 # ... a ViewHelper
				[a-zA-Z0-9]+                                # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
				:
				[a-zA-Z0-9\\.]+                             # Method Identifier (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
				\(                                          # Opening parameter brackets of ViewHelper
					(?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
						(?:
							\s*[a-zA-Z0-9\-_]+                  # The keys of the array
							\s*:\s*                             # Key|Value delimiter :
							(?:                                 # Possible value options:
								"(?:\\\"|[^"])*"                # Double qouoted string
								|\'(?:\\\\\'|[^\'])*\'          # Single quoted string
								|[a-zA-Z0-9\-_.]+               # variable identifiers
								|{(?P>ViewHelperArguments)}     # Another sub-array
							)                                   # END possible value options
							\s*,?                               # There might be a , to seperate different parts of the array
						)*                                  # The above cycle is repeated for all array elements
					)                                       # End ViewHelper Arguments submatch
				\)                                          # Closing parameter brackets of ViewHelper
			)?
			(?P<AdditionalViewHelpers>                      # There can be more than one ViewHelper chained, by adding more -> and the ViewHelper (recursively)
				(?:
					\s*->\s*
					(?P>ViewHelper)
				)*
			)
		}$/x';

	/**
	 * THIS IS ALMOST THE SAME AS $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER = '/

		(?P<NamespaceIdentifier>[a-zA-Z0-9]+)       # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
		:
		(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)
		\(                                          # Opening parameter brackets of ViewHelper
			(?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
				(?:
					\s*[a-zA-Z0-9\-_]+                  # The keys of the array
					\s*:\s*                             # Key|Value delimiter :
					(?:                                 # Possible value options:
						"(?:\\\"|[^"])*"                # Double qouoted string
						|\'(?:\\\\\'|[^\'])*\'          # Single quoted string
						|[a-zA-Z0-9\-_.]+               # variable identifiers
						|{(?P>ViewHelperArguments)}     # Another sub-array
					)                                   # END possible value options
					\s*,?                               # There might be a , to seperate different parts of the array
				)*                                  # The above cycle is repeated for all array elements
			)                                       # End ViewHelper Arguments submatch
		\)                                          # Closing parameter brackets of ViewHelper
		/x';

	/**
	 * Pattern which detects the array/object syntax like in JavaScript, so it
	 * detects strings like:
	 * {object: value, object2: {nested: array}, object3: "Some string"}
	 *
	 * THIS IS ALMOST THE SAME AS IN SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS = '/^
		(?P<Recursion>                                  # Start the recursive part of the regular expression - describing the array syntax
			{                                           # Each array needs to start with {
				(?P<Array>                              # Start submatch
					(?:
						\s*[a-zA-Z0-9\-_]+              # The keys of the array
						\s*:\s*                         # Key|Value delimiter :
						(?:                             # Possible value options:
							"(?:\\\"|[^"])*"            # Double qouoted string
							|\'(?:\\\\\'|[^\'])*\'      # Single quoted string
							|[a-zA-Z0-9\-_.]+           # variable identifiers
							|(?P>Recursion)             # Another sub-array
						)                               # END possible value options
						\s*,?                           # There might be a , to seperate different parts of the array
					)*                                  # The above cycle is repeated for all array elements
				)                                       # End array submatch
			}                                           # Each array ends with }
		)$/x';

	/**
	 * This pattern splits an array into its parts. It is quite similar to the
	 * pattern above.
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS = '/
		(?P<ArrayPart>                                             # Start submatch
			(?P<Key>[a-zA-Z0-9\-_]+)                               # The keys of the array
			\s*:\s*                                                   # Key|Value delimiter :
			(?:                                                       # Possible value options:
				(?P<QuotedString>                                     # Quoted string
					(?:"(?:\\\"|[^"])*")
					|(?:\'(?:\\\\\'|[^\'])*\')
				)
				|(?P<VariableIdentifier>[a-zA-Z][a-zA-Z0-9\-_.]*)    # variable identifiers have to start with a letter
				|(?P<Number>[0-9.]+)                                  # Number
				|{\s*(?P<Subarray>(?:(?P>ArrayPart)\s*,?\s*)+)\s*}              # Another sub-array
			)                                                         # END possible value options
		)                                                          # End array part submatch
	/x';

	/**
	 * Namespace identifiers and their component name prefix (Associative array).
	 * @var array
	 */
	protected $namespaces = array(
		'f' => 'TYPO3\Fluid\ViewHelpers'
	);

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\Fluid\Core\Parser\Configuration
	 */
	protected $configuration;

	/**
	 * Constructor. Preprocesses the $SCAN_PATTERN_NAMESPACEDECLARATION by
	 * inserting the correct namespace separator.
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct() {
		self::$SCAN_PATTERN_NAMESPACEDECLARATION = str_replace('FLUID_NAMESPACE_SEPARATOR', preg_quote(\TYPO3\Fluid\Fluid::NAMESPACE_SEPARATOR), self::$SCAN_PATTERN_NAMESPACEDECLARATION);
	}

	/**
	 * Inject object factory
	 *
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectObjectManager(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Set the configuration for the parser.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\Configuration $configuration
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setConfiguration(\TYPO3\Fluid\Core\Parser\Configuration $configuration = NULL) {
		$this->configuration = $configuration;
	}

	/**
	 * Parses a given template string and returns a parsed template object.
	 *
	 * The resulting ParsedTemplate can then be rendered by calling evaluate() on it.
	 *
	 * Normally, you should use a subclass of AbstractTemplateView instead of calling the
	 * TemplateParser directly.
	 *
	 * @param string $templateString The template to parse as a string
	 * @return \TYPO3\Fluid\Core\Parser\ParsedTemplateInterface Parsed template
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function parse($templateString) {
		if (!is_string($templateString)) throw new \TYPO3\Fluid\Core\Parser\Exception('Parse requires a template string as argument, ' . gettype($templateString) . ' given.', 1224237899);

		$this->reset();

		$templateString = $this->extractNamespaceDefinitions($templateString);
		$splitTemplate = $this->splitTemplateAtDynamicTags($templateString);
		$parsingState = $this->buildObjectTree($splitTemplate);

		$variableContainer = $parsingState->getVariableContainer();
		if ($variableContainer !== NULL && $variableContainer->exists('layoutName')) {
			$parsingState->setLayoutNameNode($variableContainer->get('layoutName'));
		}

		return $parsingState;
	}

	/**
	 * Gets the namespace definitions found.
	 *
	 * @return array Namespace identifiers and their component name prefix
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getNamespaces() {
		return $this->namespaces;
	}

	/**
	 * Resets the parser to its default values.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function reset() {
		$this->namespaces = array(
			'f' => 'TYPO3\Fluid\ViewHelpers'
		);
	}

	/**
	 * Extracts namespace definitions out of the given template string and sets
	 * $this->namespaces.
	 *
	 * @param string $templateString Template string to extract the namespaces from
	 * @return string The updated template string without namespace declarations inside
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function extractNamespaceDefinitions($templateString) {
		$matchedVariables = array();
		if (preg_match_all(self::$SCAN_PATTERN_NAMESPACEDECLARATION, $templateString, $matchedVariables) > 0) {
			foreach (array_keys($matchedVariables[0]) as $index) {
				$namespaceIdentifier = $matchedVariables[1][$index];
				$fullyQualifiedNamespace = $matchedVariables[2][$index];
				if (key_exists($namespaceIdentifier, $this->namespaces)) {
					throw new \TYPO3\Fluid\Core\Parser\Exception('Namespace identifier "' . $namespaceIdentifier . '" is already registered. Do not redeclare namespaces!', 1224241246);
				}
				$this->namespaces[$namespaceIdentifier] = $fullyQualifiedNamespace;
			}

			$templateString = preg_replace(self::$SCAN_PATTERN_NAMESPACEDECLARATION, '', $templateString);
		}
		return $templateString;
	}

	/**
	 * Splits the template string on all dynamic tags found.
	 *
	 * @param string $templateString Template string to split.
	 * @return array Splitted template
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function splitTemplateAtDynamicTags($templateString) {
		$regularExpression = $this->prepareTemplateRegularExpression(self::$SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS);
		return preg_split($regularExpression, $templateString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Build object tree from the split template
	 *
	 * @param array $splitTemplate The split template, so that every tag with a namespace declaration is already a seperate array element.
	 * @return \TYPO3\Fluid\Core\Parser\ParsingState
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function buildObjectTree($splitTemplate) {
		$regularExpression_openingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG);
		$regularExpression_closingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG);

		$state = $this->objectManager->create('TYPO3\Fluid\Core\Parser\ParsingState');
		$rootNode = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		$state->setRootNode($rootNode);
		$state->pushNodeToStack($rootNode);

		foreach ($splitTemplate as $templateElement) {
			$matchedVariables = array();
			if (preg_match(self::$SCAN_PATTERN_CDATA, $templateElement, $matchedVariables) > 0) {
				$this->textHandler($state, $matchedVariables[1]);
			} elseif (preg_match($regularExpression_openingViewHelperTag, $templateElement, $matchedVariables) > 0) {
				$this->openingViewHelperTagHandler($state, $matchedVariables['NamespaceIdentifier'], $matchedVariables['MethodIdentifier'], $matchedVariables['Attributes'], ($matchedVariables['Selfclosing'] === '' ? FALSE : TRUE));
			} elseif (preg_match($regularExpression_closingViewHelperTag, $templateElement, $matchedVariables) > 0) {
				$this->closingViewHelperTagHandler($state, $matchedVariables['NamespaceIdentifier'], $matchedVariables['MethodIdentifier']);
			} else {
				$this->textAndShorthandSyntaxHandler($state, $templateElement);
			}
		}

		if ($state->countNodeStack() !== 1) {
			throw new \TYPO3\Fluid\Core\Parser\Exception('Not all tags were closed!', 1238169398);
		}
		return $state;
	}

	/**
	 * Handles an opening or self-closing view helper tag.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $state Current parsing state
	 * @param string $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
	 * @param string $methodIdentifier Method identifier
	 * @param string $arguments Arguments string, not yet parsed
	 * @param boolean $selfclosing true, if the tag is a self-closing tag.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function openingViewHelperTagHandler(\TYPO3\Fluid\Core\Parser\ParsingState $state, $namespaceIdentifier, $methodIdentifier, $arguments, $selfclosing) {
		$argumentsObjectTree = $this->parseArguments($arguments);
		$this->initializeViewHelperAndAddItToStack($state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree);

		if ($selfclosing) {
			$node = $state->popNodeFromStack();
			$this->callInterceptor($node, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
		}
	}

	/**
	 * Initialize the given ViewHelper and adds it to the current node and to
	 * the stack.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $state Current parsing state
	 * @param string $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
	 * @param string $methodIdentifier Method identifier
	 * @param array $argumentsObjectTree Arguments object tree
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function initializeViewHelperAndAddItToStack(\TYPO3\Fluid\Core\Parser\ParsingState $state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree) {
		if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
			throw new \TYPO3\Fluid\Core\Parser\Exception('Namespace could not be resolved. This exception should never be thrown!', 1224254792);
		}
		$viewHelper = $this->objectManager->get($this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier));

			// The following three checks are only done *in an uncached template*, and not needed anymore in the cached version
		$expectedViewHelperArguments = $viewHelper->prepareArguments();
		$this->abortIfUnregisteredArgumentsExist($expectedViewHelperArguments, $argumentsObjectTree);
		$this->abortIfRequiredArgumentsAreMissing($expectedViewHelperArguments, $argumentsObjectTree);
		$this->rewriteBooleanNodesInArgumentsObjectTree($expectedViewHelperArguments, $argumentsObjectTree);

		$currentViewHelperNode = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $viewHelper, $argumentsObjectTree);

		$state->getNodeFromStack()->addChildNode($currentViewHelperNode);

		if ($viewHelper instanceof \TYPO3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface && !($viewHelper instanceof \TYPO3\Fluid\Core\ViewHelper\Facets\CompilableInterface)) {
			$state->setCompilable(FALSE);
		}

			// PostParse Facet
		if ($viewHelper instanceof \TYPO3\Fluid\Core\ViewHelper\Facets\PostParseInterface) {
			// Don't just use $viewHelper::postParseEvent(...),
			// as this will break with PHP < 5.3.
			call_user_func(array($viewHelper, 'postParseEvent'), $currentViewHelperNode, $argumentsObjectTree, $state->getVariableContainer());
		}

		$this->callInterceptor($currentViewHelperNode, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, $state);

		$state->pushNodeToStack($currentViewHelperNode);
	}

	/**
	 * Throw an exception if there are arguments which were not registered
	 * before.
	 *
	 * @param array $expectedArguments Array of TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition of all expected arguments
	 * @param array $actualArguments Actual arguments
	 * @throws \TYPO3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function abortIfUnregisteredArgumentsExist($expectedArguments, $actualArguments) {
		$expectedArgumentNames = array();
		foreach ($expectedArguments as $expectedArgument) {
			$expectedArgumentNames[] = $expectedArgument->getName();
		}

		foreach (array_keys($actualArguments) as $argumentName) {
			if (!in_array($argumentName, $expectedArgumentNames)) {
				throw new \TYPO3\Fluid\Core\Parser\Exception('Argument "' . $argumentName . '" was not registered.', 1237823695);
			}
		}
	}

	/**
	 * Throw an exception if required arguments are missing
	 *
	 * @param array $expectedArguments Array of TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition of all expected arguments
	 * @param array $actualArguments Actual arguments
	 * @throws \TYPO3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function abortIfRequiredArgumentsAreMissing($expectedArguments, $actualArguments) {
		$actualArgumentNames = array_keys($actualArguments);
		foreach ($expectedArguments as $expectedArgument) {
			if ($expectedArgument->isRequired() && !in_array($expectedArgument->getName(), $actualArgumentNames)) {
				throw new \TYPO3\Fluid\Core\Parser\Exception('Required argument "' . $expectedArgument->getName() . '" was not supplied.', 1237823699);
			}
		}
	}

	/**
	 * Wraps the argument tree, if a node is boolean, into a Boolean syntax tree node
	 *
	 * @param array $argumentDefinitions the argument definitions, key is the argument name, value is the ArgumentDefinition object
	 * @param array $argumentsObjectTree the arguments syntax tree, key is the argument name, value is an AbstractNode
	 * @return void
	 */
	protected function rewriteBooleanNodesInArgumentsObjectTree($argumentDefinitions, &$argumentsObjectTree) {
		foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
			if ($argumentDefinition->getType() === 'boolean' && isset($argumentsObjectTree[$argumentName])) {
				$argumentsObjectTree[$argumentName] = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($argumentsObjectTree[$argumentName]);
			}
		}
	}

	/**
	 * Resolve a viewhelper name.
	 *
	 * @param string $namespaceIdentifier Namespace identifier for the view helper.
	 * @param string $methodIdentifier Method identifier, might be hierarchical like "link.url"
	 * @return string The fully qualified class name of the viewhelper
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function resolveViewHelperName($namespaceIdentifier, $methodIdentifier) {
		$explodedViewHelperName = explode('.', $methodIdentifier);
		$className = '';
		if (count($explodedViewHelperName) > 1) {
			$className = implode(\TYPO3\Fluid\Fluid::NAMESPACE_SEPARATOR, array_map('ucfirst', $explodedViewHelperName));
		} else {
			$className = ucfirst($explodedViewHelperName[0]);
		}
		$className .= 'ViewHelper';

		$name = $this->namespaces[$namespaceIdentifier] . \TYPO3\Fluid\Fluid::NAMESPACE_SEPARATOR . $className;

		return $name;
	}

	/**
	 * Handles a closing view helper tag
	 *
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $state The current parsing state
	 * @param string $namespaceIdentifier Namespace identifier for the closing tag.
	 * @param string $methodIdentifier Method identifier.
	 * @return void
	 * @throws \TYPO3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function closingViewHelperTagHandler(\TYPO3\Fluid\Core\Parser\ParsingState $state, $namespaceIdentifier, $methodIdentifier) {
		if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
			throw new \TYPO3\Fluid\Core\Parser\Exception('Namespace could not be resolved. This exception should never be thrown!', 1224256186);
		}
		$lastStackElement = $state->popNodeFromStack();
		if (!($lastStackElement instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode)) {
			throw new \TYPO3\Fluid\Core\Parser\Exception('You closed a templating tag which you never opened!', 1224485838);
		}
		if ($lastStackElement->getViewHelperClassName() != $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier)) {
			throw new \TYPO3\Fluid\Core\Parser\Exception('Templating tags not properly nested. Expected: ' . $lastStackElement->getViewHelperClassName() . '; Actual: ' . $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier), 1224485398);
		}
		$this->callInterceptor($lastStackElement, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
	}

	/**
	 * Handles the appearance of an object accessor (like {posts.author.email}).
	 * Creates a new instance of \TYPO3\Fluid\ObjectAccessorNode.
	 *
	 * Handles ViewHelpers as well which are in the shorthand syntax.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $state The current parsing state
	 * @param string $objectAccessorString String which identifies which objects to fetch
	 * @param string $delimiter
	 * @param string $viewHelperString
	 * @param string $additionalViewHelpersString
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function objectAccessorHandler(\TYPO3\Fluid\Core\Parser\ParsingState $state, $objectAccessorString, $delimiter, $viewHelperString, $additionalViewHelpersString) {
		$viewHelperString .= $additionalViewHelpersString;
		$numberOfViewHelpers = 0;

			// The following post-processing handles a case when there is only a ViewHelper, and no Object Accessor.
			// Resolves bug #5107.
		if (strlen($delimiter) === 0 && strlen($viewHelperString) > 0) {
			$viewHelperString = $objectAccessorString . $viewHelperString;
			$objectAccessorString = '';
		}

			// ViewHelpers
		$matches = array();
		if (strlen($viewHelperString) > 0 && preg_match_all(self::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER, $viewHelperString, $matches, PREG_SET_ORDER) > 0) {
				// The last ViewHelper has to be added first for correct chaining.
			foreach (array_reverse($matches) as $singleMatch) {
				if (strlen($singleMatch['ViewHelperArguments']) > 0) {
					$arguments = $this->postProcessArgumentsForObjectAccessor(
						$this->recursiveArrayHandler($singleMatch['ViewHelperArguments'])
					);
				} else {
					$arguments = array();
				}
				$this->initializeViewHelperAndAddItToStack($state, $singleMatch['NamespaceIdentifier'], $singleMatch['MethodIdentifier'], $arguments);
				$numberOfViewHelpers++;
			}
		}

			// Object Accessor
		if (strlen($objectAccessorString) > 0) {

			$node = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', $objectAccessorString);
			$this->callInterceptor($node, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $state);

			$state->getNodeFromStack()->addChildNode($node);
		}

			// Close ViewHelper Tags if needed.
		for ($i=0; $i<$numberOfViewHelpers; $i++) {
			$node = $state->popNodeFromStack();
			$this->callInterceptor($node, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
		}
	}

	/**
	 * Call all interceptors registered for a given interception point.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node The syntax tree node which can be modified by the interceptors.
	 * @param integer $interceptionPoint the interception point. One of the \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_* constants.
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState the parsing state
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function callInterceptor(\TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface &$node, $interceptionPoint, \TYPO3\Fluid\Core\Parser\ParsingState $state) {
		if ($this->configuration !== NULL) {
			// $this->configuration is UNSET inside the arguments of a ViewHelper.
			// That's why the interceptors are only called if the object accesor is not inside a ViewHelper Argument
			// This could be a problem if We have a ViewHelper as an argument to another ViewHelper, and an ObjectAccessor nested inside there.
			// TODO: Clean up this.
			$interceptors = $this->configuration->getInterceptors($interceptionPoint);
			if (count($interceptors) > 0) {
				foreach($interceptors as $interceptor) {
					$node = $interceptor->process($node, $interceptionPoint, $state);
				}
			}
		}
	}

	/**
	 * Post process the arguments for the ViewHelpers in the object accessor
	 * syntax. We need to convert an array into an array of (only) nodes
	 *
	 * @param array $arguments The arguments to be processed
	 * @return array the processed array
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo This method should become superflous once the rest has been refactored, so that this code is not needed.
	 */
	protected function postProcessArgumentsForObjectAccessor(array $arguments) {
		foreach ($arguments as $argumentName => $argumentValue) {
			if (!($argumentValue instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode)) {
				$arguments[$argumentName] = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', (string)$argumentValue);
			}
		}
		return $arguments;
	}

	/**
	 * Parse arguments of a given tag, and build up the Arguments Object Tree
	 * for each argument.
	 * Returns an associative array, where the key is the name of the argument,
	 * and the value is a single Argument Object Tree.
	 *
	 * @param string $argumentsString All arguments as string
	 * @return array An associative array of objects, where the key is the argument name.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function parseArguments($argumentsString) {
		$argumentsObjectTree = array();
		$matches = array();
		if (preg_match_all(self::$SPLIT_PATTERN_TAGARGUMENTS, $argumentsString, $matches, PREG_SET_ORDER) > 0) {
			$configurationBackup = $this->configuration;
			$this->configuration = NULL;
			foreach ($matches as $singleMatch) {
				$argument = $singleMatch['Argument'];
				$value = $this->unquoteString($singleMatch['ValueQuoted']);
				$argumentsObjectTree[$argument] = $this->buildArgumentObjectTree($value);
			}
			$this->configuration = $configurationBackup;
		}
		return $argumentsObjectTree;
	}

	/**
	 * Build up an argument object tree for the string in $argumentString.
	 * This builds up the tree for a single argument value.
	 *
	 * This method also does some performance optimizations, so in case
	 * no { or < is found, then we just return a TextNode.
	 *
	 * @param string $argumentString
	 * @return ArgumentObject the corresponding argument object tree.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function buildArgumentObjectTree($argumentString) {
		if (strpos($argumentString, '{') === FALSE && strpos($argumentString, '<') === FALSE) {
			return $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $argumentString);
		}
		$splitArgument = $this->splitTemplateAtDynamicTags($argumentString);
		$rootNode = $this->buildObjectTree($splitArgument)->getRootNode();
		return $rootNode;
	}

	/**
	 * Removes escapings from a given argument string and trims the outermost
	 * quotes.
	 *
	 * This method is meant as a helper for regular expression results.
	 *
	 * @param string $quotedValue Value to unquote
	 * @return string Unquoted value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function unquoteString($quotedValue) {
		switch ($quotedValue[0]) {
			case '"':
				$value = str_replace('\"', '"', trim($quotedValue, '"'));
			break;
			case "'":
				$value = str_replace("\'", "'", trim($quotedValue, "'"));
			break;
		}
		return str_replace('\\\\', '\\', $value);
	}

	/**
	 * Takes a regular expression template and replaces "NAMESPACE" with the
	 * currently registered namespace identifiers. Returns a regular expression
	 * which is ready to use.
	 *
	 * @param string $regularExpression Regular expression template
	 * @return string Regular expression ready to be used
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function prepareTemplateRegularExpression($regularExpression) {
		return str_replace('NAMESPACE', implode('|', array_keys($this->namespaces)), $regularExpression);
	}

	/**
	 * Handler for everything which is not a ViewHelperNode.
	 *
	 * This includes Text, array syntax, and object accessor syntax.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $state Current parsing state
	 * @param string $text Text to process
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function textAndShorthandSyntaxHandler(\TYPO3\Fluid\Core\Parser\ParsingState $state, $text) {
		$sections = preg_split($this->prepareTemplateRegularExpression(self::$SPLIT_PATTERN_SHORTHANDSYNTAX), $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		foreach ($sections as $section) {
			$matchedVariables = array();
			if (preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
				$this->objectAccessorHandler($state, $matchedVariables['Object'], $matchedVariables['Delimiter'], (isset($matchedVariables['ViewHelper'])?$matchedVariables['ViewHelper']:''), (isset($matchedVariables['AdditionalViewHelpers'])?$matchedVariables['AdditionalViewHelpers']:''));
			} elseif (preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $section, $matchedVariables) > 0) {
				$this->arrayHandler($state, $matchedVariables['Array']);
			} else {
				$this->textHandler($state, $section);
			}
		}
	}

	/**
	 * Handler for array syntax. This creates the array object recursively and
	 * adds it to the current node.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $state The current parsing state
	 * @param string $arrayText The array as string.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function arrayHandler(\TYPO3\Fluid\Core\Parser\ParsingState $state, $arrayText) {
		$state->getNodeFromStack()->addChildNode(
			$this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode', $this->recursiveArrayHandler($arrayText))
		);
	}

	/**
	 * Recursive function which takes the string representation of an array and
	 * builds an object tree from it.
	 *
	 * Deals with the following value types:
	 * - Numbers (Integers and Floats)
	 * - Strings
	 * - Variables
	 * - sub-arrays
	 *
	 * @param string $arrayText Array text
	 * @return \TYPO3\Fluid\ArrayNode the array node built up
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function recursiveArrayHandler($arrayText) {
		$matches = array();
		if (preg_match_all(self::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS, $arrayText, $matches, PREG_SET_ORDER) > 0) {
			$arrayToBuild = array();
			foreach ($matches as $singleMatch) {
				$arrayKey = $singleMatch['Key'];
				if (!empty($singleMatch['VariableIdentifier'])) {
					$arrayToBuild[$arrayKey] = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', $singleMatch['VariableIdentifier']);
				} elseif (array_key_exists('Number', $singleMatch) && ( !empty($singleMatch['Number']) || $singleMatch['Number'] === '0' ) ) {
					$arrayToBuild[$arrayKey] = floatval($singleMatch['Number']);
				} elseif ( ( array_key_exists('QuotedString', $singleMatch) && !empty($singleMatch['QuotedString']) ) ) {
					$argumentString = $this->unquoteString($singleMatch['QuotedString']);
					$arrayToBuild[$arrayKey] = $this->buildArgumentObjectTree($argumentString);
				} elseif ( array_key_exists('Subarray', $singleMatch) && !empty($singleMatch['Subarray'])) {
					$arrayToBuild[$arrayKey] = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode', $this->recursiveArrayHandler($singleMatch['Subarray']));
				} else {
					throw new \TYPO3\Fluid\Core\Parser\Exception('This exception should never be thrown, as the array value has to be of some type (Value given: "' . var_export($singleMatch, TRUE) . '"). Please post your template to the bugtracker at forge.typo3.org.', 1225136013);
				}
			}
			return $arrayToBuild;
		} else {
			throw new \TYPO3\Fluid\Core\Parser\Exception('This exception should never be thrown, there is most likely some error in the regular expressions. Please post your template to the bugtracker at forge.typo3.org.', 1225136013);
		}
	}

	/**
	 * Text node handler
	 *
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $state
	 * @param string $text
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function textHandler(\TYPO3\Fluid\Core\Parser\ParsingState $state, $text) {
		$node = $this->objectManager->create('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $text);
		$this->callInterceptor($node, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_TEXT, $state);

		$state->getNodeFromStack()->addChildNode($node);
	}

}

#0             %CLASS%TYPO3_Fluid_Core_Parser_TemplateParser40473     