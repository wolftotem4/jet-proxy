<?php

namespace JetProxy;

class HttpHeaderParser
{
    /**
     * @param  string  $header
     * @return array
     */
    public static function parse($header)
    {
        $headers   = [];
        $lastKey   = '';
        $lastValue = '';

        foreach (preg_split('/(*BSR_ANYCRLF)\R/', $header) as $line) {
            $isStartWithSpace = (substr($line, 0, 1) == ' ');
            if (! $isStartWithSpace && count($segments = explode(':', $line, 2)) == 2) {
                if ($lastKey) {
                    $headers[] = ['key' => $lastKey, 'value' => trim($lastValue)];
                }

                list($lastKey, $lastValue) = $segments;
                $lastValue .= "\r\n";
            } else {
                $lastValue .= $line . "\r\n";
            }
        }

        if ($lastKey) {
            $headers[] = ['key' => $lastKey, 'value' => trim($lastValue)];
        }

        return $headers;
    }
}