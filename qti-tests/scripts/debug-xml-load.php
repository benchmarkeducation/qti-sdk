<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\XmlDocument;

try {
    $doc = new XmlDocument();
    
    // Load the QTI 3.0 file
    $xmlContent = file_get_contents(__DIR__ . '/../xml-files/sample-qti3.xml');
    echo "XML Content:\n" . substr($xmlContent, 0, 200) . "...\n\n";
    
    // Try to load without validation
    $doc->loadFromString($xmlContent, false);
    
    echo "✓ Document loaded successfully!\n";
    echo "Version: " . $doc->getVersion() . "\n";
    echo "Root element: " . $doc->getDomDocument()->documentElement->tagName . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Error class: " . get_class($e) . "\n";
    
    // Get more details about the error
    if (method_exists($e, 'getTrace')) {
        $trace = $e->getTrace();
        if (!empty($trace)) {
            echo "Error location: " . $trace[0]['file'] . ":" . $trace[0]['line'] . "\n";
            echo "Error function: " . $trace[0]['function'] . "\n";
        }
    }
}