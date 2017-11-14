<?php

namespace Mashshare\Service\Network\Interfaces;

/**
 * Interface InterfaceShareCount
 * @package Mashshare\Service\Network\Interfaces
 */
interface InterfaceShareCount
{

    public function setRequestService();

    public function getRequestService();

    public function requestShares();

    public function getShareCount();
}