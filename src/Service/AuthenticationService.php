<?php

declare(strict_types=1);

namespace App\Service;

class AuthenticationService
{
    private string $roleId;

    private string $guildId;

    public function __construct(string $roleId, string $guildId)
    {
        $this->roleId = $roleId;
        $this->guildId = $guildId;
    }

    /**
     * @throws \JsonException
     */
    public function checkPermissions(string $userId): array
    {
        $req = [
            'user' => $userId,
            'role' => $this->roleId,
            'guild' => $this->guildId,
        ];
        $ch = curl_init('https://auth.sokoloowski.pl/');

        if (! $ch) {
            return json_decode('{"message": "failed to init cURL"}', true, 512, JSON_THROW_ON_ERROR);
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req, JSON_THROW_ON_ERROR));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);

        $response = curl_exec($ch);

        if ($response === false) {
            return json_decode('{"message": "failed to execute cURL"}', true, 512, JSON_THROW_ON_ERROR);
        }

        return json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);
    }
}
