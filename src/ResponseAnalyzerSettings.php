<?php

namespace Resolventa\StopForumSpamApi;

class ResponseAnalyzerSettings
{
    private $minSpamFlagsCount = 1;
    private $minFlagAppearanceFrequency = 5;
    private $flagLastSeenDaysAgo = 7;
    private $confidenceThreshold = 90;

    public function getMinSpamFlagsCount(): int
    {
        return $this->minSpamFlagsCount;
    }

    public function setMinSpamFlagsCount(int $minSpamFlagsCount): void
    {
        $this->minSpamFlagsCount = $minSpamFlagsCount;
    }

    public function getMinFlagAppearanceFrequency(): int
    {
        return $this->minFlagAppearanceFrequency;
    }

    public function setMinFlagAppearanceFrequency(int $minFlagAppearanceFrequency): void
    {
        $this->minFlagAppearanceFrequency = $minFlagAppearanceFrequency;
    }

    public function getFlagLastSeenDaysAgo(): int
    {
        return $this->flagLastSeenDaysAgo;
    }

    public function setFlagLastSeenDaysAgo(int $flagLastSeenDaysAgo): void
    {
        $this->flagLastSeenDaysAgo = $flagLastSeenDaysAgo;
    }

    public function getConfidenceThreshold(): int
    {
        return $this->confidenceThreshold;
    }

    public function setConfidenceThreshold(int $confidenceThreshold): void
    {
        $this->confidenceThreshold = $confidenceThreshold;
    }
}
