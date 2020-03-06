<?php

use CG\Amazon\BrowseNode\Category\Usage\Storage as BrowseNodeCategoryUsageStorage;
use CG\Amazon\Category\Mapper as AmazonCategoryMapper;
use CG\Amazon\Category\Storage\Db as AmazonCategoryStorageDb;
use CG\Amazon\Category\StorageInterface as AmazonCategoryStorage;
use CG\Amazon\Category\VariationTheme\Mapper as AmazonVariationThemeMapper;
use CG\Amazon\Category\VariationTheme\Storage\Db as AmazonVariationThemeStorageDb;
use CG\Amazon\Category\VariationTheme\StorageInterface as AmazonVariationThemeStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                AmazonCategoryStorage::class => AmazonCategoryStorageDb::class,
                AmazonVariationThemeStorage::class => AmazonVariationThemeStorageDb::class,
            ],
            AmazonCategoryStorageDb::class => [
                'parameter' => [
                    'readSql' => 'amazonReadSql',
                    'fastReadSql' => 'amazonFastReadSql',
                    'writeSql' => 'amazonWriteSql',
                    'mapper' => AmazonCategoryMapper::class
                ]
            ],
            AmazonVariationThemeStorageDb::class => [
                'parameter' => [
                    'readSql' => 'amazonReadSql',
                    'fastReadSql' => 'amazonFastReadSql',
                    'writeSql' => 'amazonWriteSql',
                    'mapper' => AmazonVariationThemeMapper::class
                ]
            ],
            BrowseNodeCategoryUsageStorage::class => [
                'parameter' => [
                    'predis' => 'reliable_redis'
                ]
            ],
        ]
    ]
];