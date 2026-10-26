<?php

use PHPUnit\Framework\TestCase;

/** Base for tests that talk to the running site over HTTP (XAMPP locally, or BASE_URL). */
abstract class HttpTestCase extends TestCase
{
  protected static function base(): string
  {
    return rtrim(getenv('BASE_URL') ?: 'http://localhost/atomservices', '/');
  }

  /** @return array{status:int, body:string, headers:array<string,string>, redirect:?string} */
  protected static function http(string $path, string $method = 'GET', ?array $post = null, bool $follow = false, array $requestHeaders = []): array
  {
    $url = preg_match('#^https?://#', $path) ? $path : self::base() . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    $headers = [];
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_FOLLOWLOCATION => $follow,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_HTTPHEADER => $requestHeaders,
      CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$headers) {
        if (strpos($line, ':') !== false) {
          [$k, $v] = explode(':', $line, 2);
          $headers[strtolower(trim($k))] = trim($v);
        }
        return strlen($line);
      },
    ]);
    if ($post !== null) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($ch);
    if ($body === false) {
      self::markTestSkipped('Site not reachable at ' . $url . ': ' . curl_error($ch));
    }
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL) ?: null;
    curl_close($ch);
    return ['status' => $status, 'body' => $body, 'headers' => $headers, 'redirect' => $redirect];
  }

  protected static function dom(string $html): DOMXPath
  {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?>' . $html);
    libxml_clear_errors();
    return new DOMXPath($doc);
  }
}
