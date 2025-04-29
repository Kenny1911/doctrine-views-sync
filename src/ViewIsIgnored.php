<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

use Doctrine\DBAL\Schema\View;

/**
 * @api
 */
final class ViewIsIgnored extends \LogicException
{
    public static function createByView(View $view): self
    {
        return new self(\sprintf('View "%s" is ignored.', $view->getName()));
    }
}
