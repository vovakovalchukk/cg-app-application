<?php

namespace CG\CourierAdapter\Implementation;

use CG\CourierAdapter\Provider\Implementation\ParamsMapperProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ParamsMapperProcessorTest extends TestCase
{
    /** @var ParamsMapperProcessor */
    protected $paramsMapperProcessor;

    private const GIVEN_PARAMS = [
        'AccountInformation' => [
            'postcodeValidation' => 'No',
        ]
    ];

    private const EXPECTED_PARAMS = [
        'AccountInformation' => [
            'postcodeValidation' => '0',
        ]
    ];

    private const EXPECTED_RULES =[
        // Courier name (DPD) we want to apply the rule
        "dpd-ca" => [
            // Parameters structure we target
            'AccountInformation' => [
                'postcodeValidation' => ['value' => 'no', 'replace' => '0'],
            ],
        ],

        // Courier name (DPD Local) we want to apply the rule
        "interlink-ca" => [
            // Parameters structure we target
            'AccountInformation' => [
                'postcodeValidation' => ['value' => 'no', 'replace' => '0'],
            ],
        ]
    ];

    public function setUp()
    {
        $this->paramsMapperProcessor = new ParamsMapperProcessor();
    }

    private function mapperMock(string $givenChannel)
    {
        $mappedParams = $this->paramsMapperProcessor->runParamsMapper($givenChannel, self::GIVEN_PARAMS);
        $this->assertEquals(self::EXPECTED_PARAMS, $mappedParams);
    }

    public function testRunParamsMapperOverDpdCaSuccess()
    {
        $this->mapperMock('dpd-ca');
    }

    public function testRunParamsMapperOverDpdLocalSuccess()
    {
        $this->mapperMock('interlink-ca');
    }

    public function testRulesAreNotChangedWithoutBeingTested()
    {
        $mapperProcessor= new ReflectionClass(ParamsMapperProcessor::class);
        $constants = $mapperProcessor->getConstants();
        $rules = $constants['RULES'];
        $this->assertEquals(self::EXPECTED_RULES, $rules);
    }
}
