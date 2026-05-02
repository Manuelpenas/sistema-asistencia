<?php
/**
 * SimpleXLSX - Minimal XLSX reader without external dependencies
 * For reading Excel files in the attendance system
 */
class SimpleXLSX {
    private $sheets = [];
    private $shared_strings = [];

    public static function parse($filename) {
        $obj = new self();
        if ($obj->load($filename)) {
            return $obj;
        }
        return false;
    }

    private function load($filename) {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($filename) !== TRUE) {
            return false;
        }

        $this->readSharedStrings($zip);
        $this->readSheet($zip);
        $zip->close();

        return !empty($this->sheets);
    }

    private function readSharedStrings($zip) {
        $strings = $zip->getFromName('xl/sharedStrings.xml');
        if ($strings) {
            $xml = simplexml_load_string($strings);
            foreach ($xml->si as $string) {
                $this->shared_strings[] = (string)$string->t;
            }
        }
    }

    private function readSheet($zip) {
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheet) return;

        $xml = simplexml_load_string($sheet);
        $xml->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $row_data = [];
            $cell_idx = 0;
            foreach ($row->c as $cell) {
                $coord = (string)$cell['r'];

                while ($this->getColIndex($coord) > $cell_idx) {
                    $row_data[] = '';
                    $cell_idx++;
                }

                if (isset($cell['t']) && (string)$cell['t'] === 's') {
                    $idx = (int)$cell->v;
                    $row_data[] = isset($this->shared_strings[$idx]) ? $this->shared_strings[$idx] : '';
                } else {
                    $row_data[] = isset($cell->v) ? (string)$cell->v : '';
                }
                $cell_idx++;
            }
            $rows[] = $row_data;
        }
        $this->sheets = $rows;
    }

    private function getColIndex($coord) {
        preg_match('/^([A-Z]+)/', $coord, $matches);
        $col = $matches[1];
        $index = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    public function rows() {
        return $this->sheets;
    }
}
