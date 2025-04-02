<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\ViewsProvider;

use Kenny1911\DoctrineViewsSync\Util\RewindableGenerator;
use Kenny1911\DoctrineViewsSync\ViewsProvider;

/**
 * @api
 */
final class ChainViewsProvider implements ViewsProvider
{
    /**
     * @param iterable<ViewsProvider> $providers
     */
    public function __construct(
        private readonly iterable $providers,
    ) {}

    #[\Override]
    public function getViews(): iterable
    {
        return new RewindableGenerator(function () {
            foreach ($this->providers as $provider) {
                foreach ($provider->getViews() as $view) {
                    yield $view;
                }
            }
        });
    }
}
