<?php
/**
 * CWebService class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebService encapsulates SoapServer and provides a WSDL-based web service.
 *
 * PHP SOAP extension is required.
 *
 * CWebService makes use of {@link CWsdlGenerator} and can generate the WSDL
 * on-the-fly without requiring you to write complex WSDL.
 *
 * To generate the WSDL based on doc comment blocks in the service provider class,
 * call {@link generateWsdl} or {@link renderWsdl}. To process the web service
 * requests, call {@link run}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CWebService.php 1597 2009-12-17 22:13:29Z qiang.xue $
 * @package system.web.services
 * @since 1.0
 */
class CWebService extends CComponent
{
	const SOAP_ERROR=1001;
	/**
	 * @var string|object the web service provider class or object.
	 * If specified as a class name, it can be a path alias.
	 */
	public $provider;
	/**
	 * @var string the URL for WSDL. This is required by {@link run()}.
	 */
	public $wsdlUrl;
	/**
	 * @var string the URL for the Web service. This is required by {@link generateWsdl()} and {@link renderWsdl()}.
	 */
	public $serviceUrl;
	/**
	 * @var integer number of seconds that the generated WSDL can remain valid in cache. Defaults to 0, meaning no caching.
	 */
	public $wsdlCacheDuration=0;
	/**
	 * @var string the ID of the cache application component that is used to cache the generated WSDL.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching WSDL.
	 * @since 1.0.10
	 */
	public $cacheID='cache';
	/**
	 * @var string encoding of the Web service. Defaults to 'UTF-8'.
	 */
	public $encoding='UTF-8';
	/**
	 * @var array a list of classes that are declared as complex types in WSDL.
	 * This should be an array with WSDL types as keys and names of PHP classes as values.
	 * A PHP class can also be specified as a path alias.
	 * @see http://www.php.net/manual/en/function.soap-soapserver-construct.php
	 */
	public $classMap=array();
	/**
	 * @var string actor of the SOAP service. Defaults to null, meaning not set.
	 */
	public $actor;
	/**
	 * @var string SOAP version (e.g. '1.1' or '1.2'). Defaults to null, meaning not set.
	 */
	public $soapVersion;
	/**
	 * @var integer the persistence mode of the SOAP server.
	 * @see http://www.php.net/manual/en/function.soap-soapserver-setpersistence.php
	 */
	public $persistence;

	private $_method;


	/**
	 * Constructor.
	 * @param mixed the web service provider class name or object
	 * @param string the URL for WSDL. This is required by {@link run()}.
	 * @param string the URL for the Web service. This is required by {@link generateWsdl()} and {@link renderWsdl()}.
	 */
	public function __construct($provider,$wsdlUrl,$serviceUrl)
	{
		$this->provider=$provider;
		$this->wsdlUrl=$wsdlUrl;
		$this->serviceUrl=$serviceUrl;
	}

	/**
	 * The PHP error handler.
	 * @param CErrorEvent the PHP error event
	 */
	public function handleError($event)
	{
		$event->handled=true;
		$message=$event->message;
		if(YII_DEBUG)
		{
			$trace=debug_backtrace();
			if(isset($trace[2]) && isset($trace[2]['file']) && isset($trace[2]['line']))
				$message.=' ('.$trace[2]['file'].':'.$trace[2]['line'].')';
		}
		throw new CException($message,self::SOAP_ERROR);
	}

	/**
	 * Generates and displays the WSDL as defined by the provider.
	 * @see generateWsdl
	 */
	public function renderWsdl()
	{
		$wsdl=$this->generateWsdl();
		header('Content-Type: text/xml;charset='.$this->encoding);
		header('Content-Length: '.strlen($wsdl));
		echo $wsdl;
	}

