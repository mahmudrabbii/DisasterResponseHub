<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function fetchCurrent(?string $city, ?string $district = null, ?string $country = null): ?array
    {
        return $this->fetchByQuery($this->composeLocationQuery($city, $district, $country));
    }

    public function fetchByQuery(?string $locationQuery): ?array
    {
        $locationCandidates = $this->locationCandidatesFromQuery($locationQuery);
        $cacheKey = 'weather.current.' . md5(mb_strtolower(implode('|', $locationCandidates)));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($locationCandidates) {
            $coordinates = $this->resolveCoordinates($locationCandidates);

            if (!$coordinates) {
                return null;
            }

            try {
                $response = $this->httpClient()->timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                    'current_weather' => 'true',
                    'timezone' => 'auto',
                ]);
            } catch (\Throwable $throwable) {
                return null;
            }

            if (!$response->successful()) {
                return null;
            }

            $currentWeather = $response->json('current_weather');

            if (!$currentWeather) {
                return null;
            }

            return [
                'location' => $coordinates['name'],
                'temperature' => $currentWeather['temperature'] ?? null,
                'wind_speed' => $currentWeather['windspeed'] ?? null,
                'weather_code' => $currentWeather['weathercode'] ?? null,
                'condition' => $this->weatherCondition((int) ($currentWeather['weathercode'] ?? -1)),
                'time' => $currentWeather['time'] ?? null,
            ];
        });
    }

    private function composeLocationQuery(?string $city, ?string $district = null, ?string $country = null): string
    {
        return collect([$city, $district, $country])
            ->filter()
            ->map(function (string $value): string {
                return trim($value);
            })
            ->implode(', ');
    }

    private function resolveCoordinates(array $locationCandidates): ?array
    {
        foreach ($locationCandidates as $locationQuery) {
            try {
                $response = $this->httpClient()->timeout(8)->get('https://geocoding-api.open-meteo.com/v1/search', [
                    'name' => $locationQuery,
                    'count' => 1,
                    'language' => 'en',
                    'format' => 'json',
                ]);
            } catch (\Throwable $throwable) {
                continue;
            }

            if (!$response->successful()) {
                continue;
            }

            $result = $response->json('results.0');

            if (!$result || !isset($result['latitude'], $result['longitude'])) {
                continue;
            }

            $parts = array_filter([
                $result['name'] ?? null,
                $result['admin1'] ?? null,
                $result['country'] ?? null,
            ]);

            return [
                'latitude' => $result['latitude'],
                'longitude' => $result['longitude'],
                'name' => implode(', ', $parts),
            ];
        }

        return null;
    }

    private function locationCandidatesFromQuery(?string $locationQuery): array
    {
        $locationQuery = trim((string) $locationQuery);

        $parts = $locationQuery === ''
            ? collect()
            : collect(preg_split('/\s*,\s*/', $locationQuery, -1, PREG_SPLIT_NO_EMPTY))
                ->filter()
                ->map(function (string $value): string {
                    return trim($value);
                })
                ->values();

        $candidates = [
            $locationQuery,
            $parts->implode(', '),
            $parts->take(2)->implode(', '),
            $parts->first(),
            $parts->skip(1)->implode(', '),
            config('services.weather.default_location', 'Dhaka, Bangladesh'),
        ];

        return array_values(array_unique(array_filter(array_map(function (string $value): string {
            return trim($value);
        }, $candidates))));
    }

    private function httpClient()
    {
        $client = Http::acceptJson();

        if (app()->environment(['local', 'testing'])) {
            return $client->withoutVerifying();
        }

        return $client;
    }

    private function weatherCondition(int $weatherCode): string
    {
        $conditions = [
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45 => 'Fog',
            48 => 'Depositing rime fog',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            56 => 'Light freezing drizzle',
            57 => 'Dense freezing drizzle',
            61 => 'Slight rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            66 => 'Light freezing rain',
            67 => 'Heavy freezing rain',
            71 => 'Slight snow fall',
            73 => 'Moderate snow fall',
            75 => 'Heavy snow fall',
            77 => 'Snow grains',
            80 => 'Slight rain showers',
            81 => 'Moderate rain showers',
            82 => 'Violent rain showers',
            85 => 'Slight snow showers',
            86 => 'Heavy snow showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with slight hail',
            99 => 'Thunderstorm with heavy hail',
        ];

        return $conditions[$weatherCode] ?? 'Unknown conditions';
    }
}
