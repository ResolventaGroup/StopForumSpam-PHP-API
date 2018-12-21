<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Resolventa\StopForumSpamApi\StopForumSpamApi;

class SubmitReportTest extends TestCase
{
    private const API_KEY = '';

    public function testSubmitSpamReport(): void
    {
        $this->markTestSkipped('You must set API key to run this test');

        $stopForumSpamApi = new StopForumSpamApi(self::API_KEY);
        $result = $stopForumSpamApi->submitSpamReport('testname', '157.34.44.1', 'test@test-domain.com', 'sorry, this is just a test');

        $this->assertTrue($result);
    }
}