<?php

namespace TYPO3\FLOW3\Persistence\Doctrine\Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class TYPO3PartyDomainModelElectronicAddressProxy extends \TYPO3\Party\Domain\Model\ElectronicAddress implements \Doctrine\ORM\Proxy\Proxy
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
	 * Sets the identifier (= the value) of this electronic address.
	 *
	 * Example: john@example.com
	 *
	 * @param string $identifier The identifier
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function setIdentifier($identifier)
    {
        $this->__load();
        return parent::setIdentifier($identifier);
    }

/**
	 * Returns the identifier (= the value) of this electronic address.
	 *
	 * @return string The identifier
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function getIdentifier()
    {
        $this->__load();
        return parent::getIdentifier();
    }

/**
	 * Returns the type of this electronic address
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function getType()
    {
        $this->__load();
        return parent::getType();
    }

/**
	 * Sets the type of this electronic address
	 *
	 * @param string $type If possible, use one of the TYPE_ constants
	 * @return void
 	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function setType($type)
    {
        $this->__load();
        return parent::setType($type);
    }

/**
	 * Returns the usage of this electronic address
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function getUsage()
    {
        $this->__load();
        return parent::getUsage();
    }

/**
	 * Sets the usage of this electronic address
	 *
	 * @param string $usage If possible, use one of the USAGE_ constants
	 * @return void
	 * @author Robert Lemke
	 */
    public function setUsage($usage)
    {
        $this->__load();
        return parent::setUsage($usage);
    }

/**
	 * Sets the approved status
	 *
	 * @param boolean $approved If this address has been approved or not
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function setApproved($approved)
    {
        $this->__load();
        return parent::setApproved($approved);
    }

/**
	 * Tells if this address has been approved
	 *
	 * @return boolean TRUE if the address has been approved, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function isApproved()
    {
        $this->__load();
        return parent::isApproved();
    }

/**
	 * An alias for getIdentifier()
	 *
	 * @return string The identifier of this electronic address
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