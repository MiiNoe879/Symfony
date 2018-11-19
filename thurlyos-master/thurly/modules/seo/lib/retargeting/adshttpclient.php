<?

namespace Thurly\Seo\Retargeting;

class AdsHttpClient extends \Thurly\Main\Web\HttpClient
{
	const HTTP_DELETE = "DELETE";

	public function delete($url, $postData = null, $multipart = false)
	{
		if ($multipart)
		{
			$postData = $this->prepareMultipart($postData);
		}

		if($this->query(self::HTTP_DELETE, $url, $postData))
		{
			return $this->getResult();
		}
		return false;
	}
}