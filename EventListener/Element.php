<?php
/**
 * @date        24/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowBoardBundle\EventListener;

use Divante\WorkflowBoardBundle\Dto;
use Divante\WorkflowBoardBundle\Model;
use Divante\WorkflowBoardBundle\Service;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Event\Model\WorkflowEvent;
use Pimcore\Event\AdminEvents;
use Pimcore\Event\AssetEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\WorkflowEvents;
use Pimcore\Model\Element\Service as ElementService;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\User;
use Pimcore\WorkflowManagement\Workflow\Manager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Element
 * @SuppressWarnings(PHPMD)
 * @package Divante\WorkflowBoardBundle\EventListener
 */
class Element implements EventSubscriberInterface
{
    /**
     * @var Service\Element
     */
    protected $service;

    /**
     * Element constructor.
     * @param Service\Element $service
     */
    public function __construct(Service\Element $service)
    {
        $this->service = $service;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetEvents::POST_ADD                   => 'onPostAdd',
            DocumentEvents::POST_ADD                => 'onPostAdd',
            DataObjectEvents::POST_ADD              => 'onPostAdd',
            AssetEvents::POST_DELETE                => 'onPostDelete',
            DocumentEvents::POST_DELETE             => 'onPostDelete',
            DataObjectEvents::POST_DELETE           => 'onPostDelete',
            WorkflowEvents::POST_ACTION             => 'onPostAction',
            AdminEvents::ASSET_GET_PRE_SEND_DATA    => 'onGetPreSendData',
            AdminEvents::DOCUMENT_GET_PRE_SEND_DATA => 'onGetPreSendData',
            AdminEvents::OBJECT_GET_PRE_SEND_DATA   => 'onGetPreSendData',
        ];
    }

    /**
     * @param ElementEventInterface $event
     */
    public function onPostAdd(ElementEventInterface $event)
    {
        $element = $event->getElement();
        if (!Manager::elementHasWorkflow($element)) {
            return;
        }

        $manager = $this->getManager($element);

        $workflowElement = new Model\Element();
        $workflowElement->setCid($element->getId());
        $workflowElement->setCtype($this->getElementType($element));
        $workflowElement->setWorkflowId($manager->getWorkflow()->getId());
        $workflowElement->setState((string) $manager->getElementState());
        $workflowElement->setStatus((string) $manager->getElementStatus());
        $workflowElement->setAssignId($element->getUserOwner() ?? 0);
        $workflowElement->save();
    }

    /**
     * @param ElementEventInterface $event
     */
    public function onPostDelete(ElementEventInterface $event)
    {
        $element = $event->getElement();
        if (!Manager::elementHasWorkflow($element)) {
            return;
        }

        $manager    = $this->getManager($element);
        $cid        = $element->getId();
        $ctype      = $this->getElementType($element);
        $workflowId = $manager->getWorkflow()->getId();

        $workflowElement = Model\Element::getByPrimary($cid, $ctype, $workflowId);
        if ($workflowElement instanceof Model\Element) {
            $workflowElement->delete();
        }
    }

    /**
     * @param WorkflowEvent $event
     */
    public function onPostAction(WorkflowEvent $event)
    {
        $manager = $event->getWorkflowManager();
        $element = $manager->getElement();

        $cid        = $element->getId();
        $ctype      = $this->getElementType($element);
        $workflowId = $manager->getWorkflow()->getId();

        $workflowElement = Model\Element::getByPrimary($cid, $ctype, $workflowId);
        if ($workflowElement instanceof Model\Element) {
            $workflowElement->setState($manager->getElementState());
            $workflowElement->setStatus($manager->getElementStatus());
            $workflowElement->save();
        }

        $data = $manager->getActionData();
        if (array_key_exists('additional', $data) && is_numeric($data['additional']['user'])) {
            $this->service->changeAssign(
                $cid,
                $ctype,
                $workflowId,
                Model\Element::ASSIGN_TYPE_USER,
                (int) $data['additional']['user']
            );
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function onGetPreSendData(GenericEvent $event)
    {
        $element = $this->extractElementFromEvent($event);
        $data    = $event->getArgument('data');

        if (Manager::elementCanAction($element)) {
            $user    = \Pimcore\Tool\Admin::getCurrentUser();
            $manager = $this->getManager($element, $user);

            $cid        = $element->getId();
            $ctype      = $this->getElementType($element);
            $workflowId = $manager->getWorkflow()->getId();

            $workflowElement = Model\Element::getByPrimary($cid, $ctype, $workflowId);
            if ($workflowElement) {
                $dtoData = (new Dto\Element($workflowElement))->getData();
                $data['workflowBoard'] = [];
                $data['workflowBoard']['assignType'] = $dtoData['config']['assignType'];
                $data['workflowBoard']['assignUserId'] = $dtoData['config']['assignId'];
                $data['workflowBoard']['assignUserName'] = $dtoData['config']['assignName'];
            }
        }

        $event->setArgument('data', $data);
    }

    /**
     * @param ElementInterface $element
     * @param User $user
     * @return Manager
     */
    protected function getManager(ElementInterface $element, User $user = null) : Manager
    {
        return new Manager($element, $user);
    }

    /**
     * @param ElementInterface $element
     * @return string
     */
    protected function getElementType(ElementInterface $element) : string
    {
        return ElementService::getType($element);
    }

    /**
     * @param GenericEvent $event
     * @return ElementInterface
     */
    protected function extractElementFromEvent(GenericEvent $event) : ElementInterface
    {
        foreach (['asset', 'document', 'object'] as $type) {
            if ($event->hasArgument($type)) {
                return $event->getArgument($type);
            }
        }

        throw new \UnexpectedValueException();
    }
}
