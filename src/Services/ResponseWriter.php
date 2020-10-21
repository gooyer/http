<?php

declare(strict_types=1);

namespace Gooyer\Http\Services;

use Gooyer\Http\Response;

class ResponseWriter
{
    public static function write(\Swoole\Http\Response $swooleResponse, Response $response, $end = true)
    {
        if (headers_sent()) {
            return;
        }

        foreach ($response->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        // status
        $swooleResponse->status($response->getStatusCode());

        // cookies
        foreach ($response->headers->getCookies() as $cookie) {
            $swooleResponse->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }

        if (true === $end) {
            $swooleResponse->end($response->getContent());
        } else {
            $swooleResponse->write($response->getContent());
        }
    }
}