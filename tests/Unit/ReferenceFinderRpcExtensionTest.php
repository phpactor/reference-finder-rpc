<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinderRpc\ReferenceFinderRpcExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Phpactor\Extension\Rpc\RpcExtension;

class ReferenceFinderRpcExtensionTest extends TestCase
{
    public function testRegister()
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            ReferenceFinderRpcExtension::class,
            RpcExtension::class,
            LoggingExtension::class,
        ]);
        $handler = $container->get(RpcExtension::SERVICE_REQUEST_HANDLER);

        $this->assertInstanceOf(RequestHandler::class, $handler);
        $response = $handler->handle(Request::fromNameAndParameters('goto_definition', [
            'offset' => 10,
            'source' => '<?php ' . __CLASS__ . ';',
            'path' => __FILE__,
        ]));

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertContains('Unable to locate definition', $response->message());
    }
}
