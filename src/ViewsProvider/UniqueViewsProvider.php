<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\ViewsProvider;

use Kenny1911\DoctrineViewsSync\Util\RewindableGenerator;
use Kenny1911\DoctrineViewsSync\ViewsProvider;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 */
final class UniqueViewsProvider implements ViewsProvider
{
    public function __construct(
        private readonly ViewsProvider $inner,
    ) {}

    public function getViews(): iterable
    {
        return new RewindableGenerator(function () {
            /** @var list<string> $uniqueNames */
            $uniqueNames = [];

            foreach ($this->inner->getViews() as $view) {
                if (\in_array($view->getName(), $uniqueNames, true)) {
                    throw DuplicateView::create($view->getName());
                }

                $uniqueNames[] = $view->getName();

                yield $view;
            }
        });
    }
}
