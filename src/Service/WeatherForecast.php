<?php
namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WeatherForecast
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }
    public function getForecast(string $city): array
    {
        return $this->cache->get(
            "forecast_$city",
            function (ItemInterface $item) use ($city): array {
                $item->expiresAfter(1800);
                // this code only runs on a cache miss
                return $this->callSlowWeatherApi($city);
            },
        );
    }
    private function callSlowWeatherApi(string $city): array
    {
        // ... a 2-second HTTP request to the weather provider
    }
}
