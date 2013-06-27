<?php

namespace Egzakt\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MediaParserCompilerPass
 */
class MediaParserCompilerPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('egzakt_media.parser')) {
            return;
        }

        $definition = $container->getDefinition(
            'egzakt_media.parser'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'egzakt_media.parser'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addMediaParser',
                array(new Reference($id))
            );
        }
    }

}