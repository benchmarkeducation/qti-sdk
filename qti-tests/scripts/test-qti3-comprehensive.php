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
    
    public function runAllTests() {
        echo "=== QTI 3.0 Comprehensive Test Suite ===\n\n";
        
        $this->testManifest();
        $this->testAssessmentTest();
        $this->testAssessmentItem();
        
        $this->printSummary();
    }
    
    private function testManifest() {
        echo "1. Testing QTI 3.0 Manifest (Generic XML)\n";
        echo "------------------------------------------\n";
        
        $manifestFile = __DIR__ . '/../xml-files/imsmanifest-qti3.xml';
        
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
        echo "2. Testing QTI 3.0 Assessment Test\n";
        echo "----------------------------------\n";
        
        $testFile = __DIR__ . '/../xml-files/sample-qti3-test.xml';
        
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
        echo "3. Testing QTI 3.0 Assessment Item\n";
        echo "----------------------------------\n";
        
        $itemFile = __DIR__ . '/../xml-files/sample-qti3-simple.xml';
        
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
            $interactions = $root->getElementsByTagName('qti-choice-interaction');
            echo "✓ Choice interactions: {$interactions->length}\n";
            
            // Test item session
            $this->testItemSession($doc);
            
            $this->testResults['assessmentItem'] = true;
            
        } catch (Exception $e) {
            echo "✗ Assessment item test failed: {$e->getMessage()}\n";
            $this->testResults['assessmentItem'] = false;
        }
        
        echo "\n";
    }
    
    private function testItemSession($doc) {
        echo "\n--- Item Session Test ---\n";
        
        try {
            $itemSession = new AssessmentItemSession($doc->getDocumentComponent());
            $itemSession->beginItemSession();
            echo "✓ Item session created\n";
            
            $responseVars = $itemSession->getResponseVariables();
            echo "✓ Response variables: {$responseVars->count()}\n";
            
            if ($responseVars->count() > 0) {
                $itemSession->beginAttempt();
                
                // Create response
                $responses = new State();
                $responseVar = $responseVars->getArrayCopy()[0];
                $responses[$responseVar->getIdentifier()] = new QtiIdentifier('choice_a');
                
                $itemSession->endAttempt($responses);
                
                echo "✓ Attempt completed\n";
                echo "✓ Completion status: {$itemSession->getCompletionStatus()}\n";
                
                $responseValue = $itemSession->getVariable($responseVar->getIdentifier());
                echo "✓ Response value: {$responseValue->getValue()}\n";
                
                $scoreVar = $itemSession->getVariable('SCORE');
                if ($scoreVar) {
                    echo "✓ Score: {$scoreVar->getValue()}\n";
                }
                
                $itemSession->endItemSession();
                echo "✓ Item session ended\n";
            }
            
        } catch (Exception $e) {
            echo "✗ Item session error: {$e->getMessage()}\n";
        }
    }
    
    private function printSummary() {
        echo "=== Test Summary ===\n";
        
        $passed = 0;
        $total = count($this->testResults);
        
        foreach ($this->testResults as $test => $result) {
            $status = $result ? '✓ PASS' : '✗ FAIL';
            echo "{$status} {$test}\n";
            if ($result) $passed++;
        }
        
        echo "\nResults: {$passed}/{$total} tests passed\n";
        
        if ($passed === $total) {
            echo "🎉 All QTI 3.0 tests passed!\n";
        } else {
            echo "⚠️ Some tests failed. Check output above.\n";
        }
    }
}

// Run the comprehensive test
$test = new QTI3ComprehensiveTest();
$test->runAllTests();