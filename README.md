# StopForumSpam PHP API

[![Latest Stable Version](https://poser.pugx.org/resolventa/stopforumspam-php-api/v/stable)](https://packagist.org/packages/resolventa/stopforumspam-php-api)
[![Total Downloads](https://poser.pugx.org/resolventa/stopforumspam-php-api/downloads)](https://packagist.org/packages/resolventa/stopforumspam-php-api)
[![License](https://poser.pugx.org/resolventa/stopforumspam-php-api/license)](https://packagist.org/packages/resolventa/stopforumspam-php-api)

Composer friendly PHP API library and response analyzer for StopForumSpam service.

## Requirements

* PHP >= 7.1
* cURL extension

## Installation

```bash
composer require resolventa/stopforumspam-php-api
```

## Usage

See `example.php` and `tests/UseCaseTest.php` for usage examples.

```php
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
```

### Response analyzer
The library is included with an analyzer class to check StopForumSpam API response and
decide if user is spammer or not.

#### Default analyzer settings
See [StopForumSpam API documentation](https://www.stopforumspam.com/usage) to understand 
given analyzer settings.
* `$confidenceThreshold = 90` If response confidence equal or above `$confidenceThreshold`, user 
   is detected as spammer.
* `$minFlagAppearanceFrequency = 5` If flag (ip, email, username) appears in spam reports 
   less than `$minFlagAppearanceFrequency` value, user is NOT detected as spammer.
* `$flagLastSeenDaysAgo = 7` If flag (ip, email, username) was last time reported as spam 
   more than `$flagLastSeenDaysAgo` days ago, user is NOT detected as spammer.
* `$minSpamFlagsCount = 1` Minimum number of flags (ip, email, username) detected as spam
  to detect user as spammer. 

#### Analyzer usage
```php
// Create analyzer settings with default values
$settings = new ResponseAnalyzerSettings();

// Update any setting with your preferable value
$settings->setMinSpamFlagsCount(2);
$settings->setMinFlagAppearanceFrequency(10);
$settings->setFlagLastSeenDaysAgo(14);
$settings->setConfidenceThreshold(70);

// Start analyzer with given settings
$analyzer = new ResponseAnalyzer(new ResponseAnalyzerSettings());

// Validate response with the give analyzer settings
if($analyzer->isSpammerDetected($response)) {
    // Throw away that spam registration
}
```

## Submit spam reports
To submit spam reports you need to get StopForumSpam API key.
For usage example see `test/SubmitReportTest.php`

```php
// Instantiate StopForumSpamApi with API key
$stopForumSpamApi = new StopForumSpamApi(self::API_KEY);

// Submit report
$result = $stopForumSpamApi->submitSpamReport(string $username, string $ip, string $email, string $evidence);
```