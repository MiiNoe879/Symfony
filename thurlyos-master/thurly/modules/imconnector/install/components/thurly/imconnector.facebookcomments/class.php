<?php

CThurlyComponent::includeComponentClass("thurly:imconnector.facebook");

class ImConnectorFacebookComments extends ImConnectorFacebook
{
	protected $connector = 'facebookcomments';

	protected $pageId = 'page_fbcomm';
};