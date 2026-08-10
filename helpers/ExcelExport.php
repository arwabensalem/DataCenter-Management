<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Export Excel (SpreadsheetML XML) compatible Microsoft Excel / LibreOffice.
 */
final class ExcelExport
{
    /**
     * @param string $filename
     * @param list<array{title: string, headers: list<string>, rows: list<list<scalar|null>>}> $sheets
     */
    public static function download(string $filename, array $sheets): never
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

        foreach ($sheets as $sheet) {
            $name = self::xml(mb_substr($sheet['title'], 0, 31));
            $xml .= '<Worksheet ss:Name="' . $name . '"><Table>' . "\n";

            $xml .= '<Row>';
            foreach ($sheet['headers'] as $h) {
                $xml .= '<Cell><Data ss:Type="String">' . self::xml((string) $h) . '</Data></Cell>';
            }
            $xml .= '</Row>' . "\n";

            foreach ($sheet['rows'] as $row) {
                $xml .= '<Row>';
                foreach ($row as $cell) {
                    if (is_int($cell) || is_float($cell)) {
                        $xml .= '<Cell><Data ss:Type="Number">' . $cell . '</Data></Cell>';
                    } else {
                        $xml .= '<Cell><Data ss:Type="String">' . self::xml((string) ($cell ?? '')) . '</Data></Cell>';
                    }
                }
                $xml .= '</Row>' . "\n";
            }

            $xml .= '</Table></Worksheet>' . "\n";
        }

        $xml .= '</Workbook>';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $xml;
        exit;
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
