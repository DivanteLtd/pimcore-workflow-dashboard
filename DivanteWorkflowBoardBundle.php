<?php
/**
 * @date        22/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle;

use Pimcore\Extension\Bundle\Installer\InstallerInterface;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

/**
 * Class DivanteWorkflowBoardBundle
 * @package Divante\WorkflowBoardBundle
 */
class DivanteWorkflowBoardBundle extends AbstractPimcoreBundle
{
    const PERMISSION_WORKFLOW_BOARD       = 'workflow_board';
    const PERMISSION_WORKFLOW_BOARD_ADMIN = 'workflow_board_admin';

    /**
     * @return null|InstallerInterface
     */
    public function getInstaller()
    {
        $class = Installer::class;
        if ($this->container->has($class)) {
            return $this->container->get($class);
        }
        return null;
    }

    /**
     * @return array
     */
    public function getJsPaths()
    {
        return [
            '/bundles/divanteworkflowboard/js/pimcore/startup.js',
            '/bundles/divanteworkflowboard/js/pimcore/board.js',
            '/bundles/divanteworkflowboard/js/pimcore/element.js',
            '/bundles/divanteworkflowboard/js/workflow/workflow.js',
            '/bundles/divanteworkflowboard/js/workflow/workflowColumn.js',
            '/bundles/divanteworkflowboard/js/workflow/workflowDropZone.js',
            '/bundles/divanteworkflowboard/js/workflow/workflowPanel.js',
            '/bundles/divanteworkflowboard/js/pimcore/static6/js/pimcore/object/object.js',
        ];
    }

    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/divanteworkflowboard/css/style.css',
        ];
    }
}
