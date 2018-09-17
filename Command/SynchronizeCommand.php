<?php
/**
 * @date        25/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowBoardBundle\Command;

use Divante\WorkflowBoardBundle\Service;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SynchronizeCommand
 * @package Divante\WorkflowBoardBundle\Command
 */
class SynchronizeCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName("divante:workflow-board:synchronize");
        $this->setDescription("Synchronize workflow dashboard elements with workflow states");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $service = new Service\Element();
            $service->synchronizeAll();
            $this->dump('Synchronize is complete');
        } catch (\Exception $e) {
            $this->writeError("Error: ". $e->getMessage());
        }
    }
}
