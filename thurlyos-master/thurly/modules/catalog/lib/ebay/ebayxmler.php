<?php
namespace Thurly\Catalog\Ebay;

class EbayXMLer
{
	protected $fieldsMap = array(
		"NAME" => array(
			"PATH" => "ProductInformation/Title"
		),
		"CATEGORIES" => array(
			"PATH" => "ProductInformation/Categories/Category",
			"TYPE" => "eBayLeafCategory"
		)
	);

	public function makeProductNode($product)
	{
		$dom = new \DOMDocument('1.0', 'utf-8');

		$productNode = $dom->createElement('Product');

		foreach($this->fieldsMap as $thurlyFieldId => $ebayFieldPath)
		{
			if(isset($product[$thurlyFieldId]))
			{
				$arPath = $this->parsePath($ebayFieldPath);

				$newNode = $oldNewNode = null;

				foreach($arPath as $pathItem)
				{
					if(!$this->isChildExist($productNode, $pathItem))
					{
						$newNode = $dom->createElement($pathItem);

						if($oldNewNode)
							$oldNewNode->appendChild($newNode);
						else
							$productNode->appendChild($newNode);

						$oldNewNode = $newNode;
					}
				}

				if($newNode)
					$newNode->nodeValue = $product[$thurlyFieldId];
			}
		}

		return $dom->saveXML($productNode);
	}

	public function isChildExist(\DOMNode $node, $childName)
	{
		$result = false;
		$children = $node->childNodes;

		foreach($children as $childNode)
		{
			if($childNode->nodeName == $childName)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	protected function parsePath($path)
	{
		return explode("/", $path);
	}
}