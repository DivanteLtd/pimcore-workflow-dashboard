<?php
/**
 * @date        22/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle\Model\Element;

use Pimcore\Model\Dao\AbstractDao;

/**
 * Class Dao
 * @package Divante\WorkflowBoardBundle\Model\Element
 */
class Dao extends AbstractDao
{
    const TABLE_NAME = 'bundle_workflow_board_elements';

    /**
     * @param int $cid
     * @param string $ctype
     * @param int $workflowId
     * @throws \Exception
     */
    public function getByPrimary($cid, $ctype, $workflowId)
    {
        $sql  = sprintf('SELECT * FROM `%s` WHERE cid = ? AND ctype = ? AND workflowId = ?', static::TABLE_NAME);
        $data = $this->db->fetchRow($sql, [$cid, $ctype, $workflowId]);

        if (!$data['cid']) {
            $message = "No workflow dashboard element found with primary key (%d, %s, %d)";
            $message = sprintf($message, $cid, $ctype, $workflowId);
            throw new \Exception($message);
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * Saves workflow dashboard element
     */
    public function save()
    {
        $dataAttributes    = get_object_vars($this->model);
        $validTableColumns = $this->getValidTableColumns(static::TABLE_NAME);

        $data = [];
        foreach ($dataAttributes as $key => $value) {
            if (in_array($key, $validTableColumns)) {
                $data[$key] = $value;
            }
        }

        $this->db->insertOrUpdate(static::TABLE_NAME, $data);
    }

    /**
     * Deletes workflow dashboard element
     */
    public function delete()
    {
        $this->db->delete(static::TABLE_NAME, [
            'cid'        => $this->model->getCid(),
            'ctype'      => $this->model->getCtype(),
            'workflowId' => $this->model->getWorkflowId()
        ]);
    }
}
