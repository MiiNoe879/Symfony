<?php

namespace Thurly\Main\PhoneNumber\Tools;

class MetadataBuilder
{
	protected $fileName;
	protected $parser;

	public function __construct($fileName)
	{
		if(!\Thurly\Main\IO\File::isFileExists($fileName))
			throw new \Thurly\Main\ArgumentException('File is not found');

		$this->fileName = $fileName;
		$this->parser = new \Thurly\Main\PhoneNumber\Tools\GoogleMetadata\Root();
	}

	public function build()
	{
		$xmlReader = new \XMLReader();
		if ($xmlReader->open('file://'.$this->fileName) === false)
		{
			throw new \Thurly\Main\SystemException("XMLReader could not open URI");
		}

		// looking for the root element
		while ($xmlReader->read() && $xmlReader->nodeType !== \XMLReader::ELEMENT) ;

		$parsedData = $this->parser->parseElement($xmlReader, '/');
		$xmlReader->close();
		unset($xmlReader);
		return $parsedData['ROOT']['territory'];
	}

}