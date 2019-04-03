<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Client\HarbaDevClient;
use Predis\Client as RedisClient;

class UpdateHarborsCommand extends Command
{
    protected static $defaultName = 'update:harbors';
    protected $harbaDevClient;
    protected $redisUrl;

    public function __construct(HarbaDevClient $harbaDevClient, $redisUrl)
    {
        $this->harbaDevClient = $harbaDevClient;
        $this->redisUrl = $redisUrl;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = null;

        try {
            $response = $this->harbaDevClient->get('/harbors/visible');
        } catch (\Exception $e) {
            return $output->writeln(($e->getMessage()));
        }

        $body = $response->getBody()->getContents();

        if (!$response || !json_decode($body)) {
            return $output->writeln('Unable to parse response body');
        }

        $redis = new RedisClient($this->redisUrl);

        $harbors = $body = json_decode($body, true);

        $output->writeln('Found '. count($harbors) . ' harbors');
        $output->writeln('====================================');

        foreach ($harbors as $key => $harbor) {
            $key = 'harbor.' . $harbor['id']. '.info';

            $redis->set($key, json_encode($harbor));

            $output->writeln('Setting up ' . $harbor['name'] . ' info');
        }

        $output->writeln('====================================');
        $output->writeln('Update finished');
    }
}
