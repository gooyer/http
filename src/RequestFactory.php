<?php

declare(strict_types=1);

namespace Gooyer\Http;

use Symfony\Component\HttpFoundation\ParameterBag;

class RequestReader
{
    public static function read(\Swoole\Http\Request $request): Request
    {
        $headers = array_combine(array_map(function ($key) {
            return 'HTTP_'.str_replace('-', '_', $key);
        }, array_keys($request->header)), array_values($request->header));

        $server = array_change_key_case(array_merge($request->server, $headers), CASE_UPPER);

        if ($trustedProxies = $server['TRUSTED_PROXIES'] ?? false) {
            Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
        }

        if ($trustedHosts = $server['TRUSTED_HOSTS'] ?? false) {
            Request::setTrustedHosts(explode(',', $trustedHosts));
        }

        $gooyerRequest = new Request(
            $swooleRequest->get ?? [],
            $swooleRequest->post ?? [],
            [],
            $swooleRequest->cookie ?? [],
            $swooleRequest->files ?? [],
            $server,
            $request->rawContent()
        );

        if (0 === strpos($gooyerRequest->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($gooyerRequest->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'], true)
        ) {
            parse_str($gooyerRequest->getContent(), $data);
            $gooyerRequest->request = new ParameterBag($data);
        }

        return $gooyerRequest;
    }
}