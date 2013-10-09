<?php

namespace Flexy\MediaBundle;

use Flexy\MediaBundle\DependencyInjection\Compiler\MediaParserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FlexyMediaBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new MediaParserCompilerPass());
	}
}
