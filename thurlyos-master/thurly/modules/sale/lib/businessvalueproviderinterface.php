<?php

namespace Thurly\Sale;

interface IBusinessValueProvider
{
	public function getPersonTypeId();
	public function getBusinessValueProviderInstance($mapping);
}
