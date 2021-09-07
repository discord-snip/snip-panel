<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class DiscordService
{
    private RequestStack $requestStack;
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    /**
     * @throws \JsonException
     */
    public function apiRequest(string $url, array $post = [], array $headers = []): object
    {
        $session = $this->requestStack->getSession();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $headers[] = 'Accept: application/json';

        if ($session->has('access_token')) {
            $headers[] = 'Authorization: Bearer ' . $session->get('access_token');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        return json_decode($response, false, 512, JSON_THROW_ON_ERROR);
    }
}