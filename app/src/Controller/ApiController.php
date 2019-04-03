<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Predis\Client as RedisClient;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController {
    /**
     * List visible harbors with their weather info URIs.
     *
     * @Route("/list", name="api_list")
     */
    public function list()
    {
        $redisUrl = $this->getParameter('redis_url');

        $redisClient = new RedisClient($redisUrl);

        $harbors = $redisClient->keys('harbor.*.info');

        $result = [];

        foreach ($harbors as $_harbor) {
            $harbor = $redisClient->get($_harbor);

            if ($harbor && json_decode($harbor)) {
                $harborKeyData = explode('.', $_harbor);

                $harbor = json_decode($harbor, true);

                $result[] = [
                    'harbor_id' => $harbor['id'],
                    'harbor_name' => $harbor['name'],
                    'harbor_image' => isset($harbor['image']) ? $harbor['image'] : null,
                    'weather_info_uri' => $this->generateUrl('weather_info', [
                        'uuid' => $harborKeyData[1]
                    ])
                ];
            }
        }

        return $this->json($result);
    }

    /**
     * List visible harbors with their weather info URIs.
     *
     * @Route("/weather-info/{uuid}", name="weather_info")
     */
    public function weatherInfo($uuid)
    {
        $redisUrl = $this->getParameter('redis_url');

        $redisClient = new RedisClient($redisUrl);

        $weatherInfo = $redisClient->get('harbor.' . $uuid . '.weather');

        if (!$weatherInfo) {
            return $this->json(['success' => false, 'message' => 'No weather info for this harbor found.'], Response::HTTP_NOT_FOUND);
        }

        $weatherInfo = json_decode($weatherInfo, true);

        return $this->json($weatherInfo);
    }
}
