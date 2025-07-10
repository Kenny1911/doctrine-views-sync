<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Console;

use Kenny1911\DoctrineViewsSync\ViewsSync;
use Kenny1911\DoctrineViewsSync\ViewsSyncFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync\Console
 */
abstract class BaseCommand extends Command
{
    final public function __construct(
        private readonly ViewsSyncFactory $factory,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    final protected function configure(): void
    {
        $this->addOption(
            name: 'conn',
            mode: InputOption::VALUE_REQUIRED,
            description: 'The name of the connection to use.',
        );
    }

    #[\Override]
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connectionName = $input->hasArgument('conn') ? (string) $input->getArgument('conn') : null;
        $viewsSync = $this->factory->create($connectionName);

        $this->doExecute($viewsSync);

        return self::SUCCESS;
    }

    abstract protected function doExecute(ViewsSync $viewsSync): void;
}
