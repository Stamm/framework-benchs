<?php
/**
 * CJavaScript helper class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CJavaScript is a helper class containing JavaScript-related handling functions.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CJavaScript.php 1783 2010-02-01 21:12:10Z qiang.xue $
 * @package system.web.helpers
 * @since 1.0
 */
class CJavaScript
{
	/**
	 * Quotes a javascript string.
	 * After processing, the string can be safely enclosed within a pair of
	 * quotation marks and serve as a javascript string.
	 * @param string string to be quoted
	 * @param boolean whether this string is used as a URL
	 * @return string the quoted string
	 */
	public static function quote($js,$forUrl=false)
	{
		if($forUrl)
			return strtr($js,array('%'=>'%25',"\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\','</'=>'<\/'));
		else
			return strtr($js,array("\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\','</'=>'<\/'));
	}

	/**
	 * Encodes a PHP variable into javascript representation.
	 *
	 * Example:
	 * <pre>
	 * $options=array('key1'=>true,'key2'=>123,'key3'=>'value');
	 * echo CJavaScript::encode($options);
	 * // The following javascript code would be generated:
	 * // {'key1':true,'key2':123,'key3':'value'}
	 * </pre>
	 *
	 * For highly complex data structures use {@link jsonEncode} and {@link jsonDecode}
	 * to serialize and unserialize.
	 *
	 * @param mixed PHP variable to be encoded
	 * @return string the encoded string
	 */
	public static function encode($value)
	{
		if(is_string($value))
		{
			if(strpos($value,'js:')===0)
				return substr($value,3);
			else
				return "'".self::quote($value)."'";
		}
		else if($value===null)
			return 'null';
		else if(is_bool($value))
			return $value?'true':'false';
		else if(is_integer($value))
			return "$value";
		else if(is_float($value))
		{
			if($value===-INF)
				return 'Number.NEGATIVE_INFINITY';
			else if($value===INF)
				return 'Number.POSITIVE_INFINITY';
			else
				return "$value";
		}
		else if(is_object($value))
			return self::encode(get_object_vars($value));
		else if(is_array($value))
		{
			$es=array();
			if(($n=count($value))>0 && array_keys($value)!==range(0,$n-1))
			{
				foreach($value as $k=>$v)
					$es[]="'".self::quote($k)."':".self::encode($v);
				return '{'.implode(',',$es).'}';
			}
			else
			{
				foreach($value as $v)
					$es[]=self::encode($v);
				return '['.implode(',',$es).']';
			}
		}
		else
			return '';
	}

	/**
	 * Returns the JSON representation of the PHP data.
	 * @param mixed the data to be encoded
	 * @return string the JSON representation of the PHP data.
	 */
	public static function jsonEncode($data)
	{
		if(function_exists('json_encode'))
			return json_encode($data);
		else
		{
			return CJSON::encode($data);
		}
	}

	/**
	 * Decodes a JSON string.
	 * @param string the data to be decoded
	 * @param boolean whether to use associative array to represent object data
	 * @return mixed the decoded PHP data
	 */
	public static function jsonDecode($data,$useArray=true)
	{
		if(function_exists('json_decode'))
			return json_decode($data,$useArray);
		else
			return CJSON::decode($data,$useArray);
	}
}
