<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// proto3 lang guide https://developers.google.com/protocol-buffers/docs/proto3
namespace Paydoo\Weather;

/**
 */
class WeatherClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Paydoo\Weather\GetWeatherByDateAndStationRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function getWeatherByStation(\Paydoo\Weather\GetWeatherByDateAndStationRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/paydoo.Weather/getWeatherByStation',
        $argument,
        ['\Paydoo\Weather\WeatherConditionsList', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Paydoo\Weather\GetWeatherByDateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function getWeatherAvgStatistics(\Paydoo\Weather\GetWeatherByDateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/paydoo.Weather/getWeatherAvgStatistics',
        $argument,
        ['\Paydoo\Weather\WeatherConditionsList', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Paydoo\Weather\EmptyRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function getLastAvailableWeatherDt(\Paydoo\Weather\EmptyRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/paydoo.Weather/getLastAvailableWeatherDt',
        $argument,
        ['\Paydoo\Weather\LastAvailableWeatherTime', 'decode'],
        $metadata, $options);
    }

}
