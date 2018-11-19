<?php

namespace Massmedia\FilesFinderBundle\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations;

/**
 * Class SearchController
 * @package Massmedia\FilesFinderBundle\Api\Controller
 */
class SearchController extends Controller
{
    /**
     * List founded files.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="search", requirements=".+", nullable=false, description="Text to search")
     * @Annotations\View()
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getSearchAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $finder = $this->get('massmedia.files_finder');
        $userFiles = $this->get('kernel')->getRootDir() . '/../web/user_files';
        $founded = $finder->search($userFiles, $paramFetcher->get('search'));

        return ['files' => $founded];
    }
}
