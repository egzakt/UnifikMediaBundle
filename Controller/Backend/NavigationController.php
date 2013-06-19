<?php

namespace Egzakt\MediaBundle\Controller\Backend;

use Symfony\Component\HttpFoundation\Response;

use Egzakt\SystemBundle\Lib\Backend\BaseController;

/**
 * User Controller
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
    public function sectionModuleBarAction($masterRoute)
    {
        $selected = (0 === strpos($masterRoute, 'egzakt_media_backend_media'));

        return $this->render('EgzaktMediaBundle:Backend/Navigation:section_module_bar.html.twig', array(
            'selected' => $selected
        ));
    }

	public function globalModuleBarAction($masterRoute)
	{
		$selected = (0 === strpos($masterRoute, 'egzakt_system_backend_user'));
		return $this->render('EgzaktMediaBundle:Backend/Navigation:global_bundle_bar.html.twig', array(
			'selected' => $selected,
		));
	}

}
