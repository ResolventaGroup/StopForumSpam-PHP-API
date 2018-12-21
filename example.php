<?php
use Resolventa\StopForumSpamApi\ResponseAnalyzer;
use Resolventa\StopForumSpamApi\ResponseAnalyzerSettings;
use Resolventa\StopForumSpamApi\StopForumSpamApi;
use Resolventa\StopForumSpamApi\Exception\StopForumSpamApiException;

include 'vendor/autoload.php';

$stopForumSpamApi = new StopForumSpamApi();

/**
 * Set up Email, IP and Username to be checked
 * You can use only one, two or all three together
 *
 * Use $stopForumSpamApi->checkIp('135.34.23.33'); if only IP address need to be checked
 */
$stopForumSpamApi
    ->checkEmail('test@test-domain.com')
    ->checkIp('135.34.23.33')
    ->checkUsername('someGreatUsername');

/**
 * Get Response from StopForumSpam service
 */
$response = $stopForumSpamApi->getCheckResponse();

/**
 * You can analyze response on your own or use Resolventa\StopForumSpamApi\ResponseAnalyzer to make decision
 * See ResponseAnalyzer usage below
 */
var_dump($response);

/**
 * Create analyzer instance with $response and default analyser settings
 *
 * Or customize analyser settings to your needs
 * $settings = new ResponseAnalyzerSettings();
 * $settings->setMinSpamFlagsCount(2);
 * $settings->setMinFlagAppearanceFrequency(10);
 * $settings->setFlagLastSeenDaysAgo(14);
 * $settings->setConfidenceThreshold(70);
 */
$analyzer = new ResponseAnalyzer(new ResponseAnalyzerSettings());

try {
    if($analyzer->isSpammerDetected($response)) {
        echo "Spam user detected. \n";
    } else {
        echo "User is ok. \n";
    }
} catch (StopForumSpamApiException $e) {
    echo 'Bad response: ',  $e->getMessage(), "\n";
    exit();
}
