<?php
namespace TYPO3\FLOW3\Tests\Unit\Reflection;

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
 * Testcase for the Class Schema.
 *
 * Note that many parts of the class schema functionality are already tested by the class
 * schema builder testcase.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ClassSchemaTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function hasPropertyReturnsTrueOnlyForExistingProperties() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->addProperty('a', 'string');
		$classSchema->addProperty('b', 'integer');

		$this->assertTrue($classSchema->hasProperty('a'));
		$this->assertTrue($classSchema->hasProperty('b'));
		$this->assertFalse($classSchema->hasProperty('c'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getPropertiesReturnsAddedProperties() {
		$expectedProperties = array(
			'a' => array('type' => 'string', 'elementType' => NULL, 'lazy' => FALSE),
			'b' => array('type' => 'TYPO3\FLOW3\SomeObject', 'elementType' => NULL, 'lazy' => TRUE)
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->addProperty('a', 'string');
		$classSchema->addProperty('b', 'TYPO3\FLOW3\SomeObject', TRUE);

		$this->assertSame($expectedProperties, $classSchema->getProperties());
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @expectedException \InvalidArgumentException
	 */
	public function markAsIdentityPropertyRejectsUnknownProperties() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');

		$classSchema->markAsIdentityProperty('unknownProperty');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @expectedException \InvalidArgumentException
	 */
	public function markAsIdentityPropertyRejectsLazyLoadedProperties() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->addProperty('lazyProperty', 'TYPO3\FLOW3\SomeObject', TRUE);

		$classSchema->markAsIdentityProperty('lazyProperty');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentityPropertiesReturnsNamesAndTypes() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->addProperty('a', 'string');
		$classSchema->addProperty('b', 'integer');

		$classSchema->markAsIdentityProperty('a');

		$this->assertSame(array('a' => 'string'), $classSchema->getIdentityProperties());
	}

	/**
	 * data provider for addPropertyAcceptsValidPropertyTypes
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function validPropertyTypes() {
		return array(
			array('integer'),
			array('int'),
			array('float'),
			array('boolean'),
			array('bool'),
			array('string'),
			array('DateTime'),
			array('array'),
			array('ArrayObject'),
			array('SplObjectStorage'),
			array('TYPO3\FLOW3\Foo'),
			array('\TYPO3\FLOW3\Bar'),
			array('\Some\Object'),
			array('SomeObject'),
			array('array<string>'),
			array('array<TYPO3\FLOW3\Baz>')
		);
	}

	/**
	 * @dataProvider validPropertyTypes()
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function addPropertyAcceptsValidPropertyTypes($propertyType) {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
			// dummy assertion to avoid incomplete  test detection
		$this->assertNull($classSchema->addProperty('a', $propertyType));
	}

	/**
	 * data provider for addPropertyRejectsInvalidPropertyTypes
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function invalidPropertyTypes() {
		return array(
			array('stdClass'),
			array('\someObject'),
			array('string<string>'),
			array('int<TYPO3\FLOW3\Baz>')
		);
	}
	/**
	 * @dataProvider invalidPropertyTypes()
	 * @test
	 * @expectedException \InvalidArgumentException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function addPropertyRejectsInvalidPropertyTypes($propertyType) {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->addProperty('a', $propertyType);
	}

	/**
	 * Collections are arrays, ArrayObject and SplObjectStorage
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function addPropertyStoresElementTypesForCollectionProperties() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->addProperty('a', 'array<\TYPO3\FLOW3\Foo>');

		$properties = $classSchema->getProperties();
		$this->assertEquals('array', $properties['a']['type']);
		$this->assertEquals('TYPO3\FLOW3\Foo', $properties['a']['elementType']);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Reflection\Exception\ClassSchemaConstraintViolationException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setUuidPropertyNameThrowsExceptionForValueObjects() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);
		$classSchema->setUuidPropertyName('foo');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Reflection\Exception\ClassSchemaConstraintViolationException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function markAsIdentityPropertyThrowsExceptionForValueObjects() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);
		$classSchema->markAsIdentityProperty('foo');
	}


	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setModelTypeResetsUuidPropertyNameAndIdentityPropertiesAndAggregateRootForValueObjects() {
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('SomeClass');
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('foo', 'string');
		$classSchema->addProperty('bar', 'string');
		$classSchema->setUuidPropertyName('foo');
		$classSchema->markAsIdentityProperty('bar');
		$classSchema->setRepositoryClassName('Some\Repository');
		$this->assertSame('foo', $classSchema->getUuidPropertyName());
		$this->assertSame(array('bar' => 'string'), $classSchema->getIdentityProperties());

		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);

		$this->assertNull($classSchema->getUuidPropertyName());
		$this->assertSame(array(), $classSchema->getIdentityProperties());
		$this->assertFalse($classSchema->isAggregateRoot());
	}

}

?>