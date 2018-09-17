<?php
/**
 * @date        23/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowBoardBundle\Service;

use Pimcore\Model\Workflow;

/**
 * Class Board
 * @package Divante\WorkflowBoardBundle\Service
 */
class Board
{
    /**
     * @param int $workflowId
     * @return array
     */
    public function getConfiguration(int $workflowId) : array
    {
        $workflow = $this->getWorkflow($workflowId);

        $config = [];

        $defaultState  = $workflow->getDefaultState();
        $defaultStatus = $workflow->getDefaultStatus();
        if ($defaultState && $defaultStatus) {
            $config[$defaultState] = [$defaultStatus => 1];
        }

        foreach ($workflow->getActions() as $action) {
            $transitionTo = $action['transitionTo'] ?? [];
            foreach ($transitionTo as $state => $statuses) {
                $config[$state] = $config[$state] ?? [];
                $config[$state] = array_merge($config[$state], array_fill_keys($statuses, 1));
            }
        }

        return $this->sortConfiguration($workflow, $config);
    }

    /**
     * @param int $id
     * @return Workflow
     * @throws \UnexpectedValueException
     */
    protected function getWorkflow(int $id) : Workflow
    {
        $workflow = Workflow::getById($id);
        if (!$workflow instanceof Workflow) {
            $message = "No workflow found with ID " . $id;
            throw new \UnexpectedValueException($message);
        }
        return $workflow;
    }

    /**
     * @param Workflow $workflow
     * @param array $config
     * @return array
     */
    protected function sortConfiguration(Workflow $workflow, array $config) : array
    {
        $items = [];

        $states = [];
        foreach ($workflow->getStates() as $state) {
            $name          = $state['name'];
            $states[$name] = $state;
        }

        $statuses = [];
        foreach ($workflow->getStatuses() as $status) {
            $name            = $status['name'];
            $statuses[$name] = $status;
        }

        foreach ($states as $stateName => $state) {
            if (isset($config[$stateName])) {
                $item = ['state' => $state, 'statuses' => []];
                foreach ($statuses as $statusName => $status) {
                    if (isset($config[$stateName][$statusName])) {
                        $item['statuses'][] = $status;
                    }
                }
                $items[] = $item;
            }
        }

        return $items;
    }
}
