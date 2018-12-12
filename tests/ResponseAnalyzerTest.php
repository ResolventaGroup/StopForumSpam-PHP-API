<?php
namespace Tests;

use Faker\Factory as FackerFactory;
use PHPUnit\Framework\TestCase;
use Resolventa\StopForumSpamApi\Exception\InvalidResponseFormatException;
use Resolventa\StopForumSpamApi\Exception\ResponseErrorException;
use Resolventa\StopForumSpamApi\ResponseAnalyzer;
use Resolventa\StopForumSpamApi\ResponseAnalyzerSettings;
use \stdClass;

class ResponseAnalyzerTest extends TestCase
{
    public function testInvalidResponseFormat()
    {
        $apiResponse = (object)['response'];

        $this->expectException(InvalidResponseFormatException::class);
        $analyzer = new ResponseAnalyzer($apiResponse, new ResponseAnalyzerSettings());
        $analyzer->isSpammerDetected();
    }

    public function testResponseError()
    {
        $apiResponse = (object)[
            'success' => 0,
            'error' => 'error description'
        ];

        $this->expectException(ResponseErrorException::class);
        $analyzer = new ResponseAnalyzer($apiResponse, new ResponseAnalyzerSettings());
        $analyzer->isSpammerDetected();
    }

    /**
     * @dataProvider spammerConfidenceDataProvider
     * @dataProvider spammerDateAndFrequencyDataProvider
     */
    public function testCatchSpammer(stdClass $apiResponse)
    {
        $analyzer = new ResponseAnalyzer($apiResponse, new ResponseAnalyzerSettings());

        $this->assertTrue($analyzer->isSpammerDetected());
    }

    /**
     * @dataProvider normalUserResponseDataProvider
     */
    public function testDoNotCatchNormalUser(stdClass $apiResponse)
    {
        $analyzer = new ResponseAnalyzer($apiResponse, new ResponseAnalyzerSettings());

        $this->assertTrue( !$analyzer->isSpammerDetected() );
    }

    public function testCatchSpammerWithMultipleFlagsSetting()
    {
        $faker = FackerFactory::create();
        $settings = new ResponseAnalyzerSettings();

        $apiResponse = (object)[
            'success' => 1,
            'email' => (object)[
                'lastseen' => $faker->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
                'frequency' => 10,
                'appears' => 1,
                'confidence' => random_int($settings->getConfidenceThreshold(), 100)
            ],
            'username' => (object)[
                'lastseen' => $faker->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
                'frequency' => 10,
                'appears' => 1,
                'confidence' => random_int($settings->getConfidenceThreshold(), 100)
            ]

        ];

        $settings->setMinSpamFlagsCount(2);
        $analyzer = new ResponseAnalyzer($apiResponse, $settings);
        $this->assertTrue($analyzer->isSpammerDetected());

        $settings->setMinSpamFlagsCount(3);
        $analyzer = new ResponseAnalyzer($apiResponse, $settings);
        $this->assertTrue(!$analyzer->isSpammerDetected());
    }

    public function normalUserResponseDataProvider(): array
    {
        $faker = FackerFactory::create();
        return [
            ['Never seen as spammer response' => (object)[
                'success' => 1,
                'username' => (object)[
                    'appears' => 0,
                    'frequency' => 0
                ]
            ]],
            ['Low confidence score response' => (object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => '2010-12-10 11:38:37',
                    'frequency' => 0,
                    'appears' => 1,
                    'confidence' => random_int(0, 89)
                ]
            ]],
            ['Not frequently seen as spammer' => (object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => $faker->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
                    'frequency' => random_int(1, 4),
                    'appears' => 1,
                    'confidence' => random_int(0, 89)
                ]
            ]],
            ['Was not recently seen as spammer' => (object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => $faker->dateTimeBetween('-5 years', '-7 days')->format('Y-m-d H:i:s'),
                    'frequency' => 100,
                    'appears' => 1,
                    'confidence' => random_int(0, 89)
                ]
            ]],
        ];
    }

    public function spammerConfidenceDataProvider(): array
    {
        return [
            [(object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => '2018-12-10 11:38:37',
                    'frequency' => 2,
                    'appears' => 1,
                    'confidence' => 90
                ],
            ]],
            [(object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => '2018-12-10 11:38:37',
                    'frequency' => 2,
                    'appears' => 1,
                    'confidence' => 100
                ],
            ]],
            [(object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => '2018-12-10 11:38:37',
                    'frequency' => 22,
                    'appears' => 1,
                    'confidence' => random_int(90, 100)
                ],
            ]],
        ];
    }

    public function spammerDateAndFrequencyDataProvider(): array
    {
        $faker = FackerFactory::create();
        return [
            [(object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => $faker->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
                    'frequency' => 5,
                    'appears' => 1,
                    'confidence' => 0
                ],
            ]],
            [(object)[
                'success' => 1,
                'email' => (object)[
                    'lastseen' => $faker->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
                    'frequency' => random_int(6, 1000),
                    'appears' => 1,
                    'confidence' => 0
                ],
            ]],

        ];

    }


}