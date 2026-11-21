<?php

namespace Resolventa\StopForumSpamApi;

use Resolventa\StopForumSpamApi\Exception\CurlException;
use Resolventa\StopForumSpamApi\Exception\NoApiKeyException;
use Resolventa\StopForumSpamApi\Exception\SubmitSpamReportException;

class StopForumSpamApi
{
    public const DEFAULT_CHECK_API_URL = 'https://www.stopforumspam.com/api';
    public const DEFAULT_REPORT_API_URL = 'https://www.stopforumspam.com/add';

    private const CONNECT_TIMEOUT_SECONDS = 2;
    private const TIMEOUT_SECONDS = 3;

    private $username;
    private $email;
    private $ip;
    private $apiKey;
    private $checkApiUrl;
    private $reportApiUrl;

    public function __construct(
        ?string $apiKey = null,
        string $checkApiUrl = self::DEFAULT_CHECK_API_URL,
        string $reportApiUrl = self::DEFAULT_REPORT_API_URL
    ) {
        if ($checkApiUrl === '' || $reportApiUrl === '') {
            throw new \InvalidArgumentException('StopForumSpam API URL must not be empty');
        }

        $this->apiKey = $apiKey;
        $this->checkApiUrl = $checkApiUrl;
        $this->reportApiUrl = $reportApiUrl;
    }

    public function checkEmail(string $email): self
    {
        $this->email = urlencode($email);

        return $this;
    }

    public function checkIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function checkUsername(string $username): self
    {
        $this->username = urlencode($username);

        return $this;
    }

    public function getCheckResponse(): \stdClass
    {
        $ch = curl_init($this->buildCheckUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_SECONDS);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $message = sprintf('Unexpected curl error: %s %s', curl_errno($ch), curl_error($ch));
            curl_close($ch);
            throw new CurlException($message);
        }
        curl_close($ch);

        return json_decode($response);
    }

    private function buildCheckUrl(): string
    {
        $query = [];
        if (!empty($this->email)) {
            $query['email'] = $this->email;
        }
        if (!empty($this->ip)) {
            $query['ip'] = $this->ip;
        }
        if (!empty($this->username)) {
            $query['username'] = $this->username;
        }

        $queryString = http_build_query($query);

        return $this->checkApiUrl."?$queryString&json";
    }

    public function submitSpamReport(string $username, string $ip, string $email, string $evidence): bool
    {
        if (!$this->apiKey) {
            throw new NoApiKeyException("You can't submit spam report without API Key");
        }

        $postFields = [];
        $postFields['api_key'] = $this->apiKey;
        $postFields['username'] = $username;
        $postFields['ip_addr'] = $ip;
        $postFields['email'] = $email;
        $postFields['evidence'] = $evidence;

        $ch = curl_init($this->reportApiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_SECONDS);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $message = sprintf('Unexpected curl error: %s %s', curl_errno($ch), curl_error($ch));
            curl_close($ch);
            throw new CurlException($message);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($httpCode !== 200) {
            throw new SubmitSpamReportException(sprintf('
                Can\'t submit spam report to StopForumSpam service. Response HTTP code is %s. Response body: %s',
                    $httpCode, $response
                )
            );
        }
        curl_close($ch);

        return true;
    }
}