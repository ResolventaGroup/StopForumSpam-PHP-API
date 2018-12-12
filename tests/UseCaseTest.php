<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Resolventa\StopForumSpamApi\ResponseAnalyzer;
use Resolventa\StopForumSpamApi\ResponseAnalyzerSettings;
use Resolventa\StopForumSpamApi\StopForumSpamApi;

class UseCaseTest extends TestCase
{
    public function testStopForumSpamApiCall()
    {
        $stopForumSpamApi = new StopForumSpamApi();
        $stopForumSpamApi
            ->checkEmail('test@test-domain.com')
            ->checkIp('135.34.23.33')
            ->checkUsername('someGreatUsername');
        $response = $stopForumSpamApi->getCheckResponse();

        $analyzer = new ResponseAnalyzer($response, new ResponseAnalyzerSettings());
        $this->assertTrue(!$analyzer->isSpammerDetected());
    }
}