<?php

namespace Unifik\MediaBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Unifik\SystemBundle\Entity\Mapping;

class LoadMappingData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if ($this->hasReference('app-backend')) {
            $app = $manager->merge($this->getReference('app-backend'));
        } else {
            $app = $manager->getRepository('UnifikSystemBundle:App')->find(1);
        }

        if ($this->hasReference('navigation-global-module-bar')) {
            $navigation = $manager->merge($this->getReference('navigation-global-module-bar'));
        } else {
            $navigation = $manager->getRepository('UnifikSystemBundle:Navigation')->find(3);
        }

        // Media bundle mapping
        $mapping = new Mapping();
        $mapping->setApp($app);
        $mapping->setNavigation($navigation);
        $mapping->setTarget('UnifikMediaBundle:Backend/Navigation:GlobalModuleBar');
        $mapping->setType('render');

        $manager->persist($mapping);

        $manager->flush();
    }

    /**
     * Get Order
     *
     * @return int
     */
    public function getOrder()
    {
        return 999;
    }
}