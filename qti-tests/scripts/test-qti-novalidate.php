<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\XmlDocument;

function testQtiFileNoValidate($xmlFile) {
    echo "\n=== Testing (No Validation): " . basename($xmlFile) . " ===\n";
    
    try {
        // Load WITHOUT validation
        $doc = new XmlDocument();
        $doc->load($xmlFile, false); // false = no validation
        
        echo "✓ XML loaded (no validation)\n";
        echo "✓ Root element: " . $doc->getDomDocument()->documentElement->tagName . "\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

$xmlFiles = glob(__DIR__ . '/../xml-files/*.xml');

foreach ($xmlFiles as $xmlFile) {
    testQtiFileNoValidate($xmlFile);
}