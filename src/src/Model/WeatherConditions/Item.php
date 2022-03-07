<?php
declare(strict_types=1);

namespace App\Model\WeatherConditions;

use JsonSerializable;
use DateTime;

class Item implements JsonSerializable
{
    protected DateTime $dateTime;

    protected string $stationId;

    protected float $temperature;

    protected float $humidity;

    protected float $wind;

    protected ?float $rain;

    protected ?int $light;

    protected string|int $battery;

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTime $dateTime): self
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    public function getStationId(): string
    {
        return $this->stationId;
    }

    public function setStationId(string $stationId): self
    {
        $this->stationId = $stationId;
        return $this;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getHumidity(): float
    {
        return $this->humidity;
    }

    public function setHumidity(float $humidity): self
    {
        $this->humidity = $humidity;
        return $this;
    }

    public function getWind(): float
    {
        return $this->wind;
    }

    public function setWind(float $wind): self
    {
        $this->wind = $wind;
        return $this;
    }

    public function getRain(): ?float
    {
        return $this->rain;
    }

    public function setRain(?float $rain): self
    {
        $this->rain = $rain;
        return $this;
    }

    public function getLight(): ?int
    {
        return $this->light;
    }

    public function setLight(?int $light): self
    {
        $this->light = $light;
        return $this;
    }

    public function getBattery(): string|int
    {
        return $this->battery;
    }

    public function setBattery(string|int $battery): self
    {
        $this->battery = $battery;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'time' => $this->getDateTime()->format('Y-m-d H:i:s'),
            'temperature' => $this->getTemperature(),
            'humidity' => $this->getHumidity(),
            'wind' => $this->getWind(),
            'rain' => $this->getRain(),
            'light' => $this->getLight(),
            'battery' => $this->getBattery(),
        ];
    }
}
