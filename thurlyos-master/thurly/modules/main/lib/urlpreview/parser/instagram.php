<?php

namespace Thurly\Main\UrlPreview\Parser;

use Thurly\Main\UrlPreview\HtmlDocument;

class Instagram extends Oembed
{
	protected function detectOembedLink(HtmlDocument $document)
	{
		$this->metadataType = 'json';
		$this->metadataUrl = 'https://api.instagram.com/oembed/?url='.$document->getUri()->getLocator().'&hidecaption=1';
		return true;
	}
}
