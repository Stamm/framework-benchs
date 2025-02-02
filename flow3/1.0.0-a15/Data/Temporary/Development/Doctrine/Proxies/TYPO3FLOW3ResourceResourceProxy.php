<?php

namespace TYPO3\FLOW3\Persistence\Doctrine\Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class TYPO3FLOW3ResourceResourceProxy extends \TYPO3\FLOW3\Resource\Resource implements \Doctrine\ORM\Proxy\Proxy
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
	 * Sets the filename
	 *
	 * @param string $filename
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
    public function setFileName($filename)
    {
        $this->__load();
        return parent::setFileName($filename);
    }

/**
	 * Gets the filename
	 *
	 * @return string The filename
	 * @author Robert Lemke <robert@typo3.org>
	 */
    public function getFileName()
    {
        $this->__load();
        return parent::getFileName();
    }

/**
	 * Returns the file extension used for this resource
	 *
	 * @return string The file extension used for this file
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
    public function getFileExtension()
    {
        $this->__load();
        return parent::getFileExtension();
    }

/**
	 * Returns the mime type for this resource
	 *
	 * @return string The mime type
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
    public function getMimeType()
    {
        $this->__load();
        return parent::getMimeType();
    }

/**
	 * Sets the resource pointer
	 *
	 * @param \TYPO3\FLOW3\Resource\ResourcePointer $resourcePointer
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
    public function setResourcePointer(\TYPO3\FLOW3\Resource\ResourcePointer $resourcePointer)
    {
        $this->__load();
        return parent::setResourcePointer($resourcePointer);
    }

/**
	 * Returns the resource pointer
	 *
	 * @return \TYPO3\FLOW3\Resource\ResourcePointer $resourcePointer
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
    public function getResourcePointer()
    {
        $this->__load();
        return parent::getResourcePointer();
    }

/**
	 * Sets the publishing configuration for this resource
	 *
	 * @param \TYPO3\FLOW3\Resource\Publishing\PublishingConfigurationInterface $publishingConfiguration The publishing configuration
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
    public function setPublishingConfiguration(\TYPO3\FLOW3\Resource\Publishing\PublishingConfigurationInterface $publishingConfiguration = NULL)
    {
        $this->__load();
        return parent::setPublishingConfiguration($publishingConfiguration);
    }

/**
	 * Returns the publishing configuration for this resource
	 *
	 * @return \TYPO3\FLOW3\Resource\Publishing\PublishingConfigurationInterface The publishing configuration
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
    public function getPublishingConfiguration()
    {
        $this->__load();
        return parent::getPublishingConfiguration();
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