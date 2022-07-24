<?php

namespace App\Controller;

use App\Service\CsvService;
use App\Service\StandardizePowerhouseService;

class PowerhouseController
{
    const POWERHOUSES = [
        [
            'name' => 'Hawes',
            'step' => 15,
            'format' => 'json'
        ],
        [
            'name' => 'Barnsley',
            'step' => 30,
            'format' => 'json'
        ],
        [
            'name' => 'Hounslow',
            'step' => 60,
            'format' => 'csv'
        ],
    ];

    function index(): string
    {
        return 'Hello Elmy!';
    }

    /**
     * @param string $from
     * @param string $to
     * @return string|false
     * @throws \JsonException
     */
    function getPowerHouse(string $from, string $to): bool|string
    {
        $fromDate = preg_replace('/from=/', '', $from);
        $toDate = preg_replace('/to=/', '', $to);

        // Get all data from Elmy API
        $data = $this->getAgregateDataFromPowerhouse(self::POWERHOUSES, $fromDate, $toDate);

        // remove 1 hour otherwise date = $from + 1h when converted to timestamp
        $fromDateTimestamp = strtotime('-1 hours',strtotime($fromDate));
        $toDateTimestamp = strtotime('-1 hours',strtotime($fromDate));

        $powerHouseService = new StandardizePowerhouseService();
        $result = $powerHouseService->aggregateDataByDate($data, self::POWERHOUSES, $fromDateTimestamp, $toDateTimestamp);

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array $powerhouses
     * @param string $from
     * @param string $to
     * @return array
     * @throws \JsonException
     */
    private function getAgregateDataFromPowerhouse(array $powerhouses, string $from, string $to): array
    {
        $data = [];
        foreach ($powerhouses as $powerhouse) {
            $data[$powerhouse['name']] = $this->callElmyAPI($powerhouse, $from, $to);
        }
        return $data;
    }

    /**
     * @param array $powerhouse
     * @param string $from
     * @param string $to
     * @return bool|string|void
     * @throws \JsonException
     */
    private function callElmyAPI(array $powerhouse, string $from, string $to)
    {
        $params = http_build_query(['from' => $from, 'to' => $to]);
        $url = sprintf('https://interview.beta.bcmenergy.fr/%s?%s', strtolower($powerhouse['name']), $params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return sprintf('Error %d returned for url %s', $httpCode, $url);
        }

        if (empty($data)) {
            echo curl_error($ch);
            exit;
        }

        if ($powerhouse['format'] === 'json') {
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->transformCsvToArray(strtolower($powerhouse['name']), $data);
    }

    /**
     * @param mixed $name
     * @param bool|string $data
     * @return array|bool
     */
    private function transformCsvToArray(mixed $name, bool|string $data): bool|array
    {
        $csvPath = sprintf('/var/www/elmy/temp/%s.csv', $name);
        file_put_contents($csvPath, $data);
        return (new CsvService())->convertCsvToArray($csvPath);
    }
}

