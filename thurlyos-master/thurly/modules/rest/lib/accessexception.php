<?
namespace Thurly\Rest;


class AccessException
	extends RestException
{
	const MESSAGE = 'Access denied!';
	const CODE = 'ACCESS_DENIED';

	public function __construct($msg = '', \Exception $previous = null)
	{
		parent::__construct(
			self::MESSAGE.($msg === '' ? '' : (' '.$msg)),
			self::CODE,
			\CRestServer::STATUS_FORBIDDEN,
			$previous
		);
	}
}
?>