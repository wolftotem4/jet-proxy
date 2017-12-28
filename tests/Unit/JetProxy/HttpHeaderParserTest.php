<?php

namespace Tests\Unit\JetProxy;

use JetProxy\HttpHeaderParser;
use PHPUnit\Framework\TestCase;

class HttpHeaderParserTest extends TestCase
{
    public function testParse()
    {
        $header = <<<HEADER
Accept-Ranges: bytes
Content-Type: text/html;
 charset=utf-8
Cache-Control: private
HEADER;

        $expects = [
            ['key' => 'Accept-Ranges', 'value' => 'bytes'],
            ['key' => 'Content-Type', 'value' => "text/html;\r\n charset=utf-8"],
            ['key' => 'Cache-Control', 'value' => 'private'],
        ];

        $this->assertEquals($expects, HttpHeaderParser::parse($header));
    }
}
