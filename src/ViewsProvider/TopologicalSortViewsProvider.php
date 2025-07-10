<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\ViewsProvider;

use Doctrine\DBAL\Schema\View;
use Kenny1911\DoctrineViewsSync\Schema\View as DepsView;
use Kenny1911\DoctrineViewsSync\ViewsProvider;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\FixedArraySort;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 */
final class TopologicalSortViewsProvider implements ViewsProvider
{
    public function __construct(
        private readonly ViewsProvider $inner,
    ) {}

    /**
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    #[\Override]
    public function getViews(): iterable
    {
        $sorter = new FixedArraySort();
        /** @var array<string, View> $views */
        $viewsMap = [];

        foreach ($this->inner->getViews() as $view) {
            $viewsMap[$view->getName()] = $view;
            $sorter->add($view->getName(), $view instanceof DepsView ? $view->dependencies : []);
        }

        /** @var list<View> $sortedViews */
        $sortedViews = [];

        foreach ($sorter->sort() as $viewName) {
            $sortedViews[] = $viewsMap[$viewName] ?? throw new \RuntimeException(\sprintf('View with name "%s" not exists.', $viewName));
        }

        return $sortedViews;
    }
}
