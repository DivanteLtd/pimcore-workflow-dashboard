<?php
/**
 * @date        22/01/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowBoardBundle\Model\Element;

use Divante\WorkflowBoardBundle\Model\Element;
use Pimcore\Model\Listing\AbstractListing;

/**
 * Class Listing
 * @package Divante\WorkflowBoardBundle\Model\Element
 */
class Listing extends AbstractListing
{
    /**
     * @var array
     */
    public $elements = [];

    /**
     * @param string $key
     * @return bool
     */
    public function isValidOrderKey($key)
    {
        return true;
    }

    /**
     * @return Element[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }
}
