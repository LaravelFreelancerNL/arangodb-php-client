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
     * @param  mixed  $data
     * @return string
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
     * @psalm-suppress MixedAssignment, MixedArrayOffset
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param  ResponseInterface|null  $response
     * @return stdClass
     */
    protected function decodeResponse(?ResponseInterface $response): stdClass
    {
        $decodedResponse = new stdClass();
        if (! isset($response)) {
            return $decodedResponse;
        }

        $phpStream = StreamWrapper::getResource($response->getBody());
        $decoder = new ExtJsonDecoder(false);
        $decodedStream = Items::fromStream($phpStream, ['decoder' => $decoder]);

        foreach ($decodedStream as $key => $value) {
            $decodedResponse->$key = $value;
        }

        return $decodedResponse;
    }
}
