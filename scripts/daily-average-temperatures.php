<?php

$main = function () {
    // get zip
    global $argv;
    $zip = (isset($argv[1])) ? $argv[1] : '84116';

    // determine outfile
    $basePath = realpath(__DIR__ . '/..');
    $outfile = "{$basePath}/out/daily-average-temperatures/{$zip}.csv";

    // ensure outfile path exists
    if (!file_exists(dirname($outfile))) {
        mkdir(dirname($outfile), 0755, true);
    }

    // get csv handle
    $csv = fopen($outfile, 'w');

    for ($month = 1; $month <= 12; $month++) {
        // get xpath
        $urlFormat = 'http://www.weather.com/weather/wxclimatology/daily/%s?climoMonth=%s';
        $url = sprintf($urlFormat, $zip, $month);
        @$xpath = new DOMXPath(DOMDocument::loadHtml(file_get_contents($url)));

        if (!isset($header)) {
            // get headers using xpath
            $header = array();
            $headerNodes = $xpath->query(
                "//table//table//table//table//td[contains(@class, 'vbgA')]/b");
            foreach ($headerNodes as $node) {
                $header[] = preg_replace('/\W+/', '', $node->textContent);
            }

            // build csv headers
            $csvHeader = array(
                'Month',
                'Day',
                'Sunrise',
                'Sunset',
                'AvgHighF',
                'AvgLowF',
                'MeanF',
                'RecordHighF',
                'RecordHighYear',
                'RecordLowF',
                'RecordLowYear',
            );

            // write csv headers
            fputcsv($csv, $csvHeader);
        }

        // get table rows using xpath
        $trNodes = $xpath->query(
            "//table//table//table//table//tr[.//td[contains(@class, 'lapAvgDataRow')]]");

        // process table rows
        foreach ($trNodes as $trNode) {
            // get values using xpath
            $tdNodes = $xpath->query("td[contains(@class, 'lapAvgDataRow')]", $trNode);
            $values = array();
            foreach ($tdNodes as $tdNode) {
                $values[] = trim($tdNode->textContent);
            }

            // combine headers and values to make record
            $record = array_combine($header, $values);

            // build csv record from record
            $pattern = '/^(-?\d+)°F( \((\d+)\))?$/'; // $1 = temp, $3 = year
            $csvRecord = array_combine($csvHeader, array(
                $month,
                $record['Day'],
                date('H:i', strtotime($record['Sunrise'])),
                date('H:i', strtotime($record['Sunset'])),
                preg_replace($pattern, '$1', $record['AvgHigh']),
                preg_replace($pattern, '$1', $record['AvgLow']),
                preg_replace($pattern, '$1', $record['Mean']),
                preg_replace($pattern, '$1', $record['RecordHigh']),
                preg_replace($pattern, '$3', $record['RecordHigh']),
                preg_replace($pattern, '$1', $record['RecordLow']),
                preg_replace($pattern, '$3', $record['RecordLow']),
            ));

            // write csv record
            fputcsv($csv, $csvRecord);
        }
    }

    // done
    fclose($csv);
};

$main();
unset($main);
return;
