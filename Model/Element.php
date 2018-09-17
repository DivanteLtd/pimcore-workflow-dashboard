<?php
/**
 * @date        22/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle\Model;

use Pimcore\Model\AbstractModel;

/**
 * Class Element
 * @package Divante\WorkflowBoardBundle\Model
 * @method \Divante\WorkflowBoardBundle\Model\Element\Dao getDao()
 */
class Element extends AbstractModel
{
    const ASSIGN_TYPE_USER = 'user';
    const ASSIGN_TYPE_ROLE = 'role';

    /**
     * @var int
     */
    public $cid;

    /**
     * @var string
     */
    public $ctype;

    /**
     * @var int
     */
    public $workflowId;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $position = 0;

    /**
     * @var string
     */
    public $assignType = self::ASSIGN_TYPE_USER;

    /**
     * @var int
     */
    public $assignId = 0;

    /**
     * @param int $cid
     * @param string $ctype
     * @param int $workflowId
     * @return Element|null
     */
    public static function getByPrimary($cid, $ctype, $workflowId)
    {
        $element = new self();

        try {
            $element->getDao()->getByPrimary($cid, $ctype, $workflowId);
        } catch (\Exception $ex) {
            return null;
        }

        return $element;
    }

    /**
     * @return int
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @param int $cid
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
    }

    /**
     * @return string
     */
    public function getCtype()
    {
        return $this->ctype;
    }

    /**
     * @param string $ctype
     */
    public function setCtype($ctype)
    {
        $this->ctype = $ctype;
    }

    /**
     * @return int
     */
    public function getWorkflowId()
    {
        return $this->workflowId;
    }

    /**
     * @param int $workflowId
     */
    public function setWorkflowId($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getAssignType()
    {
        return $this->assignType;
    }

    /**
     * @param string $assignType
     */
    public function setAssignType($assignType)
    {
        $this->assignType = $assignType;
    }

    /**
     * @return int
     */
    public function getAssignId()
    {
        return $this->assignId;
    }

    /**
     * @param int $assignId
     */
    public function setAssignId($assignId)
    {
        $this->assignId = $assignId;
    }
}
