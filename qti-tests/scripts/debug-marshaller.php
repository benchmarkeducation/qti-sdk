<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\marshalling\Qti30MarshallerFactory;
use qtism\data\storage\xml\Utils;

$factory = new Qti30MarshallerFactory();

echo "Web Component Friendly: " . ($factory->isWebComponentFriendly() ? 'true' : 'false') . "\n";

// Test qtiFriendlyName conversion
$qti3Name = 'qti-assessment-item';
$qtiFriendlyName = Utils::qtiFriendlyName($qti3Name);
echo "QTI 3.0 name: $qti3Name\n";
echo "QTI friendly name: $qtiFriendlyName\n";

// Check if mapping exists
echo "Has mapping for '$qtiFriendlyName': " . ($factory->hasMappingEntry($qtiFriendlyName) ? 'true' : 'false') . "\n";
echo "Has mapping for '$qti3Name': " . ($factory->hasMappingEntry($qti3Name) ? 'true' : 'false') . "\n";

// Try to get mapping
$mapping = $factory->getMappingEntry($qtiFriendlyName);
echo "Mapping for '$qtiFriendlyName': " . ($mapping ? $mapping : 'false') . "\n";

$mapping = $factory->getMappingEntry($qti3Name);
echo "Mapping for '$qti3Name': " . ($mapping ? $mapping : 'false') . "\n";