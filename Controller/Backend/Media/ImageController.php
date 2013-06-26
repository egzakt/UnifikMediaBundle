<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Doctrine\Tests\ORM\Functional\CompositePrimaryKeyTest;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Form\ImageType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\MediaBundle\Form\MediaType;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * ad Controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
 */
class ImageController extends BaseController
{
    /**
     * @var adRepository
     */
    protected $mediaRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();
		$this->mediaRepository = $this->mediaRepository = $this->getEm()->getRepository('EgzaktMediaBundle:Media');
    }

	public function editImageAction($id, Request $request)
	{
		/** @var Image $image */
		$image = $this->mediaRepository->find($id);
		if(!$image){
			return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
		}

		$path = $this->get('kernel')->getRootDir().'/../web'.$image->getMediaPath();
		file_put_contents($path, file_get_contents($request->get('image')));

		$this->getEm()->persist($image);
		$this->getEm()->flush();

		return new JsonResponse(json_encode(array()));
	}

}
