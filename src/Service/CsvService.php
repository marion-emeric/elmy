<?php

namespace App\Service;

class CsvService
{
    /**
     * @param $sourceFile
     * @param string $delimiter
     * @param string $enclosure
     * @return array|false
     */
    public function convertCsvToArray($sourceFile, string $delimiter = ',', string $enclosure = '"'): bool|array
    {
        if (!file_exists($sourceFile) || !is_readable($sourceFile)) {
            return FALSE;
        }

        $header = NULL;
        $data = array();

        if (($handle = fopen($sourceFile, 'rb')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter, $enclosure)) !== FALSE) {
                if (!$header) {
                    $header = $row;
                } else if (count($header) === count($row)) {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }
}
