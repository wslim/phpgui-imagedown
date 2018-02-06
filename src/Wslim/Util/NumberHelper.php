<?php
namespace Wslim\Util;

class NumberHelper
{
    /**
     * to int array, from [2, 3-5, 9] to [2, 3, 4, 5, 9]
     * @param string $data
     * @param string $glue
     * 
     * @return array
     */
    static public function toIntArray($data, $glue=',|')
    {
        $result = [];
        $arr = preg_split("/[$glue]+/", str_replace(['"', '\''], '', $data));
        if ($arr) foreach ($arr as $k => $v) {
            if (strpos($v, '-')) {
                $items = explode('-', $v, 2);
                if (isset($items[1])) {
                    for ($i = intval($items[0]); $i<=intval($items[1]); $i++) {
                        $result[] = $i;
                    }
                }
            } else {
                $result[] = intval($v);
            }
        }
        return $result;
    }
}