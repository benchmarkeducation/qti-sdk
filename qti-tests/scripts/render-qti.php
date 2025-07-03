<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\goldilocks\GoldilocksRenderingEngine;

function renderQtiFile($xmlFile) {
    $basename = pathinfo($xmlFile, PATHINFO_FILENAME);
    $outputFile = __DIR__ . '/../output/' . $basename . '.html';
    
    try {
        $doc = new XmlDocument();
        $doc->load($xmlFile, true);
        
        $renderer = new GoldilocksRenderingEngine();
        $xml = $renderer->render($doc->getDocumentComponent());
        
        $html = "<!DOCTYPE html>\n<html>\n<head>\n";
        $html .= "<meta charset=\"utf-8\">\n";
        $html .= "<title>" . htmlspecialchars($doc->getDocumentComponent()->getTitle()) . "</title>\n";
        $html .= "</head>\n<body>\n";
        $html .= $xml->saveXML($xml->documentElement);
        $html .= "\n</body>\n</html>";
        
        file_put_contents($outputFile, $html);
        echo "✓ Rendered: " . basename($xmlFile) . " → " . basename($outputFile) . "\n";
        
    } catch (Exception $e) {
        echo "✗ Failed to render " . basename($xmlFile) . ": " . $e->getMessage() . "\n";
    }
}

$xmlDir = __DIR__ . '/../xml-files';
$xmlFiles = glob($xmlDir . '/*.xml');

if (empty($xmlFiles)) {
    echo "No XML files found in xml-files directory.\n";
    exit(1);
}

foreach ($xmlFiles as $xmlFile) {
    renderQtiFile($xmlFile);
}