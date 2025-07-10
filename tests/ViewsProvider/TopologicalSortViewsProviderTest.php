<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Tests\ViewsProvider;

use Doctrine\DBAL\Schema\View as DoctrineView;
use Kenny1911\DoctrineViewsSync\Schema\View;
use Kenny1911\DoctrineViewsSync\ViewsProvider\CallableViewsProvider;
use Kenny1911\DoctrineViewsSync\ViewsProvider\TopologicalSortViewsProvider;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync\Tests\ViewsProvider
 */
final class TopologicalSortViewsProviderTest extends TestCase
{
    /**
     * @param list<View> $views
     * @param list<string> $expectedViewsNames
     *
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    #[TestWith(
        data: [
            'views' => [],
            'expectedViewsNames' => [],
        ],
        name: 'empty',
    )]
    #[TestWith(
        data: [
            'views' => [
                new DoctrineView('foo', 'SELECT 1'),
                new DoctrineView('bar', 'SELECT 1'),
            ],
            'expectedViewsNames' => ['foo', 'bar'],
        ],
        name: 'simple',
    )]
    #[TestWith(
        data: [
            'views' => [
                new View('foo', 'SELECT 1', ['bar']),
                new DoctrineView('bar', 'SELECT 1'),
            ],
            'expectedViewsNames' => ['bar', 'foo'],
        ],
        name: 'depends',
    )]
    #[TestWith(
        data: [
            'views' => [
                new View('foo', 'SELECT 1', ['bar', 'qux']),
                new View('bar', 'SELECT 1'),
                new View('baz', 'SELECT 1', ['qux']),
                new View('qux', 'SELECT 1', ['bar']),
            ],
            'expectedViewsNames' => ['bar', 'qux', 'foo', 'baz'],
        ],
        name: 'complex',
    )]
    public function test(array $views, array $expectedViewsNames): void
    {
        $viewsProvider = new TopologicalSortViewsProvider(new CallableViewsProvider(static fn() => $views));
        $actualViewsNames = [];

        foreach ($viewsProvider->getViews() as $view) {
            $actualViewsNames[] = $view->getName();
        }

        self::assertSame($expectedViewsNames, $actualViewsNames);
    }

    /**
     * @throws CircularDependencyException
     */
    public function testThrowElementNotFoundException(): void
    {
        self::expectException(ElementNotFoundException::class);

        $views = [
            new View('foo', 'SELECT 1', ['bar']),
        ];

        $viewsProvider = new TopologicalSortViewsProvider(new CallableViewsProvider(static fn() => $views));
        $viewsProvider->getViews();
    }

    /**
     * @throws ElementNotFoundException
     */
    public function testThrowCircularDependencyException(): void
    {
        self::expectException(CircularDependencyException::class);

        $views = [
            new View('foo', 'SELECT 1', ['bar']),
            new View('bar', 'SELECT 1', ['foo']),
        ];

        $viewsProvider = new TopologicalSortViewsProvider(new CallableViewsProvider(static fn() => $views));
        $viewsProvider->getViews();
    }
}
