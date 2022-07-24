<?php

namespace App\Service;

class StandardizePowerhouseService
{
    /**
     * @throws \JsonException
     */
    public function aggregateDataByDate(array $data, array $powerhouses, int $fromDate, int $toDate): array
    {
        // Harmonize field names
        $data = $this->replaceArrayKeys($data);

        // For each Central, check that there is one line per 15 min. otherwise => take the previous value and insert a line corresponding to this time slot
        $dataByStepOf15 = $this->dividePowerByStepOf15($data, $powerhouses, $fromDate);

        // Add the "power" of each line of each powerhouse per 15 min
        return $this->mergeData($dataByStepOf15);
    }

    /**
     * @throws \JsonException
     */
    private function replaceArrayKeys(array $array): array
    {
        $arrayWithFinalKeys = [];
        foreach ($array as $key => $secondaryArray) {
            $json = str_replace(array('debut', 'start_time', 'fin', 'end_time', 'value', 'valeur'), array('start', 'start', 'end', 'end', 'power', 'power'), json_encode($secondaryArray, JSON_THROW_ON_ERROR));
            $arrayWithFinalKeys[$key] = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        }
        return $arrayWithFinalKeys;
    }

    /**
     * @param array $data
     * @param array $powerhouses
     * @param int $fromDate
     * @return array
     */
    private function dividePowerByStepOf15(array $data, array $powerhouses, int $fromDate): array
    {
        $result = [];
        foreach ($data as $key => $powerhouseArray) {
            $startDate = $fromDate;
            $indexOfKey = array_search($key, array_column($powerhouses, 'name'), true);
            $divider = $powerhouses[$indexOfKey]['step'] / 15;

            $i = 0;
            foreach ($powerhouseArray as $item) {
                $endDate = strtotime('+15 minutes', $startDate);
                while ($item->start > $startDate) {
                    $result[$key][] = [
                        'start' => $startDate,
                        'end' => $endDate,
                        'power' => $result[$key][$i - 1]['power'],
                    ];
                    $startDate = strtotime('+15 minutes', $startDate);
                    $endDate = strtotime('+15 minutes', $startDate);
                    $i++;
                }

                $result[$key][] = [
                    'start' => $startDate,
                    'end' => $endDate,
                    'power' => $item->power / $divider,
                ];
                $startDate = strtotime('+15 minutes', $startDate);
                $i++;
            }
        }
        return $result;
    }

    /**
     * @param array $data
     * @return array
     **/
    private function mergeData(array $data): array
    {
        $result = [];
        foreach ($data as $powerhouseArray) {
            foreach ($powerhouseArray as $key => $item) {
                $item['power'] = isset($result[$key]) ? $result[$key]['power'] + $item['power'] : $item['power'];
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
