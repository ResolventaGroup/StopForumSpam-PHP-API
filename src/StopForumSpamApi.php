<?php

namespace Resolventa\StopForumSpamApi;

use Resolventa\StopForumSpamApi\Exception\NoApiKeyException;
use Resolventa\StopForumSpamApi\Exception\SubmitSpamReportException;

class StopForumSpamApi
{
    private $username;
    private $email;
    private $ip;
    private $apiKey;
    private $checkApiUrl = 'http://api.stopforumspam.org/api';
    private $reportApiUrl = 'http://www.stopforumspam.com/add';

    public function __construct(string $apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    public function checkEmail(string $email): StopForumSpamApi
    {
        $this->email = $email;
        return $this;
    }

    public function checkIp(string $ip): StopForumSpamApi
    {
        $this->ip = $ip;
        return $this;
    }

    public function checkUsername(string $username): StopForumSpamApi
    {
        $this->username = $username;
        return $this;
    }

    public function getCheckResponse()
    {
        $ch = curl_init($this->buildCheckUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    private function buildCheckUrl(): string
    {
        $query = [];
        if(!empty($this->email)) {
            $query['email'] = $this->email;
        }
        if(!empty($this->ip)) {
            $query['ip'] = $this->ip;
        }
        if(!empty($this->username)) {
            $query['username'] = $this->username;
        }

        $queryString = http_build_query($query);

        return $this->checkApiUrl . "?$queryString&json";
    }

    public function submitSpamReport(string $username, string $ip, string $email, string $evidence): bool
    {
        if (!$this->apiKey) {
            throw new NoApiKeyException("You can't submit spam report without API Key");
        }

        $postFields['api_key'] = $this->apiKey;
        $postFields['username'] = $username;
        $postFields['ip_addr'] = $ip;
        $postFields['email'] = $email;
        $postFields['evidence'] = $evidence;

        $ch = curl_init($this->reportApiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \RuntimeException('Unexpected curl error: ' . curl_errno($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpCode != 200) {
            throw new SubmitSpamReportException("
                Can't submit spam report to StopForumSpam service. Response HTTP code is $httpCode. 
                Response body: $response
            ");
        }
        curl_close($ch);

        return true;
    }
}