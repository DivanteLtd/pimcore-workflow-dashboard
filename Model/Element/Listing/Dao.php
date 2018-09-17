<?php
/**
 * @date        22/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle\Model\Element\Listing;

use Divante\WorkflowBoardBundle\Model\Element;
use Pimcore\Model\Listing\Dao\AbstractDao;

/**
 * Class Dao
 * @package Divante\WorkflowBoardBundle\Model\Element\Listing
 */
class Dao extends AbstractDao
{
    const TABLE_NAME = 'bundle_workflow_board_elements';

    /**
     * @return array
     */
    public function load()
    {
        $sql = sprintf(
            'SELECT cid, ctype, workflowId FROM %s%s%s%s',
            static::TABLE_NAME,
            $this->getCondition(),
            $this->getOrder(),
            $this->getOffsetLimit()
        );
        $items = $this->db->fetchAll($sql, $this->model->getConditionVariables());

        $elements = [];
        foreach ($items as $item) {
            $element = Element::getByPrimary($item['cid'], $item['ctype'], $item['workflowId']);
            if ($element instanceof Element) {
                $elements[] = $element;
            }
        }

        $this->model->setElements($elements);
        return $elements;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        $sql = sprintf('SELECT COUNT(*) AS amount FROM %s%s', static::TABLE_NAME, $this->getCondition());
        return (int) $this->db->fetchOne($sql, $this->model->getConditionVariables());
    }
}
