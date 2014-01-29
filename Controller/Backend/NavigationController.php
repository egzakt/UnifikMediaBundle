<?php

namespace Unifik\MediaBundle\Controller\Backend;

use Symfony\Component\HttpFoundation\Response;

use Unifik\SystemBundle\Lib\Backend\BaseController;

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
        $selected = (0 === strpos($_masterRoute, 'unifik_media_backend_media'));
        return $this->render('UnifikMediaBundle:Backend/Navigation:global_bundle_bar.html.twig', array(
            'selected' => $selected,
        ));
    }

}
