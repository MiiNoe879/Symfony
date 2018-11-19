<?php

namespace Massmedia\FilesFinderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController
 * @package Massmedia\FilesFinderBundle\Controller
 */
class SearchController extends Controller
{
    /**
     * Show search form and results
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('files_finder_search'))
            ->add('search', 'text', ['label' => false])
            ->add('doSearch', 'submit')
            ->getForm();

        $founded = false;
        if($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $search = $form->getData()['search'];
                $finder = $this->get('massmedia.files_finder');
                $userFiles = $this->get('kernel')->getRootDir() . '/../web/user_files';
                $founded = $finder->search($userFiles, $search);
            }
        }

        return $this->render('FilesFinderBundle:Search:index.html.twig', array(
            'form' => $form->createView(),
            'founded' => $founded,
            'showResults' => $request->isMethod('POST'),
        ));
    }
}
