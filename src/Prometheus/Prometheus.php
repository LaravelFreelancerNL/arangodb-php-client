<?php

declare(strict_types=1);

namespace ArangoClient\Prometheus;

use GuzzleHttp\Psr7\StreamWrapper;
use Psr\Http\Message\ResponseInterface;

class Prometheus
{
    protected Metrics $output;

    public function __construct()
    {
        $this->output = new Metrics();
    }


    public function parseStream(ResponseInterface $from): Metrics
    {
        $stream = $from->getBody();
        $resource = StreamWrapper::getResource($stream);

        while ($line = stream_get_line($resource, 1000000, "\n")) {
            $this->handleLine($line);
        }
        return $this->output;
    }

    public function parseText(string $content): Metrics
    {
        $fileObject = new \SplFileObject('php://memory', 'r+');
        $fileObject->fwrite($content);
        $fileObject->rewind();

        while ($fileObject->valid()) {
            $line = $fileObject->current();
            if (is_string($line)) {
                $this->handleLine($line);
            }
            $fileObject->next();
        }

        return $this->output;
    }

    protected function handleLine(string $line): void
    {
        match (true) {
            str_starts_with($line, "# TYPE ") => $this->setType($line),
            str_starts_with($line, "# HELP ") => $this->setHelpText($line),
            default =>  $this->setMetric($line),
        };
    }

    protected function createMetricIfNew(string $name): void
    {
        if (!property_exists($this->output, $name)) {
            $this->output->$name = new Metric($name);
        }
    }

    protected function setType(string $line): void
    {
        $matches = [];
        if (
            preg_match(
                "/^# TYPE (?'metricName'[a-zA-Z_:][a-zA-Z0-9_:]*) (?'type'counter|gauge|histogram|summary)$/",
                $line,
                $matches,
            )
        ) {
            $metricName = $matches['metricName'];
            $this->createMetricIfNew($metricName);

            $this->output->$metricName->type = $matches['type'];
        }
    }

    protected function setHelpText(string $line): void
    {
        $matches = [];
        if (
            preg_match(
                "/^# HELP (?'metricName'[a-zA-Z_:][a-zA-Z0-9_:]*) (?'helpText'[\W\w]*)/",
                $line,
                $matches,
            )
        ) {
            $metricName = $matches['metricName'];

            $this->createMetricIfNew($metricName);

            $this->output->$metricName->help = $matches['helpText'];
        }
    }

    protected function setMetric(string $line): void
    {
        $matches = [];
        if (
            preg_match(
                "/^(?'rawMetricName'[a-zA-Z_:][a-zA-Z0-9_:]*)(?'rawLabels'{[\W\w]*})? (?'value'[-+.,eE\d]+) ?(?'timestamp'[0-9]+)?$/",
                $line,
                $matches,
            )
        ) {
            // The first group contains the metricName and the suffix
            [$metricName, $suffix] = $this->extractMetricNameAndSuffix($matches['rawMetricName']);

            $this->createMetricIfNew($metricName);

            // Feed the remaining matches to the suffix dependent parser
            match ($suffix) {
                "bucket" => $this->setBucket($metricName, $matches),
                default => $this->setMetricValues($metricName, $matches, $suffix),
            };
        }
    }

    /**
     * @return string[]
     */
    protected function extractMetricNameAndSuffix(string $value): array
    {
        $nameParticles = explode("_", $value);
        $suffix = array_pop($nameParticles);

        if (in_array($suffix, ["bucket", "count", "sum"])) {
            $baseName = implode("_", $nameParticles);
            return [$baseName, $suffix];
        }

        return [$value, 'value'];
    }

    /**
     * @param array<string|int|float> $matches
     */
    protected function setBucket(mixed $metricName, array $matches): void
    {
        $labels = null;
        $timestamp = null;

        $value = is_numeric($matches['value']) ? +$matches['value'] : $matches['value'];

        if (array_key_exists("rawLabels", $matches)) {
            $labels = $this->extractLabels((string) $matches['rawLabels']);
        }

        if (array_key_exists("timestamp", $matches)) {
            $timestamp = is_numeric($matches['timestamp']) ? +$matches['timestamp'] : $matches['timestamp'];
        }

        $bucket = new Bucket(
            $value,
            $labels,
            $timestamp,
        );

        $this->output->$metricName->buckets[] = $bucket;
    }

    /**
     * @param array<string, mixed> $matches
     */
    protected function setMetricValues(string $metricName, array $matches, string $suffix): void
    {
        $this->output->$metricName->$suffix = is_numeric($matches['value']) ? +$matches['value'] : $matches['value'];

        $this->setLabels($metricName, $matches);

        $this->setTimestamp($metricName, $matches);
    }

    /**
     * @param array<string, mixed> $matches
     */
    protected function setLabels(string $metricName, array $matches): void
    {
        if (array_key_exists("rawLabels", $matches)) {
            $this->output->$metricName->labels  = $this->extractLabels($matches['rawLabels']);
        }
    }

    /**
     * @param array<string, mixed> $matches
     */
    protected function setTimestamp(string $metricName, array $matches): void
    {
        if (array_key_exists("timestamp", $matches)) {
            $this->output->$metricName->timestamp = is_numeric($matches['timestamp']) ? +$matches['timestamp'] : $matches['timestamp'];
        }
    }


    /**
     * @return string[]
     */
    protected function extractLabels(string $rawLabels): array
    {
        $labels = [];
        $matches = [];
        if (
            preg_match_all(
                "/(?'label'[a-zA-Z0-9]*)=\"(?'value'[^\"]*)\"/",
                $rawLabels,
                $matches,
            )
        ) {
            foreach ($matches['label'] as $key => $label) {
                $value = $matches['value'][$key];

                $labels[$label] = is_numeric($value) ? +$value : $value;
            }
        }

        return $labels;
    }
}
