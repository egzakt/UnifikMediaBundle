<?php

namespace Unifik\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MediaParserCompilerPass
 */
class MediaParserCompilerPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('unifik_media.parser')) {
            return;
        }

        $definition = $container->getDefinition(
            'unifik_media.parser'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'unifik_media.parser'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addMediaParser',
                array(new Reference($id))
            );
        }
    }

}