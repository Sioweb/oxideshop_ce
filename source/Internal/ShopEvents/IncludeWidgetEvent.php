<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\ShopEvents;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class IncludeWidgetEvent
 *
 * @package OxidEsales\EshopCommunity\Internal\ShopEvents
 */
class IncludeWidgetEvent extends Event
{
    const NAME = self::class;

    /**
     * Result
     *
     * @var string
     */
    private $result = '';

    /**
     * @var Smarty
     */
    private $smarty = null;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * Setter for result.
     *
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Setter for parameters.
     *
     * @param array $parameters Parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Setter for Smarty.
     *
     * @param Smarty $smarty Smarty object
     */
    public function setSmarty(\Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Getter for parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Getter for smarty.
     *
     * @return Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     * Getter for result
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
}
