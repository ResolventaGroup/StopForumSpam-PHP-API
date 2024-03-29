<?php

namespace Resolventa\StopForumSpamApi;

use DateTime;
use Resolventa\StopForumSpamApi\Exception\InvalidResponseFormatException;
use Resolventa\StopForumSpamApi\Exception\ResponseErrorException;

class ResponseAnalyzer
{
    private ResponseAnalyzerSettings $settings;

    public function __construct(ResponseAnalyzerSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @throws InvalidResponseFormatException
     * @throws ResponseErrorException
     */
    public function isSpammerDetected(object $response): bool
    {
        if (!isset($response->success)) {
            throw new InvalidResponseFormatException('StopForumSpam API malformed response');
        }

        if (!$response->success) {
            throw new ResponseErrorException(
                sprintf('StopForumSpam API invalid response with error: %s', $response->error)
            );
        }

        $spamFlagsCount = 0;
        $types = ['email', 'username', 'ip'];
        foreach ($types as $type) {
            if (isset($response->$type) && $this->isSpam($response->$type)) {
                $spamFlagsCount++;
            }
        }

        return $spamFlagsCount >= $this->settings->getMinSpamFlagsCount();
    }

    private function isSpam(object $typeInfo): bool
    {
        if ($this->wasNeverSeenAsSpam($typeInfo)) {
            return false;
        }

        if ($this->isSpamConfidenceScoreAboveThreshold($typeInfo)) {
            return true;
        }

        if ($this->wasRecentlySeenAsSpam($typeInfo) && $this->wasFrequentlySeenAsSpam($typeInfo)) {
            return true;
        }

        return false;
    }

    private function wasNeverSeenAsSpam(object $info): bool
    {
        return !$info->appears;
    }

    private function isSpamConfidenceScoreAboveThreshold(object $info): bool
    {
        return $info->confidence >= $this->settings->getConfidenceThreshold();
    }

    private function wasRecentlySeenAsSpam(object $info): bool
    {
        $lastSeen = DateTime::createFromFormat('Y-m-d H:i:s', $info->lastseen);
        $now = new DateTime();
        $differenceInDays = $now->diff($lastSeen)->format('%a');

        return $differenceInDays < $this->settings->getFlagLastSeenDaysAgo();
    }

    private function wasFrequentlySeenAsSpam(object $info): bool
    {
        return $info->frequency >= $this->settings->getMinFlagAppearanceFrequency();
    }
}
