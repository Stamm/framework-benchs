<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Form;

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

require_once(__DIR__ . '/FormFieldViewHelperBaseTestcase.php');

/**
 * Test for the Abstract Form view helper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AbstractFormFieldViewHelperTest extends \TYPO3\Fluid\Tests\Unit\ViewHelpers\Form\FormFieldViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function ifAnAttributeValueIsAnObjectMaintainedByThePersistenceManagerItIsConvertedToAUUID() {
		$mockPersistenceManager = $this->getMock('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('6f487e40-4483-11de-8a39-0800200c9a66'));

		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);
		$object->expects($this->any())->method('FLOW3_Persistence_isNew')->will($this->returnValue(FALSE));

		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->injectPersistenceManager($mockPersistenceManager);

		$arguments = array('name' => 'foo', 'value' => $object, 'property' => NULL);
		$formViewHelper->_set('arguments', $arguments);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));

		$this->assertSame('foo[__identity]', $formViewHelper->_call('getName'));
		$this->assertSame('6f487e40-4483-11de-8a39-0800200c9a66', $formViewHelper->_call('getValue'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getNameBuildsNameFromFieldNamePrefixFormObjectNameAndPropertyIfInObjectAccessorMode() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue('myObjectName'));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[myObjectName][bla]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getNameBuildsNameFromFieldNamePrefixFormObjectNameAndHierarchicalPropertyIfInObjectAccessorMode() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue('myObjectName'));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla.blubb');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[myObjectName][bla][blubb]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getNameBuildsNameFromFieldNamePrefixAndPropertyIfInObjectAccessorModeAndNoFormObjectNameIsSpecified() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue(NULL));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[bla]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getNameBuildsNameFromFieldNamePrefixAndFieldNameIfNotInObjectAccessorMode() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[fieldName]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getValueBuildsValueFromPropertyAndFormObjectIfInObjectAccessorMode() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode', 'addAdditionalIdentityPropertiesIfNeeded'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$className = 'test_' . uniqid();
		$mockObject = eval('
			class ' . $className . ' {
				public function getSomething() {
					return "MyString";
				}
				public function getValue() {
					return new ' . $className . ';
				}
			}
			return new ' . $className . ';
		');

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->once())->method('addAdditionalIdentityPropertiesIfNeeded');
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue($mockObject));
		$this->viewHelperVariableContainer->expects($this->once())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue(TRUE));

		$arguments = array('name' => NULL, 'value' => NULL, 'property' => 'value.something');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'MyString';
		$actual = $formViewHelper->_call('getValue');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getValueReturnsNullIfNotInObjectAccessorModeAndValueArgumentIsNoSet() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));

		$mockArguments = array();
		$formViewHelper->_set('arguments', $mockArguments);

		$this->assertNull($formViewHelper->_call('getValue'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getValueReturnsValueArgumentIfSpecified() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$mockArguments = array('value' => 'someValue');
		$formViewHelper->_set('arguments', $mockArguments);

		$this->assertEquals('someValue', $formViewHelper->_call('getValue'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function isObjectAccessorModeReturnsTrueIfPropertyIsSetAndFormObjectIsGiven() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue(TRUE));

		$formViewHelper->_set('arguments', array('name' => NULL, 'value' => NULL, 'property' => 'bla'));
		$this->assertTrue($formViewHelper->_call('isObjectAccessorMode'));

		$formViewHelper->_set('arguments', array('name' => NULL, 'value' => NULL, 'property' => NULL));
		$this->assertFalse($formViewHelper->_call('isObjectAccessorMode'));
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getErrorsForPropertyReturnsErrorsFromRequestIfPropertyIsSet() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');

		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->once())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$mockArguments = $this->getMock('TYPO3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
		$mockArguments->expects($this->once())->method('offsetGet')->with('property')->will($this->returnValue('bar'));
		$formViewHelper->_set('arguments', $mockArguments);
		$this->viewHelperVariableContainer->expects($this->any())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue('foo'));

		$mockArgumentError = $this->getMock('TYPO3\FLOW3\MVC\Controller\ArgumentError', array(), array('foo'));
		$mockArgumentError->expects($this->once())->method('getPropertyName')->will($this->returnValue('foo'));
		$mockPropertyError = $this->getMock('TYPO3\FLOW3\Validation\PropertyError', array(), array('bar'));
		$mockPropertyError->expects($this->once())->method('getPropertyName')->will($this->returnValue('bar'));
		$mockError = $this->getMock('TYPO3\FLOW3\Error\Error', array(), array(), '', FALSE);
		$mockPropertyError->expects($this->once())->method('getErrors')->will($this->returnValue(array($mockError)));
		$mockArgumentError->expects($this->once())->method('getErrors')->will($this->returnValue(array($mockPropertyError)));
		$this->request->expects($this->once())->method('getErrors')->will($this->returnValue(array($mockArgumentError)));

		$errors = $formViewHelper->_call('getErrorsForProperty');
		$this->assertEquals(array($mockError), $errors);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getErrorsForPropertyReturnsEmptyArrayIfPropertyIsNotSet() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$mockArguments = $this->getMock('TYPO3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
		$mockArguments->expects($this->once())->method('hasArgument')->with('property')->will($this->returnValue(FALSE));
		$formViewHelper->_set('arguments', $mockArguments);

		$errors = $formViewHelper->_call('getErrorsForProperty');
		$this->assertEquals(array(), $errors);
	}


	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setErrorClassAttributeDoesNotSetClassAttributeIfNoErrorOccured() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getErrorsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$mockArguments = $this->getMock('TYPO3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
		$mockArguments->expects($this->once())->method('hasArgument')->with('class')->will($this->returnValue(FALSE));
		$formViewHelper->_set('arguments', $mockArguments);

		$this->tagBuilder->expects($this->never())->method('addAttribute');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setErrorClassAttributeSetsErrorClassIfAnErrorOccured() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getErrorsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$mockArguments = $this->getMock('TYPO3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
		$mockArguments->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(FALSE));
		$mockArguments->expects($this->at(1))->method('hasArgument')->with('errorClass')->will($this->returnValue(FALSE));
		$formViewHelper->_set('arguments', $mockArguments);

		$mockError = $this->getMock('TYPO3\FLOW3\Error\Error', array(), array(), '', FALSE);
		$formViewHelper->expects($this->once())->method('getErrorsForProperty')->will($this->returnValue(array($mockError)));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'error');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setErrorClassAttributeAppendsErrorClassToExistingClassesIfAnErrorOccured() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getErrorsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$mockArguments = $this->getMock('TYPO3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
		$mockArguments->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(TRUE));
		$mockArguments->expects($this->at(1))->method('offsetGet')->with('class')->will($this->returnValue('default classes'));
		$mockArguments->expects($this->at(2))->method('hasArgument')->with('errorClass')->will($this->returnValue(FALSE));
		$formViewHelper->_set('arguments', $mockArguments);

		$mockError = $this->getMock('TYPO3\FLOW3\Error\Error', array(), array(), '', FALSE);
		$formViewHelper->expects($this->once())->method('getErrorsForProperty')->will($this->returnValue(array($mockError)));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'default classes error');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setErrorClassAttributeSetsCustomErrorClassIfAnErrorOccured() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getErrorsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$mockArguments = $this->getMock('TYPO3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
		$mockArguments->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(FALSE));
		$mockArguments->expects($this->at(1))->method('hasArgument')->with('errorClass')->will($this->returnValue(TRUE));
		$mockArguments->expects($this->at(2))->method('offsetGet')->with('errorClass')->will($this->returnValue('custom-error-class'));
		$formViewHelper->_set('arguments', $mockArguments);

		$mockError = $this->getMock('TYPO3\FLOW3\Error\Error', array(), array(), '', FALSE);
		$formViewHelper->expects($this->once())->method('getErrorsForProperty')->will($this->returnValue(array($mockError)));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'custom-error-class');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setErrorClassAttributeAppendsCustomErrorClassIfAnErrorOccured() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getErrorsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$mockArguments = $this->getMock('TYPO3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
		$mockArguments->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(TRUE));
		$mockArguments->expects($this->at(1))->method('offsetGet')->with('class')->will($this->returnValue('default classes'));
		$mockArguments->expects($this->at(2))->method('hasArgument')->with('errorClass')->will($this->returnValue(TRUE));
		$mockArguments->expects($this->at(3))->method('offsetGet')->with('errorClass')->will($this->returnValue('custom-error-class'));
		$formViewHelper->_set('arguments', $mockArguments);

		$mockError = $this->getMock('TYPO3\FLOW3\Error\Error', array(), array(), '', FALSE);
		$formViewHelper->expects($this->once())->method('getErrorsForProperty')->will($this->returnValue(array($mockError)));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'default classes custom-error-class');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addAdditionalIdentityPropertiesIfNeededDoesNotCreateAnythingIfPropertyIsWithoutDot() {
		$formFieldViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formFieldViewHelper);
		$arguments = array('property' => 'simple');
		$formFieldViewHelper->expects($this->any())->method('renderHiddenIdentityField')->will($this->throwException(new \Exception('Should not be executed!!!')));
		$formFieldViewHelper->_set('arguments', $arguments);
		$formFieldViewHelper->_call('addAdditionalIdentityPropertiesIfNeeded');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addAdditionalIdentityPropertiesIfNeededCallsRenderIdentityFieldWithTheRightParameters() {
		$className = 'test_' . uniqid();
		$mockFormObject = eval('
			class ' . $className . ' {
				public function getSomething() {
					return "MyString";
				}
				public function getValue() {
					return new ' . $className . ';
				}
			}
			return new ' . $className . ';
		');
		$property = 'value.something';
		$objectName = 'myObject';
		$expectedProperty = 'myObject[value]';

		$formFieldViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formFieldViewHelper);
		$arguments = array('property' => $property);
		$formFieldViewHelper->_set('arguments', $arguments);
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue($mockFormObject));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue($objectName));

		$formFieldViewHelper->expects($this->once())->method('renderHiddenIdentityField')->with($mockFormObject, $expectedProperty);

		$formFieldViewHelper->_call('addAdditionalIdentityPropertiesIfNeeded');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addAdditionalIdentityPropertiesIfNeededCallsRenderIdentityFieldWithTheRightParametersWithMoreHierarchyLevels() {
		$className = 'test_' . uniqid();
		$mockFormObject = eval('
			class ' . $className . ' {
				public function getSomething() {
					return "MyString";
				}
				public function getValue() {
					return new ' . $className . ';
				}
			}
			return new ' . $className . ';
		');
		$property = 'value.value.something';
		$objectName = 'myObject';
		$expectedProperty1 = 'myObject[value]';
		$expectedProperty2 = 'myObject[value][value]';

		$formFieldViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formFieldViewHelper);
		$arguments = array('property' => $property);
		$formFieldViewHelper->_set('arguments', $arguments);
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue($mockFormObject));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue($objectName));

		$formFieldViewHelper->expects($this->at(0))->method('renderHiddenIdentityField')->with($mockFormObject, $expectedProperty1);
		$formFieldViewHelper->expects($this->at(1))->method('renderHiddenIdentityField')->with($mockFormObject, $expectedProperty2);

		$formFieldViewHelper->_call('addAdditionalIdentityPropertiesIfNeeded');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderHiddenFieldForEmptyValueRendersHiddenFieldIfItHasNotBeenRenderedBefore() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('SomeFieldName'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(array()));

		$expected = '<input type="hidden" name="SomeFieldName" value="" />';
		$actual = $formViewHelper->_call('renderHiddenFieldForEmptyValue');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderHiddenFieldForEmptyValueAddsHiddenFieldNameToVariableContainerIfItHasBeenRendered() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('NewFieldName'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(array('OldFieldName')));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('addOrUpdate')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields', array('OldFieldName', 'NewFieldName'));

		$formViewHelper->_call('renderHiddenFieldForEmptyValue');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderHiddenFieldForEmptyValueDoesNotRenderHiddenFieldIfItHasBeenRenderedBefore() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('SomeFieldName'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(array('SomeFieldName')));
		$this->viewHelperVariableContainer->expects($this->never())->method('addOrUpdate');

		$expected = '';
		$actual = $formViewHelper->_call('renderHiddenFieldForEmptyValue');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderHiddenFieldForEmptyValueRemovesEmptySquareBracketsFromHiddenFieldName() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('SomeFieldName[WithBrackets][]'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(array()));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('addOrUpdate')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields', array('SomeFieldName[WithBrackets]'));

		$expected = '<input type="hidden" name="SomeFieldName[WithBrackets]" value="" />';
		$actual = $formViewHelper->_call('renderHiddenFieldForEmptyValue');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderHiddenFieldForEmptyValueDoesNotRemoveNonEmptySquareBracketsFromHiddenFieldName() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('SomeFieldName[WithBrackets][foo]'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields')->will($this->returnValue(array()));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('addOrUpdate')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'renderedHiddenFields', array('SomeFieldName[WithBrackets][foo]'));

		$expected = '<input type="hidden" name="SomeFieldName[WithBrackets][foo]" value="" />';
		$actual = $formViewHelper->_call('renderHiddenFieldForEmptyValue');
		$this->assertEquals($expected, $actual);
	}
}

?>