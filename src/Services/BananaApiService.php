<?php

namespace App\Services;

use JsonException;
use RuntimeException;

final class BananaApiService
{
    private const API_URL = "https://marcconrad.com/uob/banana/api.php";

    public function getChallenge(): array
    {
        $curl = curl_init(self::API_URL);
        if ($curl === false) {
            throw new RuntimeException("Could not initialize the Banana API request.");
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ["Accept: application/json"],
        ]);

        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException("Banana API request failed: {$error}");
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("Banana API returned HTTP {$status}.");
        }

        try {
            $challenge = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("Banana API returned invalid JSON.", 0, $exception);
        }

        if (
            !is_array($challenge)
            || !isset($challenge["question"])
            || !is_string($challenge["question"])
            || !filter_var($challenge["question"], FILTER_VALIDATE_URL)
            || !array_key_exists("solution", $challenge)
            || !is_numeric($challenge["solution"])
        ) {
            throw new RuntimeException("Banana API returned an invalid challenge.");
        }

        return [
            "question" => $challenge["question"],
            "solution" => (int) $challenge["solution"],
        ];
    }
}
