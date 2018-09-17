<?php
/**
 * @date        16/02/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle\Migrations;

use Divante\WorkflowBoardBundle\DivanteWorkflowBoardBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use PimcoreDevkitBundle\Service\InstallerService;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class Version20180216150259
 * @package Divante\WorkflowBoardBundle\Migrations
 */
class Version20180216150259 extends AbstractPimcoreMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var InstallerService
     */
    protected $installerService;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->getInstallerService()->createPermission(DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }

    /**
     * @return InstallerService
     * @throws \UnexpectedValueException
     */
    protected function getInstallerService(): InstallerService
    {
        if ($this->installerService === null) {
            $id = InstallerService::class;
            if (!$this->container->has($id)) {
                $message = sprintf("%s was not found", $id);
                throw new \UnexpectedValueException($message);
            }
            $this->installerService = $this->container->get($id);
        }
        return $this->installerService;
    }
}
