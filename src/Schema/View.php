<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Schema;

/**
 * @api
 */
final class View extends \Doctrine\DBAL\Schema\View
{
    /**
     * @param list<string> $dependencies List of dependencies views names
     */
    public function __construct(
        string $name,
        string $sql,
        public readonly array $dependencies = [],
    ) {
        parent::__construct($name, $sql);
    }
}
