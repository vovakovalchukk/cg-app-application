<?php
namespace Orders\Controller\Helpers;

use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;

class Usage
{
    /** @var UsageService $usageService */
    protected $usageService;

    public function __construct(UsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * @throws UsageExceeded
     */
    public function checkUsage()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }
    }
}
