<?php

namespace ResultSystems\Paypal\Services;

class PaypalSandboxRequestService extends PaypalRequestService
{
    /**
     * sandbox ou produção?
     *
     * @var bool
     */
    protected $sandbox = true;

    public function setSandbox($sandbox = true)
    {
        return $this;
    }
}
