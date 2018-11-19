<?php

namespace Thurly\Main\UrlPreview;

use Thurly\Main\ArgumentException;
use Thurly\Main\Web\Uri;

class ParserChain
{
	/** @var array */
	protected static $metadataParsers =  array(
		'Thurly\Main\UrlPreview\Parser\OpenGraph',
		'Thurly\Main\UrlPreview\Parser\SchemaOrg',
		'Thurly\Main\UrlPreview\Parser\Oembed',
		'Thurly\Main\UrlPreview\Parser\Common'
	);

	/**
	 * @var array Key is host, value - parser class name
	 */
	protected  static $metadataParsersByHost = array(
		'vk.com' => 'Thurly\Main\UrlPreview\Parser\Vk',
		'www.facebook.com' => 'Thurly\Main\UrlPreview\Parser\Facebook',
		'www.instagram.com' => 'Thurly\Main\UrlPreview\Parser\Instagram',
	);

	/**
	 * @param Uri $uri
	 * @return array
	 */
	protected static function getParserChain(Uri $uri)
	{
		$result = array();
		if(isset(static::$metadataParsersByHost[$uri->getHost()]))
		{
			$result[] = static::$metadataParsersByHost[$uri->getHost()];
		}

		$result = array_merge($result, static::$metadataParsers);

		return $result;
	}

	/**
	 * Executes chain of parsers, passing them $document
	 *
	 * @param HtmlDocument $document
	 */
	public static function extractMetadata(HtmlDocument $document)
	{
		foreach(static::getParserChain($document->getUri()) as $parserClassName)
		{
			/** @var \Thurly\Main\UrlPreview\Parser $parser */
			if(class_exists($parserClassName))
			{
				$parser = new $parserClassName();
				if(is_a($parser, '\Thurly\Main\UrlPreview\Parser'))
				{
					$parser->handle($document);
				}
			}
			if($document->checkMetadata())
			{
				break;
			}
		}
	}

	/**
	 * Registers special parser for host
	 *
	 * @param string $host
	 * @param string $parserClassName Parser class must extend \Thurly\Main\UrlPreview\Parser
	 * @throws ArgumentException
	 */
	public static function registerMetadataParser($host, $parserClassName)
	{
		if(!class_exists($parserClassName) || !is_subclass_of($parserClassName, '\Thurly\Main\UrlPreview\Parser'))
		{
			throw new ArgumentException('Parser class must extend \Thurly\Main\UrlPreview\Parser', 'parserClassName');
		}

		static::$metadataParsersByHost[$host] = $parserClassName;
	}
}