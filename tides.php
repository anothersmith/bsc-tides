<?php

function filterNextFiveDays($csvData) {
    $now = new DateTime();
    $fiveDaysLater = new DateTime();
    $fiveDaysLater->modify('+5 days');

    $lines = explode("\n", trim($csvData));
    $header = explode(",", $lines[0]);
    $filteredLines = [implode(",", $header)];

    for ($i = 1; $i < count($lines); $i++) {
        $values = explode(",", $lines[$i]);
        $startDateStr = $values[2];

        if ($startDateStr) {
            $dateParts = explode("/", $startDateStr);
            $month = (int)$dateParts[0];
            $day = (int)$dateParts[1];
            $year = (int)$dateParts[2];

            $eventDate = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));

            if ($eventDate && $eventDate >= $now && $eventDate <= $fiveDaysLater) {
                $filteredLines[] = implode(",", $values);
            }
        }
    }

    return $filteredLines; //Return Array, not string.
}

function processCSVFile($filePath) {
    if (file_exists($filePath)) {
        $csvData = file_get_contents($filePath);
        $filteredData = filterNextFiveDays($csvData);

        if (!empty($filteredData)) {
            echo "<table style='border-collapse: collapse; width: 80%; margin: 20px auto; font-family: sans-serif;'>";

            $header = explode(",", $filteredData[0]);

            // Header Row (Combined Subject and Date):
            echo "<tr><th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;'>HighTide</th>";
            for ($i = 1; $i < 5; $i++) {
                echo "<th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;'>". htmlspecialchars($header[$i]) ."</th>";
            }
            echo "</tr>";

            // Data Rows:
            for ($i = 1; $i < count($filteredData); $i++) {
                $row = explode(",", $filteredData[$i]);
                $row[1] = str_replace('"', '', $row[1]);
                echo "<tr><td style='border: 1px solid #ddd; padding: 8px;'>". htmlspecialchars($row[1]) ."</td>";
                for ($j = 2; $j < 6; $j++) {
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>". htmlspecialchars($row[$j]) ."</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='text-align: center; margin-top: 20px;'>No data found within the next five days.</p>";
        }
    } else {
        echo "<p style='text-align: center; margin-top: 20px;'>Error: File not found - ". htmlspecialchars($filePath) ."</p>";
    }
}

$csvFilePath = dirname(__FILE__) . '/../tides.csv';

processCSVFile($csvFilePath);

?>