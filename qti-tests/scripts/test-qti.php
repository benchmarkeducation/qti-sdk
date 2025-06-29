<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;

function testQtiFile($xmlFile) {
    echo "\n=== Testing: " . basename($xmlFile) . " ===\n";
    
    try {
        // Load and validate QTI XML
        $doc = new XmlDocument();
        $doc->load($xmlFile, true);
        
        echo "✓ XML is valid QTI\n";
        echo "✓ Title: " . $doc->getDocumentComponent()->getTitle() . "\n";
        echo "✓ Identifier: " . $doc->getDocumentComponent()->getIdentifier() . "\n";
        
        // Test session creation
        $session = new AssessmentItemSession($doc->getDocumentComponent());
        $session->beginItemSession();
        echo "✓ Item session created\n";
        
        // Show response variables
        $responses = $session->getResponseVariables(false);
        echo "✓ Response variables: " . count($responses) . "\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Test all XML files in xml-files directory
$xmlDir = __DIR__ . '/../xml-files';
$xmlFiles = glob($xmlDir . '/*.xml');

if (empty($xmlFiles)) {
    echo "No XML files found in xml-files directory.\n";
    echo "Add your QTI XML files to: qti-tests/xml-files/\n";
    exit(1);
}

$passed = 0;
$total = count($xmlFiles);

foreach ($xmlFiles as $xmlFile) {
    if (testQtiFile($xmlFile)) {
        $passed++;
    }
}

echo "\n=== Summary ===\n";
echo "Passed: $passed/$total\n";