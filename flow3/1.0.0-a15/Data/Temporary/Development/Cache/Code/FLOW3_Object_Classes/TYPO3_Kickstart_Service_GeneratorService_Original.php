<?php
namespace TYPO3\Kickstart\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "Kickstart".                  *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Service for the Kickstart generator
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GeneratorService_Original {

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @inject
	 */
	protected $packageManager;

	/**
	 * @var \TYPO3\Fluid\Core\Parser\TemplateParser
	 * @inject
	 */
	protected $templateParser;

	/**
	 * @var \TYPO3\Kickstart\Utility\Inflector
	 * @inject
	 */
	protected $inflector;

	/**
	 * @var array
	 */
	protected $generatedFiles = array();

	/**
	 * Generate a controller with the given name for the given package
	 *
	 * @param string $packageKey The package key of the controller's package
	 * @param string $subpackage An optional subpackage name
	 * @param string $controllerName The name of the new controller
	 * @param boolean $overwrite Overwrite any existing files?
	 * @return array An array of generated filenames
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function generateActionController($packageKey, $subpackage, $controllerName, $overwrite = FALSE) {
		$controllerClassName = ucfirst($controllerName) . 'Controller';

		$templatePathAndFilename = 'resource://TYPO3.Kickstart/Private/Generator/Controller/ActionControllerTemplate.php.tmpl';

		$contextVariables = array();
		$contextVariables['packageKey'] = $packageKey;
		$contextVariables['packageNamespace'] = str_replace('.', '\\', $packageKey);
		$contextVariables['subpackage'] = $subpackage;
		$contextVariables['isInSubpackage'] = ($subpackage != '');
		$contextVariables['controllerClassName'] = $controllerClassName;
		$contextVariables['controllerName'] = $controllerName;

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$subpackagePath = $subpackage != '' ? $subpackage . '/' : '';
		$controllerFilename = $controllerClassName . '.php';
		$controllerPath = $this->packageManager->getPackage($packageKey)->getClassesPath() . $subpackagePath . 'Controller/';
		$targetPathAndFilename = $controllerPath . $controllerFilename;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

	/**
	 * Generate an Action Controller with pre-made CRUD methods
	 *
	 * @param string $packageKey The package key of the controller's package
	 * @param string $subpackage An optional subpackage name
	 * @param string $controllerName The name of the new controller
	 * @param boolean $overwrite Overwrite any existing files?
	 * @return array An array of generated filenames
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function generateCrudController($packageKey, $subpackage, $controllerName, $overwrite = FALSE) {
		$controllerClassName = ucfirst($controllerName) . 'Controller';

		$templatePathAndFilename = 'resource://TYPO3.Kickstart/Private/Generator/Controller/CrudControllerTemplate.php.tmpl';

		$contextVariables = array();
		$contextVariables['packageKey'] = $packageKey;
		$contextVariables['packageNamespace'] = str_replace('.', '\\', $packageKey);
		$contextVariables['subpackage'] = $subpackage;
		$contextVariables['isInSubpackage'] = ($subpackage != '');
		$contextVariables['controllerClassName'] = $controllerClassName;
		$contextVariables['controllerName'] = $controllerName;
		$contextVariables['modelName'] = strtolower($controllerName[0]) . substr($controllerName, 1);
		$contextVariables['repositoryClassName'] = '\\' . str_replace('.', '\\', $packageKey) . ($subpackage != '' ? '\\' . $subpackage : '') . '\Domain\Repository\\' . $controllerName . 'Repository';
		$contextVariables['modelFullClassName'] = '\\' . str_replace('.', '\\', $packageKey) . ($subpackage != '' ? '\\' . $subpackage : '') . '\Domain\Model\\' . $controllerName;
		$contextVariables['modelClassName'] = ucfirst($contextVariables['modelName']);

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$subpackagePath = $subpackage != '' ? $subpackage . '/' : '';
		$controllerFilename = $controllerClassName . '.php';
		$controllerPath = $this->packageManager->getPackage($packageKey)->getClassesPath() . $subpackagePath . 'Controller/';
		$targetPathAndFilename = $controllerPath . $controllerFilename;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

	/**
	 * Generate a commnand controller with the given name for the given package
	 *
	 * @param string $packageKey The package key of the controller's package
	 * @param string $controllerName The name of the new controller
	 * @param boolean $overwrite Overwrite any existing files?
	 * @return array An array of generated filenames
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function generateCommandController($packageKey, $controllerName, $overwrite = FALSE) {
		$controllerClassName = ucfirst($controllerName) . 'CommandController';

		$templatePathAndFilename = 'resource://TYPO3.Kickstart/Private/Generator/Controller/CommandControllerTemplate.php.tmpl';

		$contextVariables = array();
		$contextVariables['packageKey'] = $packageKey;
		$contextVariables['packageNamespace'] = str_replace('.', '\\', $packageKey);
		$contextVariables['controllerClassName'] = $controllerClassName;
		$contextVariables['controllerName'] = $controllerName;

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$controllerFilename = $controllerClassName . '.php';
		$controllerPath = $this->packageManager->getPackage($packageKey)->getClassesPath() . 'Command/';
		$targetPathAndFilename = $controllerPath . $controllerFilename;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

	/**
	 * Generate a view with the given name for the given package and controller
	 *
	 * @param string $packageKey The package key of the controller's package
	 * @param string $subpackage An optional subpackage name
	 * @param string $controllerName The name of the new controller
	 * @param string $viewName The name of the view
	 * @param boolean $overwrite Overwrite any existing files?
	 * @return array An array of generated filenames
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function generateView($packageKey, $subpackage, $controllerName, $viewName, $templateName, $overwrite = FALSE) {
		$viewName = ucfirst($viewName);

		$templatePathAndFilename = 'resource://TYPO3.Kickstart/Private/Generator/View/' . $templateName . 'Template.html';

		$contextVariables = array();
		$contextVariables['packageKey'] = $packageKey;
		$contextVariables['subpackage'] = $subpackage;
		$contextVariables['isInSubpackage'] = ($subpackage != '');
		$contextVariables['controllerName'] = $controllerName;
		$contextVariables['viewName'] = $viewName;
		$contextVariables['modelName'] = strtolower($controllerName[0]) . substr($controllerName, 1);
		$contextVariables['repositoryClassName'] = '\\' . str_replace('.', '\\', $packageKey) . ($subpackage != '' ? '\\' . $subpackage : '') . '\Domain\Repository\\' . $controllerName . 'Repository';
		$contextVariables['modelFullClassName'] = '\\' . str_replace('.', '\\', $packageKey) . ($subpackage != '' ? '\\' . $subpackage : '') . '\Domain\Model\\' . $controllerName;
		$contextVariables['modelClassName'] = ucfirst($contextVariables['modelName']);
		$contextVariables['propertyNames'] = array('name');

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$subpackagePath = $subpackage != '' ? $subpackage . '/' : '';
		$viewFilename = $viewName . '.html';
		$viewPath = 'resource://' . $packageKey . '/Private/Templates/' . $subpackagePath . $controllerName . '/';
		$targetPathAndFilename = $viewPath . $viewFilename;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

	/**
	 * Generate a default layout
	 *
	 * @param string $packageKey The package key of the controller's package
	 * @param string $layoutName The name of the layout
	 * @param boolean $overwrite Overwrite any existing files?
	 * @return array An array of generated filenames
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function generateLayout($packageKey, $layoutName, $overwrite = FALSE) {
		$layoutName = ucfirst($layoutName);

		$templatePathAndFilename = 'resource://TYPO3.Kickstart/Private/Generator/View/' . $layoutName . 'Layout.html';

		$contextVariables = array();
		$contextVariables['packageKey'] = $packageKey;

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$layoutFilename = $layoutName . '.html';
		$viewPath = 'resource://' . $packageKey . '/Private/Layouts/';
		$targetPathAndFilename = $viewPath . $layoutFilename;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

	/**
	 * Generate a model for the package with the given model name and fields
	 *
	 * @param string $packageKey The package key of the controller's package
	 * @param string $modelName The name of the new model
	 * @param array $fieldDefinitions The field definitions
	 * @param boolean $overwrite Overwrite any existing files?
	 * @return array An array of generated filenames
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function generateModel($packageKey, $modelName, array $fieldDefinitions, $overwrite = FALSE) {
		$modelName = ucfirst($modelName);
		$namespace = str_replace('.', '\\', $packageKey) .  '\\Domain\\Model';
		$fieldDefinitions = $this->normalizeFieldDefinitions($fieldDefinitions, $namespace);

		$templatePathAndFilename = 'resource://TYPO3.Kickstart/Private/Generator/Model/EntityTemplate.php.tmpl';

		$contextVariables = array();
		$contextVariables['packageKey'] = $packageKey;
		$contextVariables['modelName'] = $modelName;
		$contextVariables['fieldDefinitions'] = $fieldDefinitions;
		$contextVariables['namespace'] = $namespace;

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$modelFilename = $modelName . '.php';
		$modelPath = $this->packageManager->getPackage($packageKey)->getClassesPath() . 'Domain/Model/';
		$targetPathAndFilename = $modelPath . $modelFilename;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

	/**
	 * Generate a repository for a model given a model name and package key
	 *
	 * @param string $packageKey The package key
	 * @param string $modelName The name of the model
	 * @return array An array of generated filenames
	 * @param boolean $overwrite Overwrite any existing files?
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function generateRepository($packageKey, $modelName, $overwrite = FALSE) {
		$modelName = ucfirst($modelName);
		$repositoryClassName = $modelName . 'Repository';
		$namespace = str_replace('.', '\\', $packageKey) .  '\\Domain\\Repository';

		$templatePathAndFilename = 'resource://TYPO3.Kickstart/Private/Generator/Repository/RepositoryTemplate.php.tmpl';

		$contextVariables = array();
		$contextVariables['packageKey'] = $packageKey;
		$contextVariables['modelName'] = $modelName;
		$contextVariables['repositoryClassName'] = $repositoryClassName;
		$contextVariables['namespace'] = $namespace;

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$repositoryFilename = $repositoryClassName . '.php';
		$repositoryPath = $this->packageManager->getPackage($packageKey)->getClassesPath() . 'Domain/Repository/';
		$targetPathAndFilename = $repositoryPath . $repositoryFilename;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

	/**
	 * Normalize types and prefix types with namespaces
	 *
	 * @param array $fieldDefinitions The field definitions
	 * @param string $namespace The namespace
	 * @return array The normalized and type converted field definitions
	 */
	protected function normalizeFieldDefinitions(array $fieldDefinitions, $namespace = '') {
		foreach ($fieldDefinitions as &$fieldDefinition) {
			if ($fieldDefinition['type'] == 'bool') {
				$fieldDefinition['type'] = 'boolean';
			} elseif ($fieldDefinition['type'] == 'int') {
				$fieldDefinition['type'] = 'integer';
			} else if (preg_match('/^[A-Z]/', $fieldDefinition['type'])) {
				if (class_exists($fieldDefinition['type'])) {
					$fieldDefinition['type'] = '\\' . $fieldDefinition['type'];
				} else {
					$fieldDefinition['type'] = '\\' . $namespace . '\\' . $fieldDefinition['type'];
				}
			}
		}
		return $fieldDefinitions;
	}

	/**
	 * Generate a file with the given content and add it to the
	 * generated files
	 *
	 * @param string $targetPathAndFilename
	 * @param string $fileContent
	 * @param boolean $force
	 * @return void
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function generateFile($targetPathAndFilename, $fileContent, $force = FALSE) {
		if (!is_dir(dirname($targetPathAndFilename))) {
			\TYPO3\FLOW3\Utility\Files::createDirectoryRecursively(dirname($targetPathAndFilename));
		}

		if (substr($targetPathAndFilename, 0, 11) === 'resource://') {
			list($packageKey, $resourcePath) = explode('/', substr($targetPathAndFilename, 11), 2);
			$relativeTargetPathAndFilename = $packageKey . '/Resources/' . $resourcePath;

		} else {
			$relativeTargetPathAndFilename = substr($targetPathAndFilename, strrpos(substr($targetPathAndFilename, 0, strpos($targetPathAndFilename, 'Classes/') - 1), '/') + 1);
		}

		if (!file_exists($targetPathAndFilename) || $force === TRUE) {
			file_put_contents($targetPathAndFilename, $fileContent);
			$this->generatedFiles[] = 'Created .../' . $relativeTargetPathAndFilename;
		} else {
			$this->generatedFiles[] = 'Omitted .../' . $relativeTargetPathAndFilename;
		}
	}

	/**
	 * Render the given template file with the given variables
	 *
	 * @param string $templatePathAndFilename
	 * @param array $contextVariables
	 * @return string
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function renderTemplate($templatePathAndFilename, array $contextVariables) {
		$templateSource = \TYPO3\FLOW3\Utility\Files::getFileContents($templatePathAndFilename, FILE_TEXT);
		if ($templateSource === FALSE) {
			throw new \TYPO3\Fluid\Core\Exception('The template file "' . $templatePathAndFilename . '" could not be loaded.', 1225709595);
		}
		$parsedTemplate = $this->templateParser->parse($templateSource);

		$renderingContext = $this->buildRenderingContext($contextVariables);

		return $parsedTemplate->render($renderingContext);
	}

	/**
	 * Build the rendering context
	 *
	 * @param array $contextVariables
	 */
	protected function buildRenderingContext(array $contextVariables) {
		$renderingContext = $this->objectManager->create('TYPO3\Fluid\Core\Rendering\RenderingContext');

		$renderingContext->injectTemplateVariableContainer($this->objectManager->create('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer', $contextVariables));
		$renderingContext->injectViewHelperVariableContainer($this->objectManager->create('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer'));

		return $renderingContext;
	}
}

#0             %CLASS%TYPO3_Kickstart_Service_GeneratorService16797     