	/**
	 * Generates the WSDL as defined by the provider.
	 * The cached version may be used if the WSDL is found valid in cache.
	 * @return string the generated WSDL
	 * @see wsdlCacheDuration
	 */
	public function generateWsdl()
	{
		$providerClass=is_object($this->provider) ? get_class($this->provider) : Yii::import($this->provider,true);
		if($this->wsdlCacheDuration>0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key='Yii.CWebService.'.$providerClass.$this->serviceUrl.$this->encoding;
			if(($wsdl=$cache->get($key))!==false)
				return $wsdl;
		}
		$generator=new CWsdlGenerator;
		$wsdl=$generator->generateWsdl($providerClass,$this->serviceUrl,$this->encoding);
		if(isset($key))
			$cache->set($key,$wsdl,$this->wsdlCacheDuration);
		return $wsdl;
	}

	/**
	 * Handles the web service request.
	 */
	public function run()
	{
		header('Content-Type: text/xml;charset='.$this->encoding);
		if(YII_DEBUG)
			ini_set("soap.wsdl_cache_enabled",0);
		$server=new SoapServer($this->wsdlUrl,$this->getOptions());
		Yii::app()->attachEventHandler('onError',array($this,'handleError'));
		try
		{
			if($this->persistence!==null)
				$server->setPersistence($this->persistence);
			if(is_string($this->provider))
				$provider=Yii::createComponent($this->provider);
			else
				$provider=$this->provider;

			if(method_exists($server,'setObject'))
				$server->setObject($provider);
			else
				$server->setClass('CSoapObjectWrapper',$provider);

			if($provider instanceof IWebServiceProvider)
			{
				if($provider->beforeWebMethod($this))
				{
					$server->handle();
					$provider->afterWebMethod($this);
				}
			}
			else
				$server->handle();
		}
		catch(Exception $e)
		{
			if($e->getCode()===self::SOAP_ERROR) // a PHP error
				$message=$e->getMessage();
			else
			{
				$message=$e->getMessage().' ('.$e->getFile().':'.$e->getLine().')';
				// only log for non-PHP-error case because application's error handler already logs it
				// php <5.2 doesn't support string conversion auto-magically
				Yii::log($e->__toString(),CLogger::LEVEL_ERROR,'application');
			}
			if(YII_DEBUG)
				$message.="\n".$e->getTraceAsString();
			$server->fault(get_class($e),$message);
		}
	}

	/**
	 * @return string the currently requested method name. Empty if no method is being requested.
	 */
	public function getMethodName()
	{
		if($this->_method===null)
		{
			if(isset($HTTP_RAW_POST_DATA))
				$request=$HTTP_RAW_POST_DATA;
			else
				$request=file_get_contents('php://input');
			if(preg_match('/<.*?:Body[^>]*>\s*<.*?:(\w+)/mi',$request,$matches))
				$this->_method=$matches[1];
			else
				$this->_method='';
		}
		return $this->_method;
	}

	/**
	 * @return array options for creating SoapServer instance
	 * @see http://www.php.net/manual/en/function.soap-soapserver-construct.php
	 */
	protected function getOptions()
	{
		$options=array();
		if($this->soapVersion==='1.1')
			$options['soap_version']=SOAP_1_1;
		else if($this->soapVersion==='1.2')
			$options['soap_version']=SOAP_1_2;
		if($this->actor!==null)
			$options['actor']=$this->actor;
		$options['encoding']=$this->encoding;
		foreach($this->classMap as $type=>$className)
		{
			$className=Yii::import($className,true);
			if(is_int($type))
				$type=$className;
			$options['classmap'][$type]=$className;
		}
		return $options;
	}
}


/**
 * CSoapObjectWrapper is a wrapper class internally used when SoapServer::setObject() is not defined.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CWebService.php 1597 2009-12-17 22:13:29Z qiang.xue $
 * @package system.web.services
 * @since 1.0.5
 */
class CSoapObjectWrapper
{
	/**
	 * @var object the service provider
	 */
	public $object=null;

	/**
	 * Constructor.
	 * @param object the service provider
	 */
	public function __construct($object)
	{
		$this->object=$object;
	}

	/**
	 * PHP __call magic method.
	 * This method calls the service provider to execute the actual logic.
	 * @param string method name
	 * @param array method arguments
	 * @return mixed method return value
	 */
	public function __call($name,$arguments)
	{
		return call_user_func_array(array($this->object,$name),$arguments);
	}
}

