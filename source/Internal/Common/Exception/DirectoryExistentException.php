<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Common\Exception;

/**
 * @internal
 *
 */
class DirectoryExistentException extends \Exception
{
    /** @var string $directoryAlreadyExistent */
    private $directoryAlreadyExistent = '';

    /**
     * @param string $directoryAlreadyExistent
     */
    public function setDirectoryAlreadyExistent(string $directoryAlreadyExistent)
    {
        $this->directoryAlreadyExistent = $directoryAlreadyExistent;
    }

    /**
     * @return string
     */
    public function getDirectoryAlreadyExistent(): string
    {
        return $this->directoryAlreadyExistent;
    }


}
