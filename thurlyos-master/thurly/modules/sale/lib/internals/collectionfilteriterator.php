<?php

namespace Thurly\Sale\Internals;
/**
 * Class CollectionFilterIterator
 * @package Thurly\Sale\Internals
 */
class CollectionFilterIterator extends \FilterIterator
{
	public $callback = null;

	/**
	 * CustomFilterIterator constructor.
	 * @param \Iterator $iterator
	 * @param $callback
	 */
	public function __construct(\Iterator $iterator, $callback)
	{
		$this->callback = $callback;

		parent::__construct($iterator);
	}

	/**
	 * @return mixed
	 */
	public function accept()
	{
		return call_user_func($this->callback, parent::current());
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return iterator_count($this);
	}
}
