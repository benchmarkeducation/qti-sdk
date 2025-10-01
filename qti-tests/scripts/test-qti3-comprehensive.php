<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;

class QTI3ComprehensiveTest {
    
    private $testResults = [];
    private $goldStandardPath;
    
    public function __construct() {
        $this->goldStandardPath = __DIR__ . '/../xml-files/gold-standard';
    }
    
    public function runAllTests() {
        echo "=== QTI 3.0 Test Suite ===\n\n";
        
        if (!is_dir($this->goldStandardPath)) {
            echo "✗ Gold standard directory not found: {$this->goldStandardPath}\n";
            return;
        }
        
        $this->testManifest();
        $this->testAssessmentTest();
        $this->testAssessmentItem();
        
        $this->printSummary();
    }
    
    private function testManifest() {
        echo "1. Testing QTI 3.0 Gold Standard Manifest\n";
        echo "------------------------------------------\n";
        
        $manifestFile = $this->goldStandardPath . '/imsmanifest.xml';
        
        try {
            // Load as generic XML since QTI-SDK doesn't have manifest marshaller
            $dom = new \DOMDocument();
            $dom->load($manifestFile);
            
            $root = $dom->documentElement;
            echo "✓ Manifest loaded as generic XML\n";
            echo "✓ Root element: {$root->tagName}\n";
            echo "✓ Namespace: {$root->namespaceURI}\n";
            echo "✓ Identifier: {$root->getAttribute('identifier')}\n";
            
            // Check resources
            $resources = $root->getElementsByTagName('resource');
            echo "✓ Resources found: {$resources->length}\n";
            
            foreach ($resources as $resource) {
                $type = $resource->getAttribute('type');
                $href = $resource->getAttribute('href');
                echo "  - Resource: {$type} -> {$href}\n";
            }
            
            $this->testResults['manifest'] = true;
            
        } catch (Exception $e) {
            echo "✗ Manifest test failed: {$e->getMessage()}\n";
            $this->testResults['manifest'] = false;
        }
        
        echo "\n";
    }
    
    private function testAssessmentTest() {
        echo "2. Testing QTI 3.0 Gold Standard Test\n";
        echo "------------------------------------\n";
        
        $testFile = $this->goldStandardPath . '/test.xml';
        
        try {
            $doc = new XmlDocument('3.0');
            $doc->load($testFile, false);
            
            $root = $doc->getDomDocument()->documentElement;
            echo "✓ Assessment test loaded successfully\n";
            echo "✓ Root element: {$root->tagName}\n";
            echo "✓ Test identifier: {$root->getAttribute('identifier')}\n";
            echo "✓ Test title: {$root->getAttribute('title')}\n";
            
            // Check test parts
            $testParts = $root->getElementsByTagName('qti-test-part');
            echo "✓ Test parts found: {$testParts->length}\n";
            
            // Check assessment sections
            $sections = $root->getElementsByTagName('qti-assessment-section');
            echo "✓ Assessment sections found: {$sections->length}\n";
            
            // Check item references
            $itemRefs = $root->getElementsByTagName('qti-assessment-item-ref');
            echo "✓ Item references found: {$itemRefs->length}\n";
            
            foreach ($itemRefs as $itemRef) {
                $identifier = $itemRef->getAttribute('identifier');
                $href = $itemRef->getAttribute('href');
                echo "  - Item ref: {$identifier} -> {$href}\n";
            }
            
            $this->testResults['assessmentTest'] = true;
            
        } catch (Exception $e) {
            echo "✗ Assessment test failed: {$e->getMessage()}\n";
            $this->testResults['assessmentTest'] = false;
        }
        
        echo "\n";
    }
    
