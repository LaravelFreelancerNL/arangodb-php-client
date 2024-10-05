<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Prometheus\Prometheus;
use Psr\Http\Message\ResponseInterface;
use stdClass;

trait HandlesResponses
{
    use HandlesJson;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function decodeResponse(ResponseInterface $response): stdClass
    {
        $contentType = null;
        $rawContentType = $response->getHeader('Content-type');
        if (is_array($rawContentType) && sizeOf($rawContentType) > 0) {
            $contentType = $rawContentType[0];
        }

        return match ($contentType) {
            "application/json; charset=utf-8" => $this->decodeJsonResponse($response),
            "text/plain; charset=utf-8" => $this->decodeTextResponse($response),
            default => (object) $response->getBody()->getContents(),
        };
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function decodeTextResponse(ResponseInterface $response): stdClass
    {
        $prometheus = new Prometheus();
        return $prometheus->parseStream($response);
    }
}
