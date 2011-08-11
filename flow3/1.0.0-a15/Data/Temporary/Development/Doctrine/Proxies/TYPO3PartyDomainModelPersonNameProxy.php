<?php

namespace TYPO3\FLOW3\Persistence\Doctrine\Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class TYPO3PartyDomainModelPersonNameProxy extends \TYPO3\Party\Domain\Model\PersonName implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    /** @private */
    public function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__to avoid infinite recursion
                // but before loading to emulate what ClassMetadata::newInstance()
                // provides.
                $this->__wakeup();
            }

            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }
    
    
/**
	 * Autogenerated Proxy Method
	 */
    public function __wakeup()
    {
        $this->__load();
        return parent::__wakeup();
    }

/**
	 * Autogenerated Proxy Method
	 */
    public function FLOW3_AOP_Proxy_invokeJoinPoint(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint)
    {
        $this->__load();
        return parent::FLOW3_AOP_Proxy_invokeJoinPoint($joinPoint);
    }

/**
	 * Setter for firstName
	 *
	 * @param string $firstName
	 * @return void
	 */
    public function setFirstName($firstName)
    {
        $this->__load();
        return parent::setFirstName($firstName);
    }

/**
	 * Setter for middleName
	 *
	 * @param string $middleName
	 * @return void
	 */
    public function setMiddleName($middleName)
    {
        $this->__load();
        return parent::setMiddleName($middleName);
    }

/**
	 * Setter for lastName
	 *
	 * @param string $lastName
	 * @return void
	 */
    public function setLastName($lastName)
    {
        $this->__load();
        return parent::setLastName($lastName);
    }

/**
	 * Setter for title
	 *
	 * @param string $title
	 * @return void
	 */
    public function setTitle($title)
    {
        $this->__load();
        return parent::setTitle($title);
    }

/**
	 * Setter for otherName
	 *
	 * @param string $otherName
	 * @return void
	 */
    public function setOtherName($otherName)
    {
        $this->__load();
        return parent::setOtherName($otherName);
    }

/**
	 * Setter for alias
	 *
	 * @param string $alias
	 * @return void
	 */
    public function setAlias($alias)
    {
        $this->__load();
        return parent::setAlias($alias);
    }

/**
	 * Getter for firstName
	 *
	 * @return string
	 */
    public function getFirstName()
    {
        $this->__load();
        return parent::getFirstName();
    }

/**
	 * Getter for middleName
	 *
	 * @return string
	 */
    public function getMiddleName()
    {
        $this->__load();
        return parent::getMiddleName();
    }

/**
	 * Getter for lastName
	 *
	 * @return string
	 */
    public function getLastName()
    {
        $this->__load();
        return parent::getLastName();
    }

/**
	 * Getter for title
	 *
	 * @return string
	 */
    public function getTitle()
    {
        $this->__load();
        return parent::getTitle();
    }

/**
	 * Getter for otherName
	 *
	 * @return string
	 */
    public function getOtherName()
    {
        $this->__load();
        return parent::getOtherName();
    }

/**
	 * Getter for alias
	 *
	 * @return string
	 */
    public function getAlias()
    {
        $this->__load();
        return parent::getAlias();
    }

/**
	 * Returns the full name, e.g. "Mr. PhD John W. Doe"
	 *
	 * @return string The full person name
	 */
    public function getFullName()
    {
        $this->__load();
        return parent::getFullName();
    }

/**
	 * An alias for getFullName()
	 *
	 * @return string The full person name
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function __toString()
    {
        $this->__load();
        return parent::__toString();
    }


    public function __sleep()
    {
        return array_merge(array('__isInitialized__'), parent::__sleep());
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields AS $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        parent::__clone();
    }
}