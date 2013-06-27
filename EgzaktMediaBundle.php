<?php

namespace Egzakt\MediaBundle;

use Egzakt\MediaBundle\DependencyInjection\Compiler\MediaParserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EgzaktMediaBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new MediaParserCompilerPass());
	}
}