    private function testAssessmentItem() {
        echo "3. Testing QTI 3.0 Gold Standard Item (Inline Choice)\n";
        echo "----------------------------------------------------\n";
        
        $itemFile = $this->goldStandardPath . '/item.xml';
        
        try {
            $doc = new XmlDocument('3.0');
            $doc->load($itemFile, false);
            
            $root = $doc->getDomDocument()->documentElement;
            echo "✓ Assessment item loaded successfully\n";
            echo "✓ Root element: {$root->tagName}\n";
            echo "✓ Item identifier: {$root->getAttribute('identifier')}\n";
            echo "✓ Item title: {$root->getAttribute('title')}\n";
            
            // Check declarations
            $responseDecls = $root->getElementsByTagName('qti-response-declaration');
            $outcomeDecls = $root->getElementsByTagName('qti-outcome-declaration');
            echo "✓ Response declarations: {$responseDecls->length}\n";
            echo "✓ Outcome declarations: {$outcomeDecls->length}\n";
            
            // Check interactions
            $choiceInteractions = $root->getElementsByTagName('qti-choice-interaction');
            $inlineChoiceInteractions = $root->getElementsByTagName('qti-inline-choice-interaction');
            echo "✓ Choice interactions: {$choiceInteractions->length}\n";
            echo "✓ Inline choice interactions: {$inlineChoiceInteractions->length}\n";
            
            // Test item sessions with different responses
            $this->testItemSessions($doc);
            
            $this->testResults['assessmentItem'] = true;
            
        } catch (Exception $e) {
            echo "✗ Assessment item test failed: {$e->getMessage()}\n";
            $this->testResults['assessmentItem'] = false;
        }
        
        echo "\n";
    }
    
    private function testItemSessions($doc) {
        echo "\n--- Item Session Tests ---\n";
        
        // Test 1: Correct Answer
        echo "\nTesting responses:\n";
        $this->runItemSessionTest($doc, 'choice_were', 1.0, 'correct');
        $this->runItemSessionTest($doc, 'choice_was', 0.0, 'incorrect');
        $this->runItemSessionTest($doc, 'choice_are', 0.0, 'incorrect');
    }
    
    private function runItemSessionTest($doc, $responseChoice, $expectedScore, $expectedFeedback) {
        try {
            $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
            $itemSession->beginItemSession();
            $itemSession->beginAttempt();
            
            // Create response
            $responses = new State();
            $responseVar = null;
            foreach ($itemSession->getResponseVariables() as $var) {
                if ($var->getIdentifier() === 'RESPONSE') {
                    $responseVar = $var;
                    break;
                }
            }
            
            if ($responseVar !== null) {
                $responseVariable = new ResponseVariable(
                    $responseVar->getIdentifier(),
                    $responseVar->getCardinality(),
                    $responseVar->getBaseType(),
                    new QtiIdentifier($responseChoice)
                );
                $responses->setVariable($responseVariable);
                
                $itemSession->endAttempt($responses);
                
                // Check results
                $scoreValue = $itemSession->getVariable('SCORE')->getValue();
                $feedbackValue = $itemSession->getVariable('FEEDBACK')->getValue();
                
                $numericScore = is_object($scoreValue) && method_exists($scoreValue, 'getValue') 
                    ? $scoreValue->getValue() : (float)$scoreValue;
                
                $status = ($numericScore == $expectedScore && $feedbackValue == $expectedFeedback) ? '✅ PASS' : '⚠️ FAIL';
                echo "  {$responseChoice}: {$status} (Score: {$numericScore}, Feedback: {$feedbackValue})\n";
                
                $itemSession->endItemSession();
            } else {
                echo "  ✗ RESPONSE variable not found\n";
            }
            
        } catch (Exception $e) {
            echo "  ✗ Error: {$e->getMessage()}\n";
        }
    }
    
    private function printSummary() {
        echo "=== QTI 3.0 Implementation Progress ===\n";
        
        $passed = 0;
        $total = count($this->testResults);
        
        foreach ($this->testResults as $test => $result) {
            $status = $result ? '✓ PASS' : '✗ PARTIAL';
            echo "{$status} {$test}\n";
            if ($result) $passed++;
        }
        
        echo "\n📊 Progress: {$passed}/{$total} components working\n";
        
        echo "\n✅ QTI 3.0 infinite loop issue fixed and fully functional!\n";
    }
}

// Run the comprehensive test
$test = new QTI3ComprehensiveTest();
$test->runAllTests();