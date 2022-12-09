<?php

namespace TueFind\Crypt;

/** 
 * Described as "base64url" in the original Base64 RFC:
 * https://www.rfc-editor.org/rfc/rfc4648.txt
 *
 * Basically use base64, but instead of / and + we use - and _.
 * These characters can cause problems, especially /, even if URL encoded.
 * 
 * To allow /, it would be necessary to change apache config
 * which we do not want to do for security reasons:
 * https://httpd.apache.org/docs/2.4/mod/core.html#allowencodedslashes
 */
class Base64Url
{
    // The size of these arrays must be the same,
    // will be used for search / replace.
    const BASE64_SPECIFIC_CHARACTERS = ['/', '+'];
    const BASE64_URL_SPECIFIC_CHARACTERS = ['-', '_'];
    
    public function encodeString(string $inputString): string
    {
        $base64 = base64_encode($inputString);
        $base64Url = str_replace(static::BASE64_SPECIFIC_CHARACTERS, static::BASE64_URL_SPECIFIC_CHARACTERS, $base64);
        return $base64Url;
    } 
    
    public function decodeString(string $inputString): string
    {
        $base64 = str_replace(static::BASE64_URL_SPECIFIC_CHARACTERS, static::BASE64_SPECIFIC_CHARACTERS, $inputString);
        $outputString = base64_decode($base64);
        return $outputString;
    }
}
