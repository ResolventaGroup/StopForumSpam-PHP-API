<?php
namespace Resolventa\StopForumSpamApi;

class ResponseAnalyzerSettings
{
    private $minSpamFlagsCount = 1;
    private $minFlagAppearanceFrequency = 5;
    private $flagLastSeenDaysAgo = 7;
    private $confidenceThreshold = 90;

    /**
     * @return int
     */
    public function getMinSpamFlagsCount(): int
    {
        return $this->minSpamFlagsCount;
    }

    /**
     * @param int $minSpamFlagsCount
     */
    public function setMinSpamFlagsCount(int $minSpamFlagsCount): void
    {
        $this->minSpamFlagsCount = $minSpamFlagsCount;
    }

    /**
     * @return int
     */
    public function getMinFlagAppearanceFrequency(): int
    {
        return $this->minFlagAppearanceFrequency;
    }

    /**
     * @param int $minFlagAppearanceFrequency
     */
    public function setMinFlagAppearanceFrequency(int $minFlagAppearanceFrequency): void
    {
        $this->minFlagAppearanceFrequency = $minFlagAppearanceFrequency;
    }

    /**
     * @return int
     */
    public function getFlagLastSeenDaysAgo(): int
    {
        return $this->flagLastSeenDaysAgo;
    }

    /**
     * @param int $flagLastSeenDaysAgo
     */
    public function setFlagLastSeenDaysAgo(int $flagLastSeenDaysAgo): void
    {
        $this->flagLastSeenDaysAgo = $flagLastSeenDaysAgo;
    }

    /**
     * @return int
     */
    public function getConfidenceThreshold(): int
    {
        return $this->confidenceThreshold;
    }

    /**
     * @param int $confidenceThreshold
     */
    public function setConfidenceThreshold(int $confidenceThreshold): void
    {
        $this->confidenceThreshold = $confidenceThreshold;
    }

}