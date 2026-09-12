<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;
    protected static ?Process $chromeDriverProcess = null;

    /**
     * Prepare for Dusk test execution.
     */
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            // Find system chromedriver path
            $driverPath = match (true) {
                file_exists('/usr/bin/chromedriver') => '/usr/bin/chromedriver',
                file_exists('/usr/local/bin/chromedriver') => '/usr/local/bin/chromedriver',
                default => null,
            };

            if ($driverPath) {
                static::startChromeDriver([$driverPath]);
            } else {
                static::startChromeDriver();
            }
        }
    }
    // Fallback connection check if port 9515 is unreachable
    protected function setUp(): void
    {
        parent::setUp();
        
        $connection = @fsockopen('127.0.0.1', 9515);
        if (! is_resource($connection)) {
            $driverPath = file_exists('/usr/bin/chromedriver') ? '/usr/bin/chromedriver' : 'chromedriver';
            static::$chromeDriverProcess = new Process([$driverPath, '--port=9515']);
            static::$chromeDriverProcess->start();
            usleep(500000); // Wait 0.5s for service to spin up
        } else {
            fclose($connection);
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        if (static::$chromeDriverProcess && static::$chromeDriverProcess->isRunning()) {
            static::$chromeDriverProcess->stop();
        }
    }

    // protected function driver(): RemoteWebDriver
    // {
    //     $options = (new ChromeOptions)->addArguments([
    //         '--window-size=1920,1080',
    //         '--disable-gpu',
    //         '--headless=new',
    //         '--no-sandbox',
    //         '--disable-dev-shm-usage',
    //     ]);

    //     return RemoteWebDriver::create(
    //         $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515', // Must be 9515 for ChromeDriver
    //         $options->toCapabilities()
    //     );
    // }
}
