<?php

namespace App\Service;

class AuthenticationService
{

    /**
     * @throws \JsonException
     */
    public function checkPermissions(string $userId, string $roleId, string $guildId): object
    {
        $req = [
            "user" => $userId,
            "guild" => $guildId,
            "role" => $roleId
        ];
        $ch = curl_init('https://auth.sokoloowski.pl/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req, JSON_THROW_ON_ERROR));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        return json_decode(curl_exec($ch), false, 512, JSON_THROW_ON_ERROR);
    }
}