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

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Test for the Abstract Form view helper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AbstractFormViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function renderHiddenIdentityFieldReturnsAHiddenInputFieldContainingTheObjectsUUID() {
		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockPersistenceManager = $this->getMock('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue('123'));

		$expectedResult = chr(10) . '<input type="hidden" name="prefix[theName][__identity]" value="123" />' . chr(10);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function renderHiddenIdentityFieldReturnsAHiddenInputFieldIfObjectIsNewButAClone() {
		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockPersistenceManager = $this->getMock('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue('123'));

		$expectedResult = chr(10) . '<input type="hidden" name="prefix[theName][__identity]" value="123" />' . chr(10);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderHiddenIdentityFieldReturnsACommentIfTheObjectIsWithoutIdentity() {
		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockPersistenceManager = $this->getMock('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue(NULL));

		$expectedResult = chr(10) . '<!-- Object of type ' . get_class($object) . ' is without identity -->' . chr(10);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNameReturnsEmptyStringIfGivenFieldNameIsNULL() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertSame('', $viewHelper->_call('prefixFieldName', NULL));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNameReturnsEmptyStringIfGivenFieldNameIsEmpty() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertSame('', $viewHelper->_call('prefixFieldName', ''));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNameReturnsGivenFieldNameIfFieldNamePrefixIsEmpty() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(''));

		$this->assertSame('someFieldName', $viewHelper->_call('prefixFieldName', 'someFieldName'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNamePrefixesGivenFieldNameWithFieldNamePrefix() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('somePrefix'));

		$this->assertSame('somePrefix[someFieldName]', $viewHelper->_call('prefixFieldName', 'someFieldName'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNamePreservesSquareBracketsOfFieldName() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('somePrefix[foo]'));

		$this->assertSame('somePrefix[foo][someFieldName][bar]', $viewHelper->_call('prefixFieldName', 'someFieldName[bar]'));
	}
}

?>