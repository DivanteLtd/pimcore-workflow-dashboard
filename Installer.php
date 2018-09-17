<?php
/**
 * @date        22/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle;

use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;

/**
 * Class Installer
 * @package Divante\WorkflowBoardBundle
 */
class Installer extends MigrationInstaller
{
    /**
     * @param Schema $schema
     * @param Version $version
     */
    public function migrateInstall(Schema $schema, Version $version)
    {
        $sql = "CREATE TABLE `bundle_workflow_board_elements` ("
             . "`cid` int(10) unsigned NOT NULL, "
             . "`ctype` enum('document','asset','object') NOT NULL, "
             . "`workflowId` int(10) unsigned NOT NULL, "
             . "`state` varchar(255) NOT NULL, "
             . "`status` varchar(255) NOT NULL, "
             . "`position` int(10) unsigned NOT NULL DEFAULT 0, "
             . "`assignType` enum('user','role') NOT NULL DEFAULT 'user', "
             . "`assignId` int(10) unsigned NOT NULL DEFAULT 0, "
             . "PRIMARY KEY (`cid`,`ctype`,`workflowId`)"
             . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $version->addSql($sql);
    }

    /**
     * @param Schema $schema
     * @param Version $version
     */
    public function migrateUninstall(Schema $schema, Version $version)
    {
        $version->addSql("DROP TABLE `bundle_workflow_board_elements`");
    }
}
