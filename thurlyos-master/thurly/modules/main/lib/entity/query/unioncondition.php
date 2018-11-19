<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2017 Thurly
 */

namespace Thurly\Main\Entity\Query;

use Thurly\Main\ArgumentException;
use Thurly\Main\DB\SqlExpression;
use Thurly\Main\Entity\Query;

/**
 * UNION container to be used in Query.
 *
 * @package    thurly
 * @subpackage main
 */
class UnionCondition
{
	/** @var Query|SqlExpression */
	protected $subQuery;

	/** @var bool */
	protected $all;

	/**
	 * @param Query|SqlExpression $subQuery
	 * @param bool                $unionAll
	 *
	 * @throws ArgumentException
	 */
	public function __construct($subQuery, $unionAll = false)
	{
		if (!($subQuery instanceof Query) && !($subQuery instanceof SqlExpression))
		{
			throw new ArgumentException("Query or SqlExpression expected, `".gettype($subQuery)."` found.");
		}

		$this->subQuery = $subQuery;
		$this->all = $unionAll;
	}

	/**
	 * @return SqlExpression|Query
	 */
	public function getSubQuery()
	{
		return $this->subQuery;
	}

	/**
	 * @param SqlExpression|Query $subQuery
	 */
	public function setSubQuery($subQuery)
	{
		$this->subQuery = $subQuery;
	}

	/**
	 * @return bool
	 */
	public function isAll()
	{
		return $this->all;
	}

	/**
	 * @param bool $all
	 */
	public function setAll($all)
	{
		$this->all = $all;
	}

	/**
	 * @return string
	 */
	public function getSql()
	{
		$sql = "UNION ";

		if ($this->all)
		{
			$sql .= "ALL ";
		}

		return $sql."({$this->getSubQuerySql()})";
	}

	/**
	 * @return string
	 */
	public function getSubQuerySql()
	{
		if ($this->subQuery instanceof Query)
		{
			return $this->subQuery->getQuery();
		}
		elseif ($this->subQuery instanceof SqlExpression)
		{
			return $this->subQuery->compile();
		}

		return null;
	}
}
