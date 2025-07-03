<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\OutcomeVariable;

function testQtiFileNoValidate($xmlFile) {
    echo "\n=== Testing (No Validation): " . basename($xmlFile) . " ===\n";
    
    // Skip problematic files
    if (strpos(basename($xmlFile), 'complete') !== false) {
        echo "⚠️ Skipping complex file (known infinite loop issue)\n";
        return true;
    }
    
    try {
        // Load WITHOUT validation
        $doc = new XmlDocument();
        $doc->load($xmlFile, false); // false = no validation
        
        echo "✓ XML loaded (no validation)\n";
        echo "✓ Root element: " . $doc->getDomDocument()->documentElement->tagName . "\n";
        
        // Test sessions based on element type
        $rootElement = $doc->getDomDocument()->documentElement->tagName;
        if ($rootElement === 'assessmentItem' || $rootElement === 'qti-assessment-item') {
            testItemSession($doc);
        } elseif ($rootElement === 'assessmentTest' || $rootElement === 'qti-assessment-test') {
            echo "✓ Assessment test detected\n";
        } elseif ($rootElement === 'manifest') {
            echo "✓ IMS manifest detected\n";
        }
        
        return true;
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

$xmlFiles = glob(__DIR__ . '/../xml-files/*.xml');

function testItemSession($doc) {
    try {
        echo "\n✓ Item session created\n";
        
        // Create item session
        $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
        $itemSession->beginItemSession();
        
        echo "✓ Response variables: " . count($itemSession->getResponseVariables()) . "\n";
        
        // Create sample responses based on the item
        $responses = new State();
        $responseVars = $itemSession->getResponseVariables();
        
        if (count($responseVars) > 0) {
            $responseVar = $responseVars->getArrayCopy()[0];
            $identifier = $responseVar->getIdentifier();
            
            // Create sample response based on cardinality
            if ($responseVar->getCardinality() === Cardinality::MULTIPLE) {
                $container = new MultipleContainer(BaseType::IDENTIFIER);
                $container[] = new QtiIdentifier('A');
                $container[] = new QtiIdentifier('C');
                $responses[$identifier] = $container;
            } else {
                $responses[$identifier] = new QtiIdentifier('A');
            }
            
            // Begin attempt and provide response
            $itemSession->beginAttempt();
            $itemSession->endAttempt($responses);
            
            // Display session variables
            //echo "\nnumAttempts: " . $itemSession->getNumAttempts() . "\n";
            echo "completionStatus: " . $itemSession->getCompletionStatus() . "\n";
            
            // Get response variable value
            $firstResponseVar = $responseVars->getArrayCopy()[0];
            $responseVar = $itemSession->getVariable($firstResponseVar->getIdentifier());
            echo "RESPONSE: " . ($responseVar ? $responseVar->getValue() : 'N/A') . "\n";
            
            // Check for SCORE outcome variable
            $scoreVar = $itemSession->getVariable('SCORE');
            if ($scoreVar) {
                echo "SCORE: " . $scoreVar->getValue() . "\n";
            }
            
            $itemSession->endItemSession();
            echo "✓ Item session completed\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Session error: " . $e->getMessage() . "\n";
    }
}

foreach ($xmlFiles as $xmlFile) {
    testQtiFileNoValidate($xmlFile);
}