<?php
namespace TYPO3\FLOW3\Tests\Unit\Persistence\Generic;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3CR".                    *
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
 * Testcase for \TYPO3\FLOW3\Persistence\DataMapper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class DataMapperTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Persistence\Generic\Exception\InvalidObjectDataException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapToObjectThrowsExceptionOnEmptyInput() {
		$objectData = array();

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->_call('mapToObject', $objectData);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapToObjectsMapsArrayToObjectByCallingmapToObject() {
		$objectData = array(array('identifier' => '1234'));
		$object = new \stdClass();

		$dataMapper = $this->getMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('mapToObject'));
		$dataMapper->expects($this->once())->method('mapToObject')->with($objectData[0])->will($this->returnValue($object));

		$dataMapper->mapToObjects($objectData);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapToObjectReturnsObjectFromIdentityMapIfAvailable() {
		$objectData = array('identifier' => '1234');
		$object = new \stdClass();

		$mockSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockSession->expects($this->once())->method('hasIdentifier')->with('1234')->will($this->returnValue(TRUE));
		$mockSession->expects($this->once())->method('getObjectByIdentifier')->with('1234')->will($this->returnValue($object));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->injectPersistenceSession($mockSession);
		$dataMapper->_call('mapToObject', $objectData);
	}

	/**
	 * Test that an object is reconstituted, registered with the identity map
	 * and memorizes it's clean state.
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapToObjectReconstitutesExpectedObjectAndRegistersItWithIdentitymapToObjects() {
		$mockEntityClassName = 'Entity' . md5(uniqid(mt_rand(), TRUE));
		$mockEntity = $this->getMock('TYPO3\FLOW3\AOP\ProxyInterface', array('FLOW3_AOP_Proxy_invokeJoinPoint'), array(), $mockEntityClassName);

		$objectData = array('identifier' => '1234', 'classname' => $mockEntityClassName, 'properties' => array('foo'));

		$mockClassSchema = $this->getMock('TYPO3\FLOW3\Reflection\ClassSchema', array(), array(), '', FALSE);
		$mockClassSchema->expects($this->any())->method('getModelType')->will($this->returnValue(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY));
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService', array(), array(), '', FALSE);
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($mockEntityClassName)->will($this->returnValue($mockClassSchema));
		$mockSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockSession->expects($this->once())->method('registerReconstitutedEntity')->with($mockEntity, $objectData);
		$mockSession->expects($this->once())->method('registerObject')->with($mockEntity, '1234');

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('thawProperties'));
		$dataMapper->expects($this->once())->method('thawProperties')->with($mockEntity, $objectData['identifier'], $objectData);
		$dataMapper->injectPersistenceSession($mockSession);
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->_call('mapToObject', $objectData);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function thawPropertiesSetsPropertyValues() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $firstProperty; public $secondProperty; public $thirdProperty; public $fourthProperty; }');
		$object = new $className();

		$objectData = array(
			'identifier' => '1234',
			'classname' => 'TYPO3\Post',
			'properties' => array(
				'firstProperty' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'firstValue'
				),
				'secondProperty' => array(
					'type' => 'integer',
					'multivalue' => FALSE,
					'value' => 1234
				),
				'thirdProperty' => array(
					'type' => 'float',
					'multivalue' => FALSE,
					'value' => 1.234
				),
				'fourthProperty' => array(
					'type' => 'boolean',
					'multivalue' => FALSE,
					'value' => FALSE
				)
			)
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\Post');
		$classSchema->addProperty('firstProperty', 'string');
		$classSchema->addProperty('secondProperty', 'integer');
		$classSchema->addProperty('thirdProperty', 'float');
		$classSchema->addProperty('fourthProperty', 'boolean');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchema')->will($this->returnValue($classSchema));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->_call('thawProperties', $object, 1234, $objectData);
		$this->assertAttributeEquals('firstValue', 'firstProperty', $object);
		$this->assertAttributeEquals(1234, 'secondProperty', $object);
		$this->assertAttributeEquals(1.234, 'thirdProperty', $object);
		$this->assertAttributeEquals(FALSE, 'fourthProperty', $object);
	}

	/**
	 * After thawing the properties, the nodes' uuid will be available in the identifier
	 * property of the proxy class.
	 *
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function thawPropertiesAssignsTheUuidToTheProxy() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $FLOW3_Persistence_Entity_UUID; }');
		$object = new $className();

		$objectData = array(
			'identifier' => 'c254d2e0-825a-11de-8a39-0800200c9a66',
			'classname' => 'TYPO3\Post',
			'properties' => array()
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\Post');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchema')->will($this->returnValue($classSchema));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->_call('thawProperties', $object, $objectData['identifier'], $objectData);

		$this->assertAttributeEquals('c254d2e0-825a-11de-8a39-0800200c9a66', 'FLOW3_Persistence_Identifier', $object);
	}

	/**
	 * After thawing the properties, the nodes' uuid will be available in the identifier
	 * property of the proxy class.
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function thawPropertiesAssignsTheUuidToTheDeclaredUuidPropertyInProxy() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $myUuidProperty; }');
		$object = new $className();

		$objectData = array(
			'identifier' => 'c254d2e0-825a-11de-8a39-0800200c9a66',
			'classname' => 'TYPO3\Post',
			'properties' => array()
		);

		$classSchema = $this->getMock('TYPO3\FLOW3\Reflection\ClassSchema', array('getUuidPropertyName'), array('TYPO3\Post'));
		$classSchema->expects($this->once())->method('getUUIDPropertyName')->will($this->returnValue('myUuidProperty'));

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchema')->will($this->returnValue($classSchema));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->_call('thawProperties', $object, $objectData['identifier'], $objectData);
		$this->assertAttributeEquals('c254d2e0-825a-11de-8a39-0800200c9a66', 'myUuidProperty', $object);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function thawPropertiesDelegatesHandlingOfArraysAndObjects() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $firstProperty; public $secondProperty; public $thirdProperty; public $fourthProperty; }');
		$object = new $className();

		$objectData = array(
			'identifier' => '1234',
			'classname' => 'TYPO3\Post',
			'properties' => array(
				'firstProperty' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(array('type' => 'string', 'index' => 0, 'value' => 'theMappedArray'))
				),
				'secondProperty' => array(
					'type' => 'SplObjectStorage',
					'multivalue' => TRUE,
					'value' => array(array('type' => 'Some\Object', 'index' => NULL, 'value' => 'theMappedSplObjectStorage'))
				),
				'thirdProperty' => array(
					'type' => 'DateTime',
					'multivalue' => FALSE,
					'value' => 'theUnixtime'
				),
				'fourthProperty' => array(
					'type' => '\TYPO3\Some\Domain\Model',
					'multivalue' => FALSE,
					'value' => array('identifier' => 'theMappedObjectIdentifier')
				)
			)
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\Post');
		$classSchema->addProperty('firstProperty', 'array');
		$classSchema->addProperty('secondProperty', 'SplObjectStorage');
		$classSchema->addProperty('thirdProperty', 'DateTime');
		$classSchema->addProperty('fourthProperty', '\TYPO3\Some\Domain\Model');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchema')->will($this->returnValue($classSchema));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('mapDateTime', 'mapArray', 'mapSplObjectStorage', 'mapToObject'));
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->expects($this->at(0))->method('mapArray')->with($objectData['properties']['firstProperty']['value']);
		$dataMapper->expects($this->at(1))->method('mapSplObjectStorage')->with($objectData['properties']['secondProperty']['value']);
		$dataMapper->expects($this->at(2))->method('mapDateTime')->with($objectData['properties']['thirdProperty']['value']);
		$dataMapper->expects($this->at(3))->method('mapToObject')->with($objectData['properties']['fourthProperty']['value']);
		$dataMapper->_call('thawProperties', $object, $objectData['identifier'], $objectData);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @see http://forge.typo3.org/issues/9684
	 * @todo check for correct order again, somehow...
	 */
	public function thawPropertiesFollowsOrderOfGivenObjectData() {
		$this->markTestSkipped('The test needs to check for the correct order again, somehow....');

		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $firstProperty; public $secondProperty; public $thirdProperty; }');
		$object = new $className();

		$objectData = array(
			'identifier' => '1234',
			'classname' => 'TYPO3\Post',
			'properties' => array(
				'secondProperty' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'secondValue'
				),
				'firstProperty' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'firstValue'
				),
				'thirdProperty' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'thirdValue'
				)
			)
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\Post');
		$classSchema->addProperty('firstProperty', 'string');
		$classSchema->addProperty('secondProperty', 'string');
		$classSchema->addProperty('thirdProperty', 'string');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchema')->will($this->returnValue($classSchema));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->_call('thawProperties', $object, 1234, $objectData);

			// the order of setting those is important, but cannot be tested for now (static setProperty)
		$this->assertAttributeEquals('secondValue', 'secondProperty', $object);
		$this->assertAttributeEquals('firstValue', 'firstProperty', $object);
		$this->assertAttributeEquals('thirdValue', 'thirdProperty', $object);
	}

	/**
	 * If a property has been removed from a class old data still in the persistence
	 * must be skipped when reconstituting.
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function thawPropertiesSkipsPropertiesNoLongerInClassSchema() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $firstProperty; public $thirdProperty; }');
		$object = new $className();

		$objectData = array(
			'identifier' => '1234',
			'classname' => 'TYPO3\Post',
			'properties' => array(
				'firstProperty' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'firstValue'
				),
				'secondProperty' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'secondValue'
				),
				'thirdProperty' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'thirdValue'
				)
			)
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\Post');
		$classSchema->addProperty('firstProperty', 'string');
		$classSchema->addProperty('thirdProperty', 'string');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchema')->will($this->returnValue($classSchema));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->_call('thawProperties', $object, 1234, $objectData);

		$this->assertObjectNotHasAttribute('secondProperty', $object);
	}

	/**
	 * After thawing the properties, metadata in the object data will be set
	 * as a special proxy property.
	 *
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function thawPropertiesAssignsMetadataToTheProxyIfItExists() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $FLOW3_Persistence_Metadata; }');
		$object = new $className();

		$objectData = array(
			'identifier' => 'c254d2e0-825a-11de-8a39-0800200c9a66',
			'classname' => 'TYPO3\Post',
			'properties' => array(),
			'metadata' => array('My_Metadata' => 'Test')
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\Post');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->once())->method('getClassSchema')->will($this->returnValue($classSchema));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$dataMapper->injectReflectionService($mockReflectionService);
		$dataMapper->_call('thawProperties', $object, $objectData['identifier'], $objectData);

		$this->assertAttributeEquals(array('My_Metadata' => 'Test'), 'FLOW3_Persistence_Metadata', $object);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapSplObjectStorageCreatesSplObjectStorage() {
		$objectData = array(
			array('value' => array('mappedObject1')),
			array('value' => array('mappedObject2'))
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\Post');
		$classSchema->addProperty('firstProperty', 'SplObjectStorage');

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('mapToObject'));
		$dataMapper->expects($this->at(0))->method('mapToObject')->with($objectData[0]['value'])->will($this->returnValue(new \stdClass()));
		$dataMapper->expects($this->at(1))->method('mapToObject')->with($objectData[1]['value'])->will($this->returnValue(new \stdClass()));
		$dataMapper->_call('mapSplObjectStorage', $objectData);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapDateTimeCreatesDateTimeFromTimestamp() {
		$expected = new \DateTime();
		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$this->assertEquals($dataMapper->_call('mapDateTime', $expected->getTimestamp()), $expected);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapArrayCreatesExpectedArray() {
		$array = array('foo' => 'bar');
		$dateTime = new \DateTime();
		$object = new \stdClass();
		$splObjectStorage = new \SplObjectStorage();

		$expected = array(
			'one' => 'onetwothreefour',
			'two' => 1234,
			'three' => 1.234,
			'four' => FALSE,
			'five' => $dateTime,
			'six' => $object,
			'seven' => $splObjectStorage
		);

		$arrayValues = array(
			'one' => array(
				'type' => 'string',
				'index' => 'one',
				'value' => 'onetwothreefour'
			),
			'two' => array(
				'type' => 'integer',
				'index' => 'two',
				'value' => 1234
			),
			'three' => array(
				'type' => 'float',
				'index' => 'three',
				'value' =>  1.234
			),
			'four' => array(
				'type' => 'boolean',
				'index' => 'four',
				'value' => FALSE
			),
			'five' => array(
				'type' => 'DateTime',
				'index' => 'five',
				'value' => $dateTime->getTimestamp()
			),
			'six' => array(
				'type' => 'stdClass',
				'index' => 'six',
				'value' => array('mappedObject')
			),
			'seven' => array(
				'type' => 'SplObjectStorage',
				'index' => 'seven',
				'value' => array('mappedObject')
			)
		);

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('mapDateTime', 'mapToObject', 'mapSplObjectStorage'));
		$dataMapper->expects($this->once())->method('mapDateTime')->with($arrayValues['five']['value'])->will($this->returnValue($dateTime));
		$dataMapper->expects($this->once())->method('mapToObject')->with($arrayValues['six']['value'])->will($this->returnValue($object));
		$dataMapper->expects($this->once())->method('mapSplObjectStorage')->with($arrayValues['seven']['value'])->will($this->returnValue($splObjectStorage));
		$this->assertEquals($dataMapper->_call('mapArray', $arrayValues), $expected);
	}


	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function mapArrayMapsNestedArray() {
		$arrayValues = array(
			'one' => array(
				'type' => 'array',
				'index' => 'foo',
				'value' => array(
					array(
						'type' => 'string',
						'index' => 'bar',
						'value' => 'baz'
					),
					array(
						'type' => 'integer',
						'index' => 'quux',
						'value' => NULL
					)
				)
			)
		);

		$expected = array('foo' => array('bar' => 'baz', 'quux' => NULL));

		$dataMapper = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\DataMapper', array('dummy'));
		$this->assertEquals($expected, $dataMapper->_call('mapArray', $arrayValues));
	}
}

?>