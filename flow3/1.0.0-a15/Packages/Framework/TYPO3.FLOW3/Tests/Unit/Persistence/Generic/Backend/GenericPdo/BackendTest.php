<?php
namespace TYPO3\FLOW3\Tests\Unit\Persistence\Generic\Backend\GenericPdo;

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

require_once(__DIR__ . '/../../../Fixture/AnEntity.php');
require_once(__DIR__ . '/../../../Fixture/AValue.php');

if (!interface_exists('PdoInterface', FALSE)) {
	require(__DIR__ . '/../../../Fixture/PdoInterface.php');
}

/**
 * Testcase for \TYPO3\FLOW3\Persistence\Backend\GenericPdo\Backend
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class BackendTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeCallsToParentAndConnectsToDatabase() {
		$backend = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend', array('connect'));
		$backend->expects($this->once())->method('connect');
		$backend->initialize(array());
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function hasValueobjectRecordEmitsExpectedSql() {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('fakeHash'));
		$mockStatement->expects($this->once())->method('fetchColumn');
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SELECT COUNT("identifier") FROM "valueobjects" WHERE "identifier"=?')->will($this->returnValue($mockStatement));
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));
		$backend->injectPersistenceSession(new \TYPO3\FLOW3\Persistence\Generic\Session());
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_call('hasValueobjectRecord', 'fakeHash');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removePropertiesEmitsExpectedSql() {
		$mockDeletePropertyStatement = $this->getMock('PDOStatement');
		$mockDeletePropertyStatement->expects($this->once())->method('execute')->with(array('identifier', 'propertyname'));
		$mockDeleteDataStatement = $this->getMock('PDOStatement');
		$mockDeleteDataStatement->expects($this->once())->method('execute')->with(array('identifier', 'propertyname'));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->at(0))->method('prepare')->with('DELETE FROM "properties" WHERE "parent"=? AND "name"=?')->will($this->returnValue($mockDeletePropertyStatement));
		$mockPdo->expects($this->at(1))->method('prepare')->with('DELETE FROM "properties_data" WHERE "parent"=? AND "name"=?')->will($this->returnValue($mockDeleteDataStatement));
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));
		$backend->injectPersistenceSession(new \TYPO3\FLOW3\Persistence\Generic\Session());
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_call('removeProperties', array('propertyname' => array('parent' => 'identifier')));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removePropertiesByParentEmitsExpectedSql() {
		$parent = new \stdClass();
		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->registerObject($parent, 'identifier');
		$mockDeletePropertyStatement = $this->getMock('PDOStatement');
		$mockDeletePropertyStatement->expects($this->once())->method('execute')->with(array('identifier'));
		$mockDeleteDataStatement = $this->getMock('PDOStatement');
		$mockDeleteDataStatement->expects($this->once())->method('execute')->with(array('identifier'));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->at(0))->method('prepare')->with('DELETE FROM "properties_data" WHERE "parent"=?')->will($this->returnValue($mockDeleteDataStatement));
		$mockPdo->expects($this->at(1))->method('prepare')->with('DELETE FROM "properties" WHERE "parent"=?')->will($this->returnValue($mockDeletePropertyStatement));
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_call('removePropertiesByParent', $parent);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectCreatesRecordOnlyForNewObject() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\FLOW3\Persistence\Tests\\' . $className;
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\AOP\ProxyInterface {
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
		}');
		$newObject = new $fullClassName();
		$oldObject = new $fullClassName();

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService', array('getClassSchema'));
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnValue(new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName)));

		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->injectReflectionService($mockReflectionService);
		$persistenceSession->registerObject($oldObject, '');

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'emitPersistedObject', 'validateObject'));
		$backend->expects($this->exactly(2))->method('emitPersistedObject');
		$backend->expects($this->once())->method('createObjectRecord');
		$backend->injectPersistenceSession($persistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $newObject, NULL);
		$backend->_call('persistObject', $oldObject, NULL);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @see http://forge.typo3.org/issues/show/3859
	 */
	public function persistObjectsHandlesCyclicReferences() {
		$namespace = 'TYPO3\FLOW3\Persistence\Tests';
		$className1 = 'RootClass' . uniqid();
		$fullClassName1 = $namespace . '\\' . $className1;
		eval('namespace ' . $namespace . '; class ' . $className1 . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			public $FLOW3_Persistence_Identifier = \'A\';
			public $sub;
		}');
		$className2 = 'SubClass' . uniqid();
		$fullClassName2 = $namespace . '\\' . $className2;
		eval('namespace ' . $namespace . '; class ' . $className2 . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			public $FLOW3_Persistence_Identifier = \'B\';
			public $sub;
		}');
		$className3 = 'SubClass' . uniqid();
		$fullClassName3 = $namespace . '\\' . $className3;
		eval('namespace ' . $namespace . '; class ' . $className3 . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			public $FLOW3_Persistence_Identifier = \'C\';
			public $sub;
		}');
		$objectA = new $fullClassName1();
		$objectB = new $fullClassName2();
		$objectC = new $fullClassName3();
		$objectA->sub = $objectB;
		$objectB->sub = $objectC;
		$objectC->sub = $objectB;
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($objectA);

		$classSchema1 = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName1);
		$classSchema1->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema1->addProperty('sub', $fullClassName2);
		$classSchema1->setRepositoryClassName('Some\Repository');
		$classSchema2 = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName2);
		$classSchema2->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema2->addProperty('sub', $fullClassName3);
		$classSchema3 = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName3);
		$classSchema3->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema3->addProperty('sub', $fullClassName2);
		$classSchemata = array(
			$fullClassName1 => $classSchema1,
			$fullClassName2 => $classSchema2,
			$fullClassName3 => $classSchema3
		);

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnCallback(function ($object) use ($classSchemata) {
			return $classSchemata[get_class($object)];
		}));

		$mockSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('hasObject'));
		$mockSession->injectReflectionService($mockReflectionService);
		$mockSession->expects($this->at(0))->method('hasObject')->with($this->attribute($this->equalTo('A'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
		$mockSession->expects($this->at(1))->method('hasObject')->with($this->attribute($this->equalTo('A'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
		$mockSession->expects($this->at(2))->method('hasObject')->with($this->attribute($this->equalTo('A'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
			// the following fails although the same object is present, neither equalTo nor identicalTo work...
		//$mockSession->expects($this->at(0))->method('hasObject')->/*with($this->identicalTo($objectA))->*/will($this->returnValue(FALSE));
		$mockSession->expects($this->at(3))->method('hasObject')->with($this->attribute($this->equalTo('B'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
		$mockSession->expects($this->at(4))->method('hasObject')->with($this->attribute($this->equalTo('B'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
		$mockSession->expects($this->at(5))->method('hasObject')->with($this->attribute($this->equalTo('B'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
		$mockSession->expects($this->at(6))->method('hasObject')->with($this->attribute($this->equalTo('C'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
		$mockSession->expects($this->at(7))->method('hasObject')->with($this->attribute($this->equalTo('C'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));
		$mockSession->expects($this->at(8))->method('hasObject')->with($this->attribute($this->equalTo('C'), 'FLOW3_Persistence_Identifier'))->will($this->returnValue(FALSE));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'setProperties', 'emitPersistedObject', 'validateObject'));
		$backend->expects($this->exactly(3))->method('createObjectRecord')->will($this->onConsecutiveCalls('A', 'B', 'C'));
		$expectedPropertiesOfA = array(
			'identifier' => 'A',
			'classname' => $fullClassName1,
			'properties' => array(
				'sub' => array(
					'type' => $fullClassName2,
					'multivalue' => FALSE,
					'value' => array(
						'identifier' => 'B'
					)
				)
			)
		);
		$expectedPropertiesOfB = array(
			'identifier' => 'B',
			'classname' => $fullClassName2,
			'properties' => array(
				'sub' => array(
					'type' => $fullClassName3,
					'multivalue' => FALSE,
					'value' => array(
						'identifier' => 'C'
					)
				)
			)
		);
		$expectedPropertiesOfC = array(
			'identifier' => 'C',
			'classname' => $fullClassName3,
			'properties' => array(
				'sub' => array(
					'type' => $fullClassName2,
					'multivalue' => FALSE,
					'value' => array(
						'identifier' => 'B'
					)
				)
			)
		);
		$backend->expects($this->at(6))->method('setProperties')->with($expectedPropertiesOfC);
		$backend->expects($this->at(8))->method('setProperties')->with($expectedPropertiesOfB);
		$backend->expects($this->at(10))->method('setProperties')->with($expectedPropertiesOfA);
		$backend->injectPersistenceSession($mockSession);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->injectReflectionService($mockReflectionService);

		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function uuidPropertyNameFromNewObjectIsUsedForRecord() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\AOP\ProxyInterface {
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
		}');
		$newObject = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->addProperty('idProp', 'string');
		$classSchema->setUUIDPropertyName('idProp');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($newObject)->will($this->returnValue($classSchema));

		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array($identifier, $fullClassName, ''));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('INSERT INTO "entities" ("identifier", "type", "parent") VALUES (?, ?, ?)')->will($this->returnValue($mockStatement));
		$mockSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject'));
		$mockSession->injectReflectionService($mockReflectionService);
		$mockSession->expects($this->any())->method('getIdentifierByObject')->with($newObject)->will($this->returnValue($identifier));
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));
		$backend->injectPersistenceSession($mockSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('createObjectRecord', $newObject, NULL);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function uuidOfNewEntityIsUsedForRecord() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\AOP\ProxyInterface {
			public $FLOW3_Persistence_Identifier = \'' . $identifier . '\';
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
		}');
		$newObject = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($newObject)->will($this->returnValue($classSchema));

		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array($identifier, $fullClassName, 'parentUuid'));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('INSERT INTO "entities" ("identifier", "type", "parent") VALUES (?, ?, ?)')->will($this->returnValue($mockStatement));
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->injectReflectionService($mockReflectionService);
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));
		$backend->injectPersistenceSession($session);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('createObjectRecord', $newObject, 'parentUuid');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function hashOfNewValueObjectIsUsedForRecord() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$hash = sha1($fullClassName);
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			public $FLOW3_Persistence_Identifier = \'' . $hash . '\';
		}');
		$newObject = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($newObject)->will($this->returnValue($classSchema));

		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array($hash, $fullClassName));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('INSERT INTO "valueobjects" ("identifier", "type") VALUES (?, ?)')->will($this->returnValue($mockStatement));
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->injectReflectionService($mockReflectionService);
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));
		$backend->injectPersistenceSession($session);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('createObjectRecord', $newObject, NULL);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectProcessesDirtyObject() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			public $simpleString = \'simpleValue\';
		}');
		$dirtyObject = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('simpleString', 'string');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($dirtyObject)->will($this->returnValue($classSchema));

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('isDirty'));
		$mockPersistenceSession->expects($this->once())->method('isDirty')->will($this->returnValue(TRUE));
		$mockPersistenceSession->registerObject($dirtyObject, $identifier);

		$expectedProperties = array(
			'identifier' => $identifier,
			'classname' => $fullClassName,
			'properties' => array(
				'simpleString' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'simpleValue'
				)
			)
		);
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('setProperties', 'emitPersistedObject', 'validateObject'));
		$backend->expects($this->once())->method('setProperties')->with($expectedProperties);
		$backend->expects($this->once())->method('emitPersistedObject', \TYPO3\FLOW3\Persistence\Generic\Backend\AbstractBackend::OBJECTSTATE_RECONSTITUTED);
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());

		$backend->_call('persistObject', $dirtyObject, $identifier);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectProcessesObjectsWithDateTimeMember() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			public $date;
		}');
		$newObject = new $fullClassName();
		$date = new \DateTime();
		$newObject->date = $date;
		$newObject->FLOW3_Persistence_Identifier = NULL;

		$expectedProperties = array(
			'identifier' => NULL,
			'classname' => $fullClassName,
			'properties' => array(
				'date' => array(
					'type' => 'DateTime',
					'multivalue' => FALSE,
					'value' => $date->getTimestamp()
				)
			)
		);

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('date', 'DateTime');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($newObject)->will($this->returnValue($classSchema));

		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->injectReflectionService($mockReflectionService);
		$persistenceSession->registerObject($newObject, '');

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('setProperties', 'emitPersistedObject', 'validateObject'));
		$backend->expects($this->once())->method('setProperties')->with($expectedProperties);
		$backend->injectPersistenceSession($persistenceSession);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $newObject, NULL);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function valueObjectsAreStoredOnceAndReusedAsNeeded() {
			// set up objects
		$A = new \TYPO3\TYPO3CR\Tests\Fixtures\AnEntity('A');
		$A->FLOW3_Persistence_Identifier = 'fakeUuidA';
		$B = new \TYPO3\TYPO3CR\Tests\Fixtures\AnEntity('B');
		$B->FLOW3_Persistence_Identifier = 'fakeUuidB';
		$V = new \TYPO3\TYPO3CR\Tests\Fixtures\AValue('V');
		$V->FLOW3_Persistence_Identifier = 'fakeHash';
		$A->add($V);
		$B->add($V);
		$B->add($V);
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($A);
		$aggregateRootObjects->attach($B);

		$expectedPropertiesForA = array(
			'identifier' => 'fakeUuidA',
			'classname' => 'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity',
			'properties' => array(
				'name' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'A',
				),
				'members' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'TYPO3\TYPO3CR\Tests\Fixtures\AValue',
							'index' => '0',
							'value' => array(
								'identifier' => 'fakeHash'
							)
						)
					)
				)
			)
		);
		$expectedPropertiesForB = array(
			'identifier' => 'fakeUuidB',
			'classname' => 'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity',
			'properties' => array(
				'name' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'B',
				),
				'members' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'TYPO3\TYPO3CR\Tests\Fixtures\AValue',
							'index' => '0',
							'value' => array(
								'identifier' => 'fakeHash'
							)
						),
						array(
							'type' => 'TYPO3\TYPO3CR\Tests\Fixtures\AValue',
							'index' => '1',
							'value' => array(
								'identifier' => 'fakeHash'
							)
						)
					)
				)
			)
		);

			// set up needed infrastructure
		$entityClassSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\TYPO3CR\Tests\Fixtures\AnEntity');
		$entityClassSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$entityClassSchema->addProperty('name', 'string');
		$entityClassSchema->addProperty('members', 'array');
		$valueClassSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\TYPO3CR\Tests\Fixtures\AValue');
		$valueClassSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);
		$valueClassSchema->addProperty('name', 'string');

		$classSchemata = array(
			'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity' => $entityClassSchema,
			'TYPO3\TYPO3CR\Tests\Fixtures\AValue' => $valueClassSchema
		);
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnCallback(function ($object) use ($classSchemata) {
			return $classSchemata[get_class($object)];
		}));

			// ... and here we go
		$mockSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('hasObject'));
		$mockSession->injectReflectionService($mockReflectionService);
		$mockSession->expects($this->exactly(9))->method('hasObject')->will($this->returnValue(FALSE));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('hasValueObjectRecord', 'createObjectRecord', 'setProperties', 'emitPersistedObject', 'validateObject'));
		$backend->expects($this->at(1))->method('createObjectRecord')->with($A)->will($this->returnValue('fakeUuidA'));
		$backend->expects($this->at(2))->method('hasValueObjectRecord')->with('fakeHash')->will($this->returnValue(FALSE));
		$backend->expects($this->at(4))->method('createObjectRecord')->with($V)->will($this->returnValue('fakeHash'));
		$backend->expects($this->at(7))->method('setProperties')->with($expectedPropertiesForA);
		$backend->expects($this->at(10))->method('createObjectRecord')->with($B)->will($this->returnValue('fakeUuidB'));
		$backend->expects($this->at(11))->method('setProperties')->with($expectedPropertiesForB);

		$backend->injectPersistenceSession($mockSession);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $A, 'fakeUuidA');
		$backend->_call('persistObject', $B, 'fakeUuidB');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function splObjectStorageIsStoredAsExpected() {
			// set up object
		$A = new \TYPO3\TYPO3CR\Tests\Fixtures\AnEntity('A');
		$A->FLOW3_Persistence_Identifier = 'fakeUuuidA';
		$B = new \TYPO3\TYPO3CR\Tests\Fixtures\AnEntity('B');
		$B->FLOW3_Persistence_Identifier = 'fakeUuuidB';
		$A->addObject($B);

		$expectedPropertiesForB = array(
			'identifier' => 'fakeUuidB',
			'classname' => 'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity',
			'properties' => array(
				'name' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'B'
				),
				'objects' => array(
					'type' => 'SplObjectStorage',
					'multivalue' => TRUE,
					'value' => array()
				)
			)
		);
		$expectedPropertiesForA = array(
			'identifier' => 'fakeUuidA',
			'classname' => 'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity',
			'properties' => array(
				'name' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'A'
				),
				'objects' => array(
					'type' => 'SplObjectStorage',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity',
							'index' => NULL,
							'value' => array('identifier' => 'fakeUuidB'),
						),
					)
				)
			)
		);

			// set up needed infrastructure
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\TYPO3CR\Tests\Fixtures\AnEntity');
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('name', 'string');
		$classSchema->addProperty('objects', 'SplObjectStorage');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnValue($classSchema));

		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->injectReflectionService($mockReflectionService);
		$persistenceSession->registerObject($A, 'fakeUuidA');
		$persistenceSession->registerObject($B, 'fakeUuidB');

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'setProperties', 'emitPersistedObject', 'validateObject'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->expects($this->never())->method('createObjectRecord');
		$backend->expects($this->at(1))->method('setProperties')->with($expectedPropertiesForB);
		$backend->expects($this->at(4))->method('setProperties')->with($expectedPropertiesForA);

		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $A, 'fakeUuidA');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function dateTimeInSplObjectStorageIsStoredAsExpected() {
			// set up object
		$A = new \TYPO3\TYPO3CR\Tests\Fixtures\AnEntity('A');
		$A->FLOW3_Persistence_Identifier = 'fakeUuidA';
		$dateTime = new \DateTime;
		$A->addObject($dateTime);

		$expectedPropertiesForA = array(
			'identifier' => 'fakeUuidA',
			'classname' => 'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity',
			'properties' => array(
				'name' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'A'
				),
				'objects' => array(
					'type' => 'SplObjectStorage',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'DateTime',
							'index' => NULL,
							'value' => $dateTime->getTimestamp()
						),
					)
				)
			)
		);

			// set up needed infrastructure
		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('TYPO3\TYPO3CR\Tests\Fixtures\AnEntity');
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('name', 'string');
		$classSchema->addProperty('objects', 'SplObjectStorage');

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($A)->will($this->returnValue($classSchema));

		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->registerObject($A, 'fakeUuidA');
		$persistenceSession->injectReflectionService($mockReflectionService);

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'setProperties', 'emitPersistedObject', 'validateObject'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->expects($this->never())->method('createObjectRecord');
		$backend->expects($this->once())->method('setProperties')->with($expectedPropertiesForA);

		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $A, 'fakeUuidA');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function entitiesDetachedFromSplObjectStorageAreRemovedFromRepository() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' {}');
		$object = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($object)->will($this->returnValue($classSchema));

		$objectStorage = new \SplObjectStorage();
		$previousObjectStorage = array(
			'type' => 'SplObjectStorage',
			'multivalue' => TRUE,
			'value' => array(
				array(
					'type' => $fullClassName,
					'index' => NULL,
					'value' => array(
						'identifier' => $identifier,
						'classname' => $fullClassName,
						'properties' => array()
					)
				)
			)
		);

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->expects($this->any())->method('getObjectByIdentifier')->with($identifier)->will($this->returnValue($object));
		$mockPersistenceSession->injectReflectionService($mockReflectionService);

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeEntity'));
		$backend->expects($this->once())->method('removeEntity')->with($object);
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_call('processSplObjectStorage', $objectStorage, $identifier, $previousObjectStorage);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function valueObjectsDetachedFromSplObjectStorageAreRemovedFromRepository() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' {}');
		$object = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($object)->will($this->returnValue($classSchema));

		$objectStorage = new \SplObjectStorage();
		$previousObjectStorage = array(
			'type' => 'SplObjectStorage',
			'multivalue' => TRUE,
			'value' => array(
				array(
					'type' => $fullClassName,
					'index' => NULL,
					'value' => array(
						'identifier' => $identifier,
						'classname' => $fullClassName,
						'properties' => array()
					)
				)
			)
		);

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->expects($this->any())->method('getObjectByIdentifier')->with($identifier)->will($this->returnValue($object));
		$mockPersistenceSession->injectReflectionService($mockReflectionService);

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeValueObject'));
		$backend->expects($this->once())->method('removeValueObject')->with($object);
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_call('processSplObjectStorage', $objectStorage, $identifier, $previousObjectStorage);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function entitiesRemovedFromArrayAreRemovedFromRepository() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' {}');
		$object = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($fullClassName)->will($this->returnValue($classSchema));

		$array = array();
		$previousArray = array(
			'type' => 'array',
			'multivalue' => TRUE,
			'value' => array(
				array(
					'type' => $fullClassName,
					'index' => 0,
					'value' => array(
						'identifier' => $identifier,
						'classname' => $fullClassName,
						'properties' => array()
					)
				)
			)
		);

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->injectReflectionService($mockReflectionService);
		$mockPersistenceSession->expects($this->any())->method('hasIdentifier')->with($identifier)->will($this->returnValue(TRUE));
		$mockPersistenceSession->expects($this->any())->method('getObjectByIdentifier')->with($identifier)->will($this->returnValue($object));

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeEntity'));
		$backend->expects($this->once())->method('removeEntity')->with($object);
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_call('processArray', $array, $identifier, $previousArray);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function entitiesRemovedFromNestedArrayAreRemovedFromRepository() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' {}');
		$object = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($fullClassName)->will($this->returnValue($classSchema));

		$array = array(array());
		$previousArray = array(
			'type' => 'array',
			'multivalue' => TRUE,
			'value' => array(
				array(
					'type' => 'array',
					'index' => 0,
					'value' => array(
						array(
							'type' => $fullClassName,
							'index' => 0,
							'value' => array(
								'identifier' => $identifier,
								'classname' => $fullClassName,
								'properties' => array()
							)
						)
					)
				)
			)
		);

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->injectReflectionService($mockReflectionService);
		$mockPersistenceSession->expects($this->any())->method('hasIdentifier')->with($identifier)->will($this->returnValue(TRUE));
		$mockPersistenceSession->expects($this->any())->method('getObjectByIdentifier')->with($identifier)->will($this->returnValue($object));

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeEntity'));
		$backend->expects($this->once())->method('removeEntity')->with($object);
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_call('processArray', $array, $identifier, $previousArray);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function valueObjectsRemovedFromArrayAreRemovedFromRepository() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $className;
		$identifier = \TYPO3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' {}');
		$object = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->with($fullClassName)->will($this->returnValue($classSchema));

		$array = array();
		$previousArray = array(
			'type' => 'array',
			'multivalue' => TRUE,
			'value' => array(
				array(
					'type' => $fullClassName,
					'index' => 0,
					'value' => array(
						'identifier' => $identifier,
						'classname' => $fullClassName,
						'properties' => array()
					)
				)
			)
		);

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->injectReflectionService($mockReflectionService);
		$mockPersistenceSession->expects($this->any())->method('hasIdentifier')->with($identifier)->will($this->returnValue(TRUE));
		$mockPersistenceSession->expects($this->any())->method('getObjectByIdentifier')->with($identifier)->will($this->returnValue($object));

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeValueObject'));
		$backend->expects($this->once())->method('removeValueObject')->with($object);
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_call('processArray', $array, $identifier, $previousArray);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function dateTimeAndLiteralsInArrayAreProcessedAsExpected() {
		$dateTime = new \DateTime();
		$array = array('foo' => 'bar', 'date' => $dateTime);

		$expected = array(
			array(
				'type' => 'string',
				'index' => 'foo',
				'value' => 'bar'
			),
			array(
				'type' => 'DateTime',
				'index' => 'date',
				'value' => $dateTime->getTimestamp()
			)
		);

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));

		$result = $backend->_call('processArray', $array, 'fakeUuid');
		$this->assertEquals($result, $expected);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processArrayHandlesNestedArrays() {
		$array = array('foo' => array('bar' => 'baz'));

		$storePropertyCallback = function() {
			if (
					func_get_arg(0) !== 'fakeUuid'
					|| !preg_match('/a[a-f0-9]{14}\.[a-f0-9]{8}/', func_get_arg(1))
					|| func_get_arg(2) !== array(
							'multivalue' => TRUE,
							'value' => array(array(
								'type' => 'string',
								'index' => 'bar',
								'value' => 'baz'
							))
						)
			) {
				throw new \PHPUnit_Framework_ExpectationFailedException('Did not receive expected params to storePropertyData call.');
			}
		};

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('storePropertyData'));
		$backend->expects($this->once())->method('storePropertyData')->will($this->returnCallback($storePropertyCallback));

		$result = $backend->_call('processArray', $array, 'fakeUuid');
		$this->assertEquals('array', $result[0]['type']);
		$this->assertEquals('foo', $result[0]['index']);
		$this->assertRegExp('/a[a-f0-9]{14}\.[a-f0-9]{8}/', $result[0]['value']);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Persistence\Exception
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processArrayRejectsNestedSplObjectStorageInsideArray() {
		$array = array(new \SplObjectStorage());

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));

		$backend->_call('processArray', $array, 'fakeUuid');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processDateTimeHandlesNullInputByReturningNull() {
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));

		$this->assertNull($backend->_call('processDateTime', NULL, 'fakeUuid'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processArrayHandlesNullInputByReturningNull() {
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));

		$this->assertNull($backend->_call('processArray', NULL, 'fakeUuid'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processSplObjectStorageHandlesNullInputByReturningNull() {
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));

		$this->assertNull($backend->_call('processSplObjectStorage', NULL, 'fakeUuid'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function aggregateRootObjectsFoundWhenPersistingThatAreNotAmongAggregateRootObjectsCollectedFromRepositoriesArePersisted() {
		$otherClassName = 'OtherClass' . uniqid();
		$fullOtherClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $otherClassName;
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $otherClassName . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
		}');
		$someClassName = 'SomeClass' . uniqid();
		$fullSomeClassName = 'TYPO3\\FLOW3\Persistence\\Tests\\' . $someClassName;
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $someClassName . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			public $property;
		}');
		$otherAggregateRootObject = new $fullOtherClassName();
		$someAggregateRootObject = new $fullSomeClassName();
		$someAggregateRootObject->property = $otherAggregateRootObject;

		$otherClassSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($otherClassName);
		$otherClassSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$otherClassSchema->setRepositoryClassName('Some\Repository');
		$someClassSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($someClassName);
		$someClassSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$someClassSchema->setRepositoryClassName('Some\Repository');
		$someClassSchema->addProperty('property', $fullOtherClassName);

		$classSchemata = array(
			$fullOtherClassName => $otherClassSchema,
			$fullSomeClassName => $someClassSchema
		);
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnCallback(function ($object) use ($classSchemata) {
			return $classSchemata[get_class($object)];
		}));

		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($someAggregateRootObject);

		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->injectReflectionService($mockReflectionService);
		$persistenceSession->registerObject($someAggregateRootObject, '');

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'setProperties', 'emitPersistedObject', 'validateObject'));
		$backend->expects($this->once())->method('createObjectRecord')->with($otherAggregateRootObject);
		$backend->injectPersistenceSession($persistenceSession);
		$backend->injectValidatorResolver($this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE));
		$backend->injectReflectionService($mockReflectionService);
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setPropertiesByParentEmitsExpectedSql() {
		$propertyData = array(
			'identifier' => 'identifier',
			'classname' => 'TYPO3\TYPO3CR\Tests\Fixtures\AnEntity',
			'properties' => array(
				'singleValue' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'propertyValue',
				),
				'multiValue' => array(
					'type' => 'SplObjectStorage',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'DateTime',
							'index' => NULL,
							'value' => 1
						),
						array(
							'type' => 'DateTime',
							'index' => NULL,
							'value' => 2
						)
					)
				),
				'keyedMultiValue' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => '\FooBar',
							'index' => 'one',
							'value' => '1234'
						),
						array(
							'type' => '\FooBar',
							'index' => 'two',
							'value' => '5678'
						)
					)
				),
				'singleNullValue' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => NULL,
				),
				'multiValueContainingNull' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'object',
							'index' => NULL,
							'value' => NULL
						)
					)
				)
			)
		);


		$mockInsertPropertyStatement = $this->getMock('PDOStatement');
		$mockInsertPropertyStatement->expects($this->at(0))->method('execute')->with(array('identifier', 'singleValue', 0, 'string'));

		$mockInsertDataStatement = $this->getMock('PDOStatement');
		$mockInsertDataStatement->expects($this->at(0))->method('execute')->with(array('identifier', 'singleValue', NULL, 'string', 'propertyValue'));
		$mockInsertDataStatement->expects($this->at(1))->method('execute')->with(array('identifier', 'multiValue', NULL, 'DateTime', '1'));
		$mockInsertDataStatement->expects($this->at(2))->method('execute')->with(array('identifier', 'multiValue', NULL, 'DateTime', '2'));
		$mockInsertDataStatement->expects($this->at(3))->method('execute')->with(array('identifier', 'keyedMultiValue', 'one', '\FooBar', '1234'));
		$mockInsertDataStatement->expects($this->at(4))->method('execute')->with(array('identifier', 'keyedMultiValue', 'two', '\FooBar', '5678'));
		$mockInsertDataStatement->expects($this->at(5))->method('execute')->with(array('identifier', 'singleNullValue', NULL, 'NULL'));
		$mockInsertDataStatement->expects($this->at(6))->method('execute')->with(array('identifier', 'multiValueContainingNull', NULL, 'NULL'));

		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->at(0))->method('prepare')->with('INSERT INTO "properties" ("parent", "name", "multivalue", "type") VALUES (?, ?, ?, ?)')->will($this->returnValue($mockInsertPropertyStatement));
		$mockPdo->expects($this->at(1))->method('prepare')->with('INSERT INTO "properties_data" ("parent", "name", "index", "type", "string") VALUES (?, ?, ?, ?, ?)')->will($this->returnValue($mockInsertDataStatement));
		$mockPdo->expects($this->at(2))->method('prepare')->with('INSERT INTO "properties_data" ("parent", "name", "index", "type", "datetime") VALUES (?, ?, ?, ?, ?)')->will($this->returnValue($mockInsertDataStatement));
		$mockPdo->expects($this->at(3))->method('prepare')->with('INSERT INTO "properties_data" ("parent", "name", "index", "type", "datetime") VALUES (?, ?, ?, ?, ?)')->will($this->returnValue($mockInsertDataStatement));
		$mockPdo->expects($this->at(4))->method('prepare')->with('INSERT INTO "properties_data" ("parent", "name", "index", "type", "object") VALUES (?, ?, ?, ?, ?)')->will($this->returnValue($mockInsertDataStatement));
		$mockPdo->expects($this->at(5))->method('prepare')->with('INSERT INTO "properties_data" ("parent", "name", "index", "type", "object") VALUES (?, ?, ?, ?, ?)')->will($this->returnValue($mockInsertDataStatement));
		$mockPdo->expects($this->at(6))->method('prepare')->with('INSERT INTO "properties_data" ("parent", "name", "index", "type") VALUES (?, ?, ?, ?)')->will($this->returnValue($mockInsertDataStatement));
		$mockPdo->expects($this->at(7))->method('prepare')->with('INSERT INTO "properties_data" ("parent", "name", "index", "type") VALUES (?, ?, ?, ?)')->will($this->returnValue($mockInsertDataStatement));

		$backendProxyClassName = $this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend');
		$backend = new $backendProxyClassName();
		$backend->injectPersistenceSession(new \TYPO3\FLOW3\Persistence\Generic\Session());
		$backend->_set('databaseHandle', $mockPdo);

		$backend->_call('setProperties', $propertyData, \TYPO3\FLOW3\Persistence\Generic\Backend\AbstractBackend::OBJECTSTATE_NEW);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removeEntitiesByParentEmitsExpectedSql() {
		$fooBarClassSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('FooBar');
		$fooBarClassSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$fooBarClassSchema->setRepositoryClassName('Some\Repository');
		$quuxClassSchema = new \TYPO3\FLOW3\Reflection\ClassSchema('Quux');
		$quuxClassSchema->setModelType(\TYPO3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);

		$classSchemata = array(
			'FooBar' => $fooBarClassSchema,
			'Quux' => $quuxClassSchema
		);
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnCallback(function ($className) use ($classSchemata) {
			return $classSchemata[$className];
		}));

		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('fakeUuid1'));
		$mockStatement->expects($this->once())->method('fetchAll')->will($this->onConsecutiveCalls(array(array('type' => 'FooBar', 'identifier' => 'heretostay'), array('type' => 'Quux', 'identifier' => 'goaway'))));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SELECT "identifier", "type" FROM "entities" WHERE "parent" = ?')->will($this->returnValue($mockStatement));

		$object1 = new \stdClass();
		$object2 = new \stdClass();
		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->injectReflectionService($mockReflectionService);
		$persistenceSession->registerObject($object1, 'fakeUuid1');
		$persistenceSession->registerObject($object2, 'goaway');

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeEntity', 'emitRemovedObject'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->expects($this->once())->method('removeEntity')->with($object2);
		$backend->_set('databaseHandle', $mockPdo);
		$backend->injectReflectionService($mockReflectionService);

		$backend->_call('removeEntitiesByParent', $object1);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removeValueObjectsByParentEmitsExpectedSql() {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('fakeUuid'));
		$mockStatement->expects($this->exactly(3))->method('fetchColumn')->will($this->onConsecutiveCalls('fakeHash1', 'fakeHash2', FALSE));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SELECT "identifier" FROM "valueobjects" WHERE "identifier" IN (SELECT DISTINCT "object" FROM "properties_data" WHERE "parent"=?)')->will($this->returnValue($mockStatement));

		$parent = new \stdClass();
		$object1 = new \stdClass();
		$object2 = new \stdClass();
		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->registerObject($parent, 'fakeUuid');
		$persistenceSession->registerObject($object1, 'fakeHash1');
		$persistenceSession->registerObject($object2, 'fakeHash2');

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('getValueObjectUsageCount', 'removeValueObject'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->expects($this->at(0))->method('getValueObjectUsageCount')->with($object1)->will($this->returnValue(2));
		$backend->expects($this->at(1))->method('getValueObjectUsageCount')->with($object2)->will($this->returnValue(1));
		$backend->expects($this->at(2))->method('removeValueObject')->with($object2);
		$backend->_set('databaseHandle', $mockPdo);

		$backend->_call('removeValueObjectsByParent', $parent);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removeEntityEmitsExpectedSqlAndRemovedObjectSignal() {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('fakeUuid'));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('DELETE FROM "entities" WHERE "identifier"=?')->will($this->returnValue($mockStatement));

		$object = new \stdClass();
		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->registerObject($object, 'fakeUuid');

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeEntitiesByParent', 'removeValueObjectsByParent', 'removePropertiesByParent', 'emitRemovedObject'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->expects($this->once())->method('emitRemovedObject')->with($object);
		$backend->expects($this->once())->method('removeEntitiesByParent')->with($object);
		$backend->expects($this->once())->method('removeValueObjectsByParent')->with($object);
		$backend->expects($this->once())->method('removePropertiesByParent')->with($object);
		$backend->_set('databaseHandle', $mockPdo);

		$backend->_call('removeEntity', $object);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removeValueObjectEmitsExpectedSqlAndRemovedObjectSignal($subject = 'fakeHash') {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('fakeHash'));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('DELETE FROM "valueobjects" WHERE "identifier"=?')->will($this->returnValue($mockStatement));

		$subject = new \stdClass();
		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->registerObject($subject, 'fakeHash');

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('removeValueObjectsByParent', 'removePropertiesByParent', 'emitRemovedObject'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->expects($this->once())->method('emitRemovedObject')->with($subject);
		$backend->expects($this->once())->method('removeValueObjectsByParent')->with($subject);
		$backend->expects($this->once())->method('removePropertiesByParent')->with($subject);
		$backend->_set('databaseHandle', $mockPdo);

		$backend->_call('removeValueObject', $subject);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getValueObjectUsageCountEmitsExpectedSql($subject = 'fakeHash') {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('fakeHash'));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SELECT COUNT(DISTINCT "parent") FROM "properties_data" WHERE "object"=?')->will($this->returnValue($mockStatement));

		$subject = new \stdClass();
		$persistenceSession = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$persistenceSession->registerObject($subject, 'fakeHash');

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('dummy'));
		$backend->injectPersistenceSession($persistenceSession);
		$backend->_set('databaseHandle', $mockPdo);

		$backend->_call('getValueObjectUsageCount', $subject);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectCountByQueryDelegatesQueryBuildingAndUsesResultForDatabaseQuery() {
		$mockQuery = $this->getMock('TYPO3\FLOW3\Persistence\QueryInterface');
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('PARAMETERS'));
		$mockStatement->expects($this->once())->method('fetchColumn')->will($this->returnValue(3));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SQLSTRING')->will($this->returnValue($mockStatement));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('buildQuery', 'processObjectRecords'));
		$backend->expects($this->once())->method('buildQuery')->with($mockQuery, TRUE)->will($this->returnValue(array('sql' =>  'SQLSTRING', 'parameters' => array('PARAMETERS'))));
		$backend->_set('databaseHandle', $mockPdo);
		$this->assertEquals(3, $backend->_call('getObjectCountByQuery', $mockQuery));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectDataInitializesKnownRecordsArray() {
		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('_getObjectData'));
		$backend->_set('knownRecords', FALSE);
		$backend->_call('getObjectDataByIdentifier', '');
		$this->assertEquals(array(), $backend->_get('knownRecords'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function _getObjectDataFetchesIdentifierAndTypeForEntities() {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('e2408ea7-9742-48d6-9aab-df85d78120ae'));
		$mockStatement->expects($this->once())->method('fetchAll')->will($this->returnValue(array('QUERY_RESULT')));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SELECT "identifier", "type" AS "classname" FROM "entities" WHERE "identifier"=?')->will($this->returnValue($mockStatement));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('processObjectRecords'));
		$backend->expects($this->once())->method('processObjectRecords')->will($this->returnValue(array()));
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_call('_getObjectData', 'e2408ea7-9742-48d6-9aab-df85d78120ae');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function _getObjectDataFetchesIdentifierAndTypeForValueObjects() {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('fakeHash'));
		$mockStatement->expects($this->once())->method('fetchAll')->will($this->returnValue(array('QUERY_RESULT')));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SELECT "identifier", "type" AS "classname" FROM "valueobjects" WHERE "identifier"=?')->will($this->returnValue($mockStatement));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('processObjectRecords'));
		$backend->expects($this->once())->method('processObjectRecords')->will($this->returnValue(array()));
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_call('_getObjectData', 'fakeHash');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function _getObjectDataCallsProcessObjectRecordsAndReturnsResult() {
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('fetchAll')->will($this->returnValue(array('QUERY_RESULT')));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SELECT "identifier", "type" AS "classname" FROM "valueobjects" WHERE "identifier"=?')->will($this->returnValue($mockStatement));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('processObjectRecords'));
		$backend->expects($this->once())->method('processObjectRecords')->with(array('QUERY_RESULT'))->will($this->returnValue(array('RESULT')));
		$backend->_set('databaseHandle', $mockPdo);
		$this->assertEquals('RESULT', $backend->_call('_getObjectData', 'fakeHash'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectDataByQueryInitializesKnownRecordsArray() {
		$mockQuery = $this->getMock('TYPO3\FLOW3\Persistence\QueryInterface');
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('fetchAll')->will($this->returnValue(array('QUERY_RESULT')));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->will($this->returnValue($mockStatement));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('buildQuery', 'processObjectRecords'));
		$backend->expects($this->once())->method('processObjectRecords')->will($this->returnValue(array()));
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_set('knownRecords', FALSE);
		$backend->_call('getObjectDataByQuery', $mockQuery);
		$this->assertEquals(array(), $backend->_get('knownRecords'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectDataByQueryDelegatesQueryBuildingAndUsesResultForDatabaseQuery() {
		$mockQuery = $this->getMock('TYPO3\FLOW3\Persistence\QueryInterface');
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute')->with(array('PARAMETERS'));
		$mockStatement->expects($this->once())->method('fetchAll')->will($this->returnValue(array('QUERY_RESULT')));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->with('SQLSTRING')->will($this->returnValue($mockStatement));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('buildQuery', 'processObjectRecords'));
		$backend->expects($this->once())->method('processObjectRecords')->will($this->returnValue(array()));
		$backend->expects($this->once())->method('buildQuery')->with($mockQuery)->will($this->returnValue(array('sql' =>  'SQLSTRING', 'parameters' => array('PARAMETERS'))));
		$backend->_set('databaseHandle', $mockPdo);
		$backend->_call('getObjectDataByQuery', $mockQuery);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectDataByQueryCallsProcessObjectRecordsWithQueryResult() {
		$mockQuery = $this->getMock('TYPO3\FLOW3\Persistence\QueryInterface');
		$mockStatement = $this->getMock('PDOStatement');
		$mockStatement->expects($this->once())->method('execute');
		$mockStatement->expects($this->once())->method('fetchAll')->will($this->returnValue(array('QUERY_RESULT')));
		$mockPdo = $this->getMock('TYPO3\FLOW3\Tests\Unit\Persistence\Fixture\PdoInterface');
		$mockPdo->expects($this->once())->method('prepare')->will($this->returnValue($mockStatement));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('buildQuery', 'processObjectRecords'));
		$backend->expects($this->once())->method('processObjectRecords')->with(array('QUERY_RESULT'))->will($this->returnValue(array('OBJECTS')));
		$backend->_set('databaseHandle', $mockPdo);
		$this->assertEquals(array('OBJECTS'), $backend->_call('getObjectDataByQuery', $mockQuery));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectAsksForValidatorsForNewAndDirtyObjects() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\FLOW3\Persistence\Tests\\' . $className;
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			protected $foo;
		}');
		$newObject = new $fullClassName();
		$oldObject = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->addProperty('foo', 'string');
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnValue($classSchema));

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->injectReflectionService($mockReflectionService);
		$mockPersistenceSession->expects($this->exactly(4))->method('hasObject')->will($this->onConsecutiveCalls(FALSE, FALSE, TRUE, TRUE));
		$mockPersistenceSession->expects($this->exactly(2))->method('isDirty')->will($this->onConsecutiveCalls(TRUE, TRUE));

		$mockValidatorConjunction = $this->getMock('TYPO3\FLOW3\Validation\Validator\ConjunctionValidator', array(), array(), '', FALSE);
		$validationResults = $this->getMock('TYPO3\FLOW3\Error\Result');
		$validationResults->expects($this->exactly(2))->method('hasErrors')->will($this->returnValue(FALSE));
		$mockValidatorConjunction->expects($this->any())->method('validate')->will($this->returnValue($validationResults));
		$mockValidatorResolver = $this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE);
		$mockValidatorResolver->expects($this->exactly(2))->method('getBaseValidatorConjunction')->with($fullClassName)->will($this->returnValue($mockValidatorConjunction));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'emitPersistedObject', 'setProperties'));
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectValidatorResolver($mockValidatorResolver);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $newObject, NULL);
		$backend->_call('persistObject', $oldObject, NULL);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Persistence\Generic\Exception\ObjectValidationFailedException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectThrowsExceptionOnValidationFailureForNewObjects() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\FLOW3\Persistence\Tests\\' . $className;
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			protected $foo;
		}');
		$newObject = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->addProperty('foo', 'string');
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnValue($classSchema));

		$validationResults = $this->getMock('TYPO3\FLOW3\Error\Result');
		$validationResults->expects($this->once())->method('hasErrors')->will($this->returnValue(TRUE));
		$validationResults->expects($this->any())->method('getErrors')->will($this->returnValue(new \TYPO3\FLOW3\Error\Error('error', 1234)));

		$mockValidator = $this->getMock('TYPO3\FLOW3\Validation\Validator\ValidatorInterface');
		$mockValidator->expects($this->once())->method('validate')->will($this->returnValue($validationResults));

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->injectReflectionService($mockReflectionService);
		$mockPersistenceSession->expects($this->exactly(2))->method('hasObject')->will($this->returnValue(FALSE));

		$mockValidatorResolver = $this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE);
		$mockValidatorResolver->expects($this->once())->method('getBaseValidatorConjunction')->with($fullClassName)->will($this->returnValue($mockValidator));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'emitPersistedObject', 'setProperties'));
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectValidatorResolver($mockValidatorResolver);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $newObject, NULL);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Persistence\Generic\Exception\ObjectValidationFailedException
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectThrowsExceptionOnValidationFailureForOldObjects() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'TYPO3\FLOW3\Persistence\Tests\\' . $className;
		eval('namespace TYPO3\\FLOW3\Persistence\\Tests; class ' . $className . ' implements \TYPO3\FLOW3\Persistence\Aspect\PersistenceMagicInterface {
			protected $foo;
		}');
		$oldObject = new $fullClassName();

		$classSchema = new \TYPO3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->addProperty('foo', 'string');
		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassSchema')->will($this->returnValue($classSchema));

		$validationResults = $this->getMock('TYPO3\FLOW3\Error\Result');
		$validationResults->expects($this->once())->method('hasErrors')->will($this->returnValue(TRUE));
		$validationResults->expects($this->any())->method('getErrors')->will($this->returnValue(new \TYPO3\FLOW3\Error\Error('error', 1234)));

		$mockValidator = $this->getMock('TYPO3\FLOW3\Validation\Validator\ValidatorInterface');
		$mockValidator->expects($this->once())->method('validate')->will($this->returnValue($validationResults));

		$mockPersistenceSession = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session');
		$mockPersistenceSession->injectReflectionService($mockReflectionService);
		$mockPersistenceSession->expects($this->exactly(2))->method('hasObject')->will($this->returnValue(TRUE));
		$mockPersistenceSession->expects($this->once())->method('isDirty')->will($this->returnValue(TRUE));

		$mockValidatorResolver = $this->getMock('TYPO3\FLOW3\Validation\ValidatorResolver', array(), array(), '', FALSE);
		$mockValidatorResolver->expects($this->once())->method('getBaseValidatorConjunction')->with($fullClassName)->will($this->returnValue($mockValidator));

		$backend = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Backend\GenericPdo\Backend'), array('createObjectRecord', 'emitPersistedObject', 'setProperties'));
		$backend->injectPersistenceSession($mockPersistenceSession);
		$backend->injectValidatorResolver($mockValidatorResolver);
		$backend->injectReflectionService($mockReflectionService);
		$backend->_set('visitedDuringPersistence', new \SplObjectStorage());
		$backend->_call('persistObject', $oldObject, NULL);
	}

}

?>