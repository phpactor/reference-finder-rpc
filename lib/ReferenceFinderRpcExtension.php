<?php

namespace Phpactor\Extension\ReferenceFinderRpc;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\ReferenceFinderRpc\Handler\GotoDefinitionHandler;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\MapResolver\Resolver;

class ReferenceFinderRpcExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('reference_finder_rpc.handler.goto_definition', function (Container $container) {
            return new GotoDefinitionHandler($container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR));
        }, [ RpcExtension::TAG_RPC_HANDLER => [ 'name' => 'goto_definition' ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
