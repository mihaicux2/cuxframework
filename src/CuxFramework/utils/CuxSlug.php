<?php

/**
 * CuxSlug class file
 * 
 * @package Utils
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\utils;

use CuxFramework\utils\CuxSingleton;

/**
 * Simple class that can be used to generate URL-friendly and camelCase strings
 */
class CuxSlug extends CuxSingleton {

    /**
     * Setup instance properties
     * @param array $config
     */
    public static function config(array $config) {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
    }

    /**
     * Convert strings to camelCase notation
     * @param string $str The text to be converted
     * @param array $noStrip The list of non-alpha-numerical characters to be allowed
     * @return string
     */
    public static function camelCase(string $str, array $noStrip = []): string {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);

        return $str;
    }
    
    /**
     * Convert strings to Slug notation
     * @param string $text The text to be converted
     * @param string $separator Word separator
     * @param bool $toLowerCase Turn the resulting text to lower case
     * @return string
     */
    static public function slugify(string $text, string $separator = '_', bool $toLowerCase = true): string {

        $matrix = array(
            'й' => 'i', 'ц' => 'c', 'у' => 'u', 'к' => 'k', 'е' => 'e', 'н' => 'n',
            'г' => 'g', 'ш' => 'sh', 'щ' => 'shch', 'з' => 'z', 'х' => 'h', 'ъ' => '',
            'ф' => 'f', 'ы' => 'y', 'в' => 'v', 'а' => 'a', 'п' => 'p', 'р' => 'r',
            'о' => 'o', 'л' => 'l', 'д' => 'd', 'ж' => 'zh', 'э' => 'e', 'ё' => 'e',
            'я' => 'ya', 'ч' => 'ch', 'с' => 's', 'м' => 'm', 'и' => 'i', 'т' => 't',
            'ь' => '', 'б' => 'b', 'ю' => 'yu', 'ү' => 'u', 'қ' => 'k', 'ғ' => 'g',
            'ә' => 'e', 'ң' => 'n', 'ұ' => 'u', 'ө' => 'o', 'Һ' => 'h', 'һ' => 'h',
            'і' => 'i', 'ї' => 'ji', 'є' => 'je', 'ґ' => 'g',
            'Й' => 'I', 'Ц' => 'C', 'У' => 'U', 'Ұ' => 'U', 'Ө' => 'O', 'К' => 'K',
            'Е' => 'E', 'Н' => 'N', 'Г' => 'G', 'Ш' => 'SH', 'Ә' => 'E', 'Ң ' => 'N',
            'З' => 'Z', 'Х' => 'H', 'Ъ' => '', 'Ф' => 'F', 'Ы' => 'Y', 'В' => 'V',
            'А' => 'A', 'П' => 'P', 'Р' => 'R', 'О' => 'O', 'Л' => 'L', 'Д' => 'D',
            'Ж' => 'ZH', 'Э' => 'E', 'Ё' => 'E', 'Я' => 'YA', 'Ч' => 'CH', 'С' => 'S',
            'М' => 'M', 'И' => 'I', 'Т' => 'T', 'Ь' => '', 'Б' => 'B', 'Ю' => 'YU',
            'Ү' => 'U', 'Қ' => 'K', 'Ғ' => 'G', 'Щ' => 'SHCH', 'І' => 'I', 'Ї' => 'YI',
            'Є' => 'YE', 'Ґ' => 'G',
            'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'î' => 'i', 'Î' => 'I',
            'ş' => 's', 'Ş' => 'S', 'ţ' => 't', 'Ţ' => 'T', 'ș' => 's', 'Ș' => 'S',
            'ț' => 't', 'Ţ' => 'T'
        );
        foreach ($matrix as $from => $to) {
            $text = mb_eregi_replace($from, $to, $text);
        }
        
        if ($toLowerCase){
//            $text = mb_strtolower($text);
            $text = strtolower($text);
        }
        
//        $text = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', $text);
//        $text = preg_replace('~[^\\pL\d]+~u', $separator, $text);
        $text = preg_replace('~[^\\pL\d]+~u', $separator, $text);
        $flip = $separator == '-' ? '_' : '-';
        $text = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $text);
        $text = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $text);
        return trim($text, $separator);
    }
    
    /**
     * Convert strings to URL-friendly notation
     * @param string $string The text to be converted
     * @param string $suffix Add a suffix to the resulting text
     * @return string
     */
    public static function seourelize(string $string, $suffix = ''): string {
        $string = static::convertEncoding($string);

        $string = strip_tags($string);
        // Preserve escaped octets.
        $string = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $string);
        // Remove percent signs that are not part of an octet.
        $string = str_replace('%', '', $string);
        // Restore octets.
        $string = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $string);

        $string = strtolower($string);
        $string = preg_replace('/&.+?;/', '', $string); // kill entities
        $string = str_replace('.', '-', $string);
        $string = preg_replace('/[^%a-z0-9 _-]/', '', $string);
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('|-+|', '-', $string);
        $string = trim($string, '-');

        return $string . $suffix;
    }

    /**
     * Map cyrillic characters to latin counter-parts
     * @param type $text The text to be converted
     * @return string
     */
    public static function convertEncoding($text = ""): string {
        $text = static::remove_accents($text);

        $roman = array("Sch", "sch", 'Yo', 'Zh', 'Kh', 'Ts', 'Ch', 'Sh', 'Yu', 'ya', 'yo', 'zh', 'kh', 'ts', 'ch', 'sh', 'yu', 'ya', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', '', 'Y', '', 'E', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', '', 'y', '', 'e');
        $cyrillic = array("Щ", "щ", 'Ё', 'Ж', 'Х', 'Ц', 'Ч', 'Ш', 'Ю', 'я', 'ё', 'ж', 'х', 'ц', 'ч', 'ш', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Ь', 'Ы', 'Ъ', 'Э', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'ь', 'ы', 'ъ', 'э');
        $text = str_replace($cyrillic, $roman, $text);

        return $text;
    }

    /**
     * Checks if a string is UTF-8 encoded
     * @param string $str The string to be checked
     * @return bool
     */
    public static function seems_utf8(string $str): bool {
        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80)
                $n = 0;# 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0)
                $n = 1;# 110bbbbb
            elseif (($c & 0xF0) == 0xE0)
                $n = 2;# 1110bbbb
            elseif (($c & 0xF8) == 0xF0)
                $n = 3;# 11110bbb
            elseif (($c & 0xFC) == 0xF8)
                $n = 4;# 111110bb
            elseif (($c & 0xFE) == 0xFC)
                $n = 5;# 1111110b
            else
                return false;# Does not match any model
            for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
                if (( ++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    public static function remove_accents($string): string {
        if (!preg_match('/[\x80-\xff]/', $string))
            return $string;

        if (static::seems_utf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
                chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
                chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
                chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
                chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
                chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
                chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
                chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
                chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
                chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
                chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
                chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
                chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
                chr(195) . chr(191) => 'y',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
                chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
                chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
                chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
                chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
                chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
                chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
                chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
                chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
                chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
                chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
                chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
                chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
                chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
                chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
                chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
                chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
                chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
                chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
                chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
                chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
                chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
                chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
                chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
                chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
                chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
                chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
                chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
                chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
                chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
                chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
                chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
                chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
                chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
                chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
                chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
                chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
                chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
                chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
                // Euro Sign
                chr(226) . chr(130) . chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194) . chr(163) => '');

            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                    . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                    . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
                    . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                    . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                    . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                    . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                    . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                    . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                    . chr(252) . chr(253) . chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    /**
     * Sorts an array with respect to the URL-friendly converted content
     * @param array $items
     * @param string $order
     * @return int
     */
    public static function asort(array &$items, string $order = "asc") {

        if (!is_array($items)) {
            return false;
        }

        if (class_exists("Collator")) {
            $collator = new Collator('en_US');
        } else {
            $collator = false;
        }

        return uasort($items, function($a, $b) use ($property, $order, $collator) {
            if ($collator) {
                $cmp = $collator->compare($a, $b);
            } else {
                $cmp = strcmp(static::seourelize($a), static::seourelize($b));
            }
            return $order === 'asc' ? $cmp : -$cmp;
        });
    }

    /**
     * Sorts a multidimensional array by a given property, with respect to the URL-friendly converted content
     * @param array $items
     * @param string $property
     * @param string $order
     * @return boolean
     */
    public static function sortByProperty(array &$items, string $property, string $order = "asc") {
        if (!is_array($items)) {
            return false;
        }

        if (class_exists("Collator")) {
            $collator = new Collator('en_US');
        } else {
            $collator = false;
        }

        return usort($items, function($a, $b) use ($property, $order, $collator) {
            if ($collator) {
                $cmp = $collator->compare($a[$property], $b[$property]);
            } else {
                $cmp = strcmp(static::seourelize($a[$property]), static::seourelize($b[$property]));
            }
            return $order === 'asc' ? $cmp : -$cmp;
        });
    }

}
