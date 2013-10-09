<?php

namespace Flexy\MediaBundle\Controller\Backend;

use Symfony\Component\HttpFoundation\Response;

use Flexy\SystemBundle\Lib\Backend\BaseController;

/**
 * Navigation Controller
 */
class NavigationController extends BaseController
{
    /**
     * Global bundle bar
     *
     * @param $_masterRoute
     * @return Response
     */
    public function globalModuleBarAction($_masterRoute)
    {
        $selected = (0 === strpos($_masterRoute, 'flexy_media_backend_media'));
        return $this->render('FlexyMediaBundle:Backend/Navigation:global_bundle_bar.html.twig', array(
            'selected' => $selected,
        ));
    }

}
