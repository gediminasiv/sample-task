<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Client\OpenWeatherClient;
use App\Client\HarbaDevClient;
use Predis\Client as RedisClient;

class UpdateWeatherCommand extends Command
{
    protected static $defaultName = 'update:weather';
    protected $harbaDevClient;
    protected $openWeatherClient;
    protected $openWeatherApiKey;

    public function __construct(
        OpenWeatherClient $openWeatherClient,
        HarbaDevClient $harbaDevClient,
        $openWeatherApiKey,
        $redisUrl)
    {
        $this->openWeatherClient = $openWeatherClient;
        $this->harbaDevClient = $harbaDevClient;
        $this->openWeatherApiKey = $openWeatherApiKey;
        $this->redisUrl = $redisUrl;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redisClient = new RedisClient($this->redisUrl);

        $harborIds = $redisClient->keys('harbor.*.info');

        $output->writeln('Fetching data from '. count($harborIds) . ' harbors');
        $output->writeln('====================================');

        foreach ($harborIds as $harborId) {
            $_harbor = $redisClient->get($harborId);

            if (!json_decode($_harbor)) {
                continue;
            }

            $harbor = json_decode($_harbor, true);

            $mainRequest = $this->getMainData($harbor['lat'], $harbor['lon']);

            if ($mainRequest) {
                $key = 'harbor.' . $harbor['id'] . '.weather';

                $redisClient->set($key, $mainRequest);
                $output->writeln('Saved harbor ' . $harbor['name'] . ' weather');
                continue;
            }

            // in this place we should add a fallback request, but I was unable to acquire google API key, so didn't do it.
        }
        $output->writeln('====================================');
        $output->writeln('Update finished');
    }

    protected function getMainData($lat, $lon) {
        $response = null;

        try {
            $response = $this->openWeatherClient->get('/data/2.5/weather', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'APPID' => $this->openWeatherApiKey
                ]
            ]);
        } catch (\Exception $e) {
            return false;
        }

        $body = $response->getBody()->getContents();

        if (!$response || !json_decode($body)) {
            return false;
        }


        return $body;
    }

    protected function getFallbackData($lat, $lon) {

    }
}
