<?php

namespace Thurly\Main\UrlPreview\Parser;

use Thurly\Main\UrlPreview\HtmlDocument;
use Thurly\Main\Web\HttpClient;

class Facebook extends Oembed
{
	public function handle(HtmlDocument $document, HttpClient $httpClient = null)
	{
		parent::handle($document);

		if($document->getEmdbed())
		{
			$embedHtml = $document->getEmdbed();

			preg_match('/<div(?:.+?)fb-post(.+?)>/i', $embedHtml, $divTags);
			if($divTags[0])
			{
				$div = str_replace('>', ' data-show-text="false">', $divTags[0]);
				$embedHtml = str_replace($divTags[0], $div, $embedHtml);
				$document->setEmbed($embedHtml);
			}
		}
	}
}
