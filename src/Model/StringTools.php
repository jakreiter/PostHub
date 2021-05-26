<?php


namespace App\Model;

use Symfony\Component\String\Slugger\AsciiSlugger;

class StringTools
{


    public static function slug($stringToSlug): string
    {
        $slugger = new AsciiSlugger('en',
            ['en' => [
                '%' => 'percent',
                '&' => 'and',
                '@' => 'at'
            ]]);
        return $slugger->slug($stringToSlug);
    }

    public static function removeReplyQuotes($emailText)
    {
        $emailText = str_replace("\r\n", "\n", $emailText);
        $text = preg_replace('#(^\w.+:\n)?(^>.*(\n|$))+#mi', "", $emailText);
        return $text;
    }

    public static function randomString()
    {
        $sa = array('a', 'e', 'i', 'o', 'u', 'y', '4', '3', '8', '2', '9');
        $sp = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'z');
        srand((double)microtime() * 1000000);
        $has1 = '';
        for ($i = 0; $i < ((rand() % 4) + 3); $i++) {
            $has1 = $has1 . $sp[rand() % count($sp)] . $sa[rand() % count($sa)];
        }
        return $has1;
    }


    public static function onlyDigits($text)
    {
        $leaveString = '_1234567890';

        $resuString = '';

        for ($i = 0; $i < strlen($text); $i++) {
            $poz = strpos($leaveString, $text[$i]);
            if ($poz > 0) {
                $resuString = $resuString . $leaveString[$poz];
            }
        }
        return $resuString;
    }


    public static function onlySafeChars($text)
    {
        $leaveString = ' abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-1234567890.';
        $text = strtolower($text);
        $text = str_replace('/', '_', $text);
        $text = str_replace(' ', '-', $text);
        $text = str_replace('ó', 'o', $text);
        $text = str_replace('ą', 'a', $text);
        $text = str_replace('ż', 'z', $text);
        $text = str_replace('ź', 'z', $text);
        $text = str_replace('ś', 's', $text);
        $text = str_replace('ć', 'c', $text);
        $text = str_replace('ł', 'l', $text);
        $text = str_replace('ę', 'e', $text);
        $text = str_replace('ń', 'n', $text);

        $resuString = '';

        for ($i = 0; $i < strlen($text); $i++) {
            $poz = strpos($leaveString, $text[$i]);
            if ($poz > 0) {
                $resuString = $resuString . $leaveString[$poz];
            }
        }
        return $resuString;
    }

    public static function onlySafeChars2($text)
    {
        $leaveString = ' ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ_-1234567890.';
        $text = strtoupper($text);
        $text = str_replace('/', '_', $text);
        $text = str_replace(' ', '_', $text);
        $text = str_replace('Ó', 'O', $text);
        $text = str_replace('Ą', 'A', $text);
        $text = str_replace('Ż', 'Z', $text);
        $text = str_replace('Ź', 'Z', $text);
        $text = str_replace('Ś', 'S', $text);
        $text = str_replace('Ć', 'C', $text);
        $text = str_replace('Ł', 'L', $text);
        $text = str_replace('Ę', 'E', $text);
        $text = str_replace('Ń', 'N', $text);
        $resuString = '';

        for ($i = 0; $i < strlen($text); $i++) {
            $poz = strpos($leaveString, $text[$i]);
            if ($poz > 0) {
                $resuString = $resuString . $leaveString[$poz];
            }
        }
        return $resuString;
    }

    public static function lengthLimit($inputString, $maxLen = 32)
    {
        $strLen = strlen($inputString);
        if ($strLen <= $maxLen) return $inputString;
        $newString = substr($inputString, 0, $maxLen);
        return $newString;
    }

    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function unclawMailHeaderString($hString)
    {
       return str_replace(['<', '>'], ['', ''], $hString);
    }

}