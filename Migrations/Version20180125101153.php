<?php
/**
 * @date        25/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle\Migrations;

use Divante\WorkflowBoardBundle\DivanteWorkflowBoardBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\User;

/**
 * Class Version20180125101153
 * @package Divante\WorkflowBoardBundle\Migrations
 */
class Version20180125101153 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $key = DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD_ADMIN;
        User\Permission\Definition::create($key);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
