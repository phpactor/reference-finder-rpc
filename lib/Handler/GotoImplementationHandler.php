<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Handler;

use Phpactor\Completion\Core\Util\OffsetHelper;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\Extension\Rpc\Response\Reference\FileReferences;
use Phpactor\Extension\Rpc\Response\Reference\Reference;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\ReferenceFinder\DefinitionFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;

class GotoImplementationHandler extends AbstractHandler
{
    const NAME = 'goto_implementation';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_LANGUAGE = 'language';
    const PARAM_TARGET = 'target';
    const PARAM_SELECTED_PATH = 'selected_path';

    /**
     * @var ClassImplementationFinder
     */
    private $finder;

    public function __construct(
        ClassImplementationFinder $finder
    ) {
        $this->finder = $finder;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver)
    {
        $resolver->setDefaults([
            self::PARAM_LANGUAGE => 'php',
            self::PARAM_TARGET => OpenFileResponse::TARGET_FOCUSED_WINDOW
        ]);
        $resolver->setRequired([
            self::PARAM_OFFSET,
            self::PARAM_SOURCE,
            self::PARAM_PATH,
        ]);
    }

    public function handle(array $arguments)
    {
        $document = TextDocumentBuilder::create($arguments[self::PARAM_SOURCE])
            ->uri($arguments[self::PARAM_PATH])
            ->language($arguments[self::PARAM_LANGUAGE])->build();

        $offset = ByteOffset::fromInt($arguments[self::PARAM_OFFSET]);
        $locations = $this->finder->findImplementations($document, $offset);

        if (1 !== $locations->count()) {
            $references = [];
            foreach ($locations as $location) {
                assert($location instanceof Location);

                $fileReferences = FileReferences::fromPathAndReferences(
                    $location->uri()->__toString(),
                    [
                        Reference::fromStartEndLineNumberAndCol($location->offset()->toInt(), $location->offset()->toInt(), 0, 0)
                    ]
                );
                $references[] = $fileReferences;
            }

            return new FileReferencesResponse($references);
        }

        $location = $locations->first();
        return OpenFileResponse::fromPathAndOffset(
            $location->uri()->path(),
            $location->offset()->toInt()
        )->withTarget($arguments[self::PARAM_TARGET]);
    }
}
