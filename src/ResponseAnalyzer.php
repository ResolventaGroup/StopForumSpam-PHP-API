<?php

namespace Resolventa\StopForumSpamApi;

use Resolventa\StopForumSpamApi\Exception\InvalidResponseFormatException;
use Resolventa\StopForumSpamApi\Exception\ResponseErrorException;
use \stdClass;

class ResponseAnalyzer
{
    private $settings;

    public function __construct(ResponseAnalyzerSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @throws InvalidResponseFormatException
     * @throws ResponseErrorException
     */
    public function isSpammerDetected(stdClass $response): bool
    {
        if(!isset($response->success)) {
            throw new InvalidResponseFormatException('StopForumSpam API malformed response');
        }

        if(!$response->success) {
            throw new ResponseErrorException('StopForumSpam API invalid response with error: ' . $response->error);
        }

        $spamFlagsCount = 0;
        $types = ['email', 'username', 'ip'];
        foreach ($types as $type) {
            if(isset($response->$type) && $this->isSpam($response->$type)) {
                $spamFlagsCount++;
            }
        }

        if($spamFlagsCount >= $this->settings->getMinSpamFlagsCount()) {
            return true;
        }

        return false;
    }

    private function isSpam(stdClass $typeInfo): bool
    {
        if($this->wasNeverSeenAsSpam($typeInfo)) {
            return false;
        }

        if($this->isSpamConfidenceScoreAboveThreshold($typeInfo)) {
            return true;
        }

        if($this->wasRecentlySeenAsSpam($typeInfo) && $this->wasFrequentlySeenAsSpam($typeInfo)) {
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
        $lastseen = \DateTime::createFromFormat('Y-m-d H:i:s', $info->lastseen);
        $now = new \DateTime();
        $differenceInDays = $now->diff($lastseen)->format('%a');

        if($differenceInDays < $this->settings->getFlagLastSeenDaysAgo()) {
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