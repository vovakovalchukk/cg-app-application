<?php
use CG\Template\Renderer\Pdf\Label as LabelRenderer;
use CG\Template\Renderer\Pdf\Label\Repository;
use CG\Template\Renderer\Pdf\Label\Storage\Redis;
use CG\Template\Renderer\Pdf\Label\Storage\Runtime;

return [
    'di' => [
        'instance' => [
            LabelRenderer::class => [
                'parameters' => [
                    'labelPdfStorage' => Repository::class,
                ],
            ],
            Repository::class => [
                'parameters' => [
                    'storage' => Redis::class,
                    'repository' => Runtime::class,
                ],
            ],
            Redis::class => [
                'parameters' => [
                    'client' => 'image-cache_redis',
                ],
            ],
        ],
    ],
];