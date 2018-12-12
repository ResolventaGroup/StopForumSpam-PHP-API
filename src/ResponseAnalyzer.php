<?php

namespace Resolventa\StopForumSpamApi;

use Carbon\Carbon;
use Resolventa\StopForumSpamApi\Exception\InvalidResponseFormatException;
use Resolventa\StopForumSpamApi\Exception\ResponseErrorException;
use \stdClass;

class ResponseAnalyzer
{
    private $response = [];
    private $settings;

    public function __construct(stdClass $response, ResponseAnalyzerSettings $settings)
    {
        if(!isset($response->success)) {
            throw new InvalidResponseFormatException('StopForumSpam API malformed response');
        }

        if(!$response->success) {
            throw new ResponseErrorException('StopForumSpam API invalid response with error: ' . $response->error);
        }

        $this->response = $response;
        $this->settings = $settings;
    }

    public function isSpammerDetected(): bool
    {
        $spamFlagsCount = 0;
        $types = ['email', 'username', 'ip'];
        foreach ($types as $type) {
            if($this->isSpam($type)) {
                $spamFlagsCount++;
            }
        }

        if($spamFlagsCount >= $this->settings->getMinSpamFlagsCount()) {
            return true;
        }

        return false;
    }

    private function isSpam(string $type): bool
    {
        if(!isset($this->response->$type)) {
            return false;
        }

        $info = $this->response->$type;

        if($this->wasNeverSeenAsSpam($info)) {
            return false;
        }

        if($this->isSpamConfidenceScoreAboveThreshold($info)) {
            return true;
        }

        if($this->wasRecentlySeenAsSpam($info) && $this->wasFrequentlySeenAsSpam($info)) {
            return true;
        }

        return false;
    }

    private function wasNeverSeenAsSpam(stdClass $info): bool
    {
        return !$info->appears;
    }

    private function isSpamConfidenceScoreAboveThreshold(stdClass $info): bool
    {
        if($info->confidence >= $this->settings->getConfidenceThreshold()) {
            return true;
        }

        return false;
    }

    private function wasRecentlySeenAsSpam(stdClass $info): bool
    {
        $lastseen = Carbon::createFromFormat('Y-m-d H:i:s', $info->lastseen);
        $now = Carbon::now();

        if($now->diffInDays($lastseen) < $this->settings->getFlagLastSeenDaysAgo()) {
            return true;
        }

        return false;
    }

    private function wasFrequentlySeenAsSpam(stdClass $info): bool
    {
        $frequency = $info->frequency;
        if($frequency >= $this->settings->getMinFlagAppearanceFrequency()) {
            return true;
        }

        return false;
    }

}