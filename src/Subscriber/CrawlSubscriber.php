<?php

namespace ScraperBot\GoogleSheets\Subscriber;

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
        // Load GSheets credentials
        // Write Data
    }

    public static function getSubscribedEvents() {
        return [
            CrawledEvent::NAME => 'onCrawled',
            CrawlCompleteEvent::NAME => 'onCrawlComplete'
        ];
    }

}
