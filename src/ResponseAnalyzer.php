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

    private function isSpam(stdClass $typeInfo): bool
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

    private function wasNeverSeenAsSpam(stdClass $info): bool
    {
        return !$info->appears;
    }

    private function isSpamConfidenceScoreAboveThreshold(stdClass $info): bool
    {
        return $info->confidence >= $this->settings->getConfidenceThreshold();
    }

    private function wasRecentlySeenAsSpam(stdClass $info): bool
    {
        $lastSeen = \DateTime::createFromFormat('Y-m-d H:i:s', $info->lastseen);
        $now = new \DateTime();
        $differenceInDays = $now->diff($lastSeen)->format('%a');

        return $differenceInDays < $this->settings->getFlagLastSeenDaysAgo();
    }

    private function wasFrequentlySeenAsSpam(stdClass $info): bool
    {
        return $info->frequency >= $this->settings->getMinFlagAppearanceFrequency();
    }
}
