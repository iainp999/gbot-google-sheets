<?php

namespace ScraperBot\GoogleSheets\Subscriber;

use Google\Exception;
use ScraperBot\Event\CrawlCompleteEvent;
use ScraperBot\Event\CrawledEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to crawl events and write the data to google sheets.
 *
 * @package ScraperBot\MySqlStorage\Subscriber
 */
class CrawlSubscriber implements EventSubscriberInterface {

    private $crawlData = [];

    public function onCrawled(CrawledEvent $event) {
        $data = $event->getRawCrawldata();
        $this->crawlData[] = $data;
    }

    public function onCrawlComplete(CrawlCompleteEvent $event) {
        if (count($this->crawlData) < 1) {
            return;
        }

        // Create the Google sheets client.
        $client = $this->getClient();

        // Create sheet
        $service = new \Google_Service_Sheets($client);

        // Extract headers from data.
        $values[] = array_keys(current($this->crawlData));

        // Remove the associative indexes from the captured entries.
        foreach ($this->crawlData as $data) {
            $entry = [];

            foreach($data as $value) {
                $entry[] = $value;
            }

            $values[] = $entry;
        }

        $spreadsheet = new \Google_Service_Sheets_Spreadsheet([
            'properties' => [
                'title' => 'GlitcherBot Data'
            ]
        ]);

        // TODO: store ID on first run as config, read from YAML.
        $spreadsheet = $service->spreadsheets->create($spreadsheet, [
            'fields' => 'spreadsheetId'
        ]);

        printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);

        // Write Data.
        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'RAW'
        ];
        $result = $service->spreadsheets_values->append($spreadsheet->spreadsheetId, 'Sheet1!A:E', $body, $params);

        printf("%d cells appended.", $result->getUpdates()->getUpdatedCells());
    }

    public static function getSubscribedEvents() {
        return [
            CrawledEvent::NAME => 'onCrawled',
            CrawlCompleteEvent::NAME => 'onCrawlComplete'
        ];
    }

    /**
     * Returns an authorized API client.
     *
     * @return \Google_Client the authorized client object
     * @throws Exception
     */
    private function getClient() {
        require __DIR__ . '/../../vendor/autoload.php';

        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(\Google_Service_Sheets::DRIVE);
        $client->setAuthConfig(__DIR__ . '/../../credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = __DIR__ . '/../../token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

}
