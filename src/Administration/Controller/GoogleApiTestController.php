<?php

namespace Appflix\DewaShop\Administration\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SettingsController
 * @package Appflix\DewaShop\Administration\Controller
 * @Route(defaults={"_routeScope"={"api"}})
 */
class GoogleApiTestController
{
    /**
     * @Route("/api/dewa/google-api-test", name="api.dewa.google-api-test", methods={"POST"})
     */
    public function googleApiTest(Request $request): JsonResponse
    {
        $apiKey = $request->get('AppflixDewaShop.config.googleMapsApiKey');

        if (!$apiKey) {
            throw new \Exception("API Key empty.");
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=' . $apiKey;

        $client = curl_init();
        curl_setopt($client, CURLOPT_URL, $url);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($client);
        curl_close($client);

        if (!$response) {
            throw new \Exception("Connection timed out.");
        }

        $response = json_decode($response, true);

        if (!empty($response['error_message'])) {
            throw new \Exception($response['error_message']);
        }

        return new JsonResponse([]);
    }
}
