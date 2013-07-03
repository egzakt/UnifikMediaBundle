<?php

namespace Egzakt\MediaBundle\Controller\Backend;

use Symfony\Component\HttpFoundation\Response;

use Egzakt\SystemBundle\Lib\Backend\BaseController;

/**
 * Navigation Controller
 */
class NavigationController extends BaseController
{
    /**
     * Global Bundle Bar Action
     *
     * @param string $masterRoute
     *
     * @return Response
     */
   	public function globalModuleBarAction($masterRoute)
	{
		$selected = (0 === strpos($masterRoute, 'egzakt_media_backend_media'));
		return $this->render('EgzaktMediaBundle:Backend/Navigation:global_bundle_bar.html.twig', array(
			'selected' => $selected,
		));
	}

}
