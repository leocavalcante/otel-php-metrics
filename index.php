<?php

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;

require 'vendor/autoload.php';

$transport = (new GrpcTransportFactory())->create('http://localhost:4317' . OtlpUtil::method(Signals::METRICS));
$exporter = new MetricExporter($transport, Temporality::CUMULATIVE);
$reader = new ExportingReader($exporter, ClockFactory::getDefault());

$meter = MeterProvider::builder()
       ->addReader($reader)
       ->build()
       ->getMeter('example');

$counter = $meter->createCounter('http_requests');

$counter->add(1);

$reader->collect();
