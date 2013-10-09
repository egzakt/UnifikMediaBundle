<?php

namespace Flexy\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MediaParserCompilerPass
 */
class MediaParserCompilerPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('flexy_media.parser')) {
            return;
        }

        $definition = $container->getDefinition(
            'flexy_media.parser'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'flexy_media.parser'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addMediaParser',
                array(new Reference($id))
            );
        }
    }

}