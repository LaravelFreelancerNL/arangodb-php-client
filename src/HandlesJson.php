<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Psr7\StreamWrapper;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Psr\Http\Message\ResponseInterface;
use stdClass;

trait HandlesJson
{
    /**
     * @throws ArangoException
     */
    public function jsonEncode(mixed $data): string
    {
        $options = 0;
        if (empty($data)) {
            $options = JSON_FORCE_OBJECT;
        }

        $response = json_encode($data, $options);

        if ($response === false) {
            throw new ArangoException('JSON encoding failed with error: ' . json_last_error_msg(), json_last_error());
        }

        return $response;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function decodeJsonResponse(ResponseInterface $response): stdClass
    {
        $contentLength = $response->getHeaderLine('Content-Length');
        $sizeSwitch = $this->getConfig('jsonStreamDecoderThreshold');
        if ($contentLength < $sizeSwitch) {
            return json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        }

        $decodedResponse = new stdClass();

        $phpStream = StreamWrapper::getResource($response->getBody());
        $decoder = new ExtJsonDecoder(true);
        $decodedStream = Items::fromStream($phpStream, ['decoder' => $decoder]);

        foreach ($decodedStream as $key => $value) {
            $decodedResponse->$key = $value;
        }

        return $decodedResponse;
    }
}
