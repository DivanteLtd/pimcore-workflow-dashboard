<?php
/**
 * @date        23/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowBoardBundle\Dto;

use Divante\WorkflowBoardBundle\Model;
use Pimcore\Model\Element\Service as ElementService;
use Pimcore\Model\User;

/**
 * Class Element
 * @package Divante\WorkflowBoardBundle\Dto
 */
class Element
{
    /**
     * @var Model\Element
     */
    protected $element;

    /**
     * Element constructor.
     * @param Model\Element $element
     */
    public function __construct(Model\Element $element)
    {
        $this->element = $element;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        $element = ElementService::getElementById($this->element->getCtype(), $this->element->getCid());
        if (!$element) {
            throw new \UnexpectedValueException();
        }

        return [
            'state'  => $this->element->getState(),
            'status' => $this->element->getStatus(),
            'config' => [
                'id'            => $this->element->getCid(),
                'type'          => $this->element->getCtype(),
                'workflowId'    => $this->element->getWorkflowId(),
                'position'      => $this->element->getPosition(),
                'assignType'    => $this->element->getAssignType(),
                'assignId'      => $this->element->getAssignId(),
                'assignName'    => $this->getAssignName(),
                'name'          => $element->getKey(),
                'allowedColumn' => [],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getAssignName() : string
    {
        $assignName = 'System';

        if (!$this->element->getAssignId()) {
            return $assignName;
        }

        switch ($this->element->getAssignType()) {
            case Model\Element::ASSIGN_TYPE_USER:
                $assignName = $this->getUserAssignName();
                break;
            case Model\Element::ASSIGN_TYPE_ROLE:
                $assignName = $this->getRoleAssignName();
                break;
        }

        return $assignName;
    }

    /**
     * @return string
     */
    protected function getUserAssignName() : string
    {
        $user = User::getById($this->element->getAssignId());
        if (!$user) {
            throw new \UnexpectedValueException();
        }

        $assignName = trim(sprintf('%s %s', $user->getFirstname(), $user->getLastname()));
        if ($assignName !== '') {
            return $user->getName() . '(' . $assignName . ')';
        }

        return $user->getName();
    }

    /**
     * @return string
     */
    protected function getRoleAssignName() : string
    {
        $role = User\Role::getById($this->element->getAssignId());
        if (!$role) {
            throw new \UnexpectedValueException();
        }

        return $role->getName();
    }
}
