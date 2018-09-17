<?php
/**
 * @date        23/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowBoardBundle\Controller;

use Divante\WorkflowBoardBundle\Dto;
use Divante\WorkflowBoardBundle\Model;
use Divante\WorkflowBoardBundle\Service;
use Divante\WorkflowBoardBundle\DivanteWorkflowBoardBundle;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\User;
use Pimcore\Model\Workflow;
use Pimcore\Tool\Admin;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WorkflowBoardController
 * @SuppressWarnings(PHPMD)
 * @package Divante\WorkflowBoardBundle\Controller
 * @Route("/admin/workflow-board")
 */
class WorkflowBoardController extends AdminController
{
    /**
     * @return JsonResponse
     * @Route("/get-workflows")
     * @Method({"GET"})
     */
    public function getWorkflowsAction() : JsonResponse
    {
        $this->checkPermission(DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD);

        $listing = new Workflow\Listing();
        $listing->load();

        $data = [];
        foreach ($listing->getWorkflows() as $workflow) {
            $data[] = [
                'id'   => $workflow->getId(),
                'text' => $workflow->getName(),
            ];
        }

        return $this->adminJson(['data' => $data]);
    }

    /**
     * @return JsonResponse
     * @Route("/get-users")
     * @Method({"GET"})
     */
    public function getUsersAction() : JsonResponse
    {
        $this->checkPermission(DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD);

        $data = array_merge([['value' => -1, 'key' => 'all']], $this->getUserOptions());

        return $this->adminJson(['success' => true, 'data' => $data]);
    }

    /**
     * @param Request $request
     * @param Service\Board $service
     * @return JsonResponse
     * @Route("/get-configuration")
     * @Method({"GET"})
     */
    public function getConfigurationAction(Request $request, Service\Board $service) : JsonResponse
    {
        $this->checkPermission(DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD);

        $workflowId = (int) $request->get('workflowId', 0);
        $data       = $service->getConfiguration($workflowId);

        return $this->adminJson(['success' => true, 'data' => $data]);
    }

    /**
     * @param Request $request
     * @param Service\Element $service
     * @return JsonResponse
     * @Route("/get-elements")
     * @Method({"GET"})
     */
    public function getElementsAction(Request $request, Service\Element $service) : JsonResponse
    {
        $this->checkPermission(DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD);

        $workflowId = (int) $request->get('workflowId', 0);
        $userId     = (int) $request->get('userId', -1);
        $offset     = (int) $request->get('start', 0);
        $limit      = (int) $request->get('limit', 0);

        $result = $service->findAll($workflowId, $userId, $offset, $limit);
        $total  = $result['total'];
        $data   = [];

        foreach ($result['data'] as $element) {
            $data[] = (new Dto\Element($element))->getData();
        }

        return $this->adminJson([
            'success' => true,
            'total'   => $total,
            'data'    => $data,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @Route("/get-assign-options")
     * @Method({"GET"})
     */
    public function getAssignOptionsAction(Request $request) : JsonResponse
    {
        $this->checkPermission(DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD);

        $type = $request->get('type');

        if (Model\Element::ASSIGN_TYPE_USER === $type) {
            $data = $this->getUserOptions();
            return $this->adminJson(['success' => true, 'data' => $data]);
        }

        if (Model\Element::ASSIGN_TYPE_ROLE === $type) {
            $data = $this->getRoleOptions();
            return $this->adminJson(['success' => true, 'data' => $data]);
        }

        $message = "Invalid assign type: " . $type;
        throw new \Exception($message);
    }

    /**
     * @param Request $request
     * @param Service\Element $service
     * @return JsonResponse
     * @Route("/change-assign")
     * @Method({"POST"})
     */
    public function changeAssignAction(Request $request, Service\Element $service) : JsonResponse
    {
        $this->checkPermission(DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD);

        $cid        = (int) $request->get('id');
        $ctype      = $request->get('type');
        $workflowId = (int) $request->get('workflowId');
        $assignType = $request->get('assignType');
        $assignId   = (int) $request->get('assignId');

        $service->changeAssign($cid, $ctype, $workflowId, $assignType, $assignId);

        $workflowElement = Model\Element::getByPrimary($cid, $ctype, $workflowId);
        if (!$workflowElement instanceof Model\Element) {
            $message = "No workflow dashboard element found with primary key (%d, %s, %d)";
            $message = sprintf($message, $cid, $ctype, $workflowId);
            throw new \UnexpectedValueException($message);
        }

        return $this->adminJson([
            'success' => true,
            'data'    => (new Dto\Element($workflowElement))->getData(),
            'msg'     => '',
        ]);
    }

    /**
     * @return array
     */
    protected function getUserOptions() : array
    {
        $list = new User\Listing();

        if (!$this->isWorkflowAdmin()) {
            $list->setCondition('id = ?', Admin::getCurrentUser()->getId());
        }

        $list->setOrder('asc');
        $list->setOrderKey('name');
        $users = $list->load();

        if (!is_array($users) || count($users) < 1) {
            return [];
        }

        $options = [];
        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $value = $user->getName();
            $first = $user->getFirstname();
            $last  = $user->getLastname();

            if (!empty($first) || !empty($last)) {
                $value .= ' (' . $first . ' ' . $last . ')';
            }

            $options[] = [
                'value' => $user->getId(),
                'key'   => $value
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function getRoleOptions() : array
    {
        $listing = new User\Role\Listing();

        if (!$this->isWorkflowAdmin()) {
            $roleIds = Admin::getCurrentUser()->getRoles();
            $len     = count($roleIds);
            if ($len === 0) {
                return [];
            }

            $condition = sprintf('id IN (%s)', implode(', ', array_fill(0, $len, '?')));
            $listing->setCondition($condition, $roleIds);
        }

        $listing->setOrderKey('name');
        $listing->setOrder('asc');
        $listing->load();

        $options = array_map(function (User\Role $role) {
            return [
                'value' => $role->getId(),
                'key'   => $role->getName()
            ];
        }, $listing->getItems());

        return $options;
    }

    /**
     * @return bool
     */
    protected function isWorkflowAdmin() : bool
    {
        $user       = Admin::getCurrentUser();
        $permission = DivanteWorkflowBoardBundle::PERMISSION_WORKFLOW_BOARD_ADMIN;
        if ($user->isAdmin() || $user->isAllowed($permission)) {
            return true;
        }
        return false;
    }
}
