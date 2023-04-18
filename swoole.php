<?php

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

require 'vendor/autoload.php';

$transport = (new GrpcTransportFactory())->create('http://localhost:4317' . OtlpUtil::method(Signals::METRICS));
$exporter = new MetricExporter($transport);
$reader = new ExportingReader($exporter, ClockFactory::getDefault());

$meter = MeterProvider::builder()
       ->addReader($reader)
       ->build()
       ->getMeter('example');

$counter = $meter->createCounter('http_requests');

$server = new Server('0.0.0.0', 9501);

$server->on('request', static function (Request $request, Response $response) use ($counter, $reader): void {
    $response->end();
    $counter->add(1);
    $reader->collect();
});

$server->start();
