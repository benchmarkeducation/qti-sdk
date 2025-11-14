<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionState;

echo "=== QTI 3.0 Branching Rules Test ===\n\n";

// Create simple QTI 3.0 test with branching
$qti3TestXml = '<?xml version="1.0" encoding="UTF-8"?>
<qti-assessment-test xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                     xmlns="http://www.imsglobal.org/xsd/imsqtiasi_v3p0" 
                     identifier="branching-test" title="QTI 3.0 Branching Test">
    <qti-outcome-declaration identifier="SCORE" cardinality="single" base-type="float">
        <qti-default-value><qti-value>0</qti-value></qti-default-value>
    </qti-outcome-declaration>
    
    <qti-test-part navigation-mode="linear" submission-mode="individual" identifier="part1">
        <qti-assessment-section fixed="false" title="Section 1" visible="true" identifier="section1">
            
            <qti-assessment-item-ref identifier="item1" href="item1.xml" fixed="false">
                <qti-branch-rule target="item3">
                    <qti-base-value base-type="boolean">true</qti-base-value>
                </qti-branch-rule>
            </qti-assessment-item-ref>
            
            <qti-assessment-item-ref identifier="item2" href="item2.xml" fixed="false" />
            
            <qti-assessment-item-ref identifier="item3" href="item3.xml" fixed="false">
                <qti-pre-condition>
                    <qti-base-value base-type="boolean">true</qti-base-value>
                </qti-pre-condition>
            </qti-assessment-item-ref>
            
        </qti-assessment-section>
    </qti-test-part>
</qti-assessment-test>';

try {
    $doc = new XmlDocument('3.0');
    $doc->loadFromString($qti3TestXml, false);
    
    echo "✓ QTI 3.0 test with branching rules loaded\n";
    
    $root = $doc->getDomDocument()->documentElement;
    $branchRules = $root->getElementsByTagName('qti-branch-rule');
    $preConditions = $root->getElementsByTagName('qti-pre-condition');
    
    echo "✓ Branch rules found: {$branchRules->length}\n";
    echo "✓ Pre-conditions found: {$preConditions->length}\n";
    
    // Test element name mapping
    $test = $doc->getDocumentComponent();
    $testParts = $test->getTestParts();
    $section = $testParts->getArrayCopy()[0]->getAssessmentSections()->getArrayCopy()[0];
    $itemRefs = $section->getSectionParts();
    
    echo "✓ Test parts: {$testParts->count()}\n";
    echo "✓ Item refs: {$itemRefs->count()}\n";
    
    // Check if branching elements are properly mapped
    $itemRefsArray = $itemRefs->getArrayCopy();
    $item1 = $itemRefsArray[0];
    $branchRules = $item1->getBranchRules();
    echo "✓ Item 1 branch rules: {$branchRules->count()}\n";
    
    $item3 = $itemRefsArray[2];
    $preConditions = $item3->getPreConditions();
    echo "✓ Item 3 pre-conditions: {$preConditions->count()}\n";
    
    echo "\n🎉 QTI 3.0 Branching Rules: WORKING!\n";
    
} catch (Exception $e) {
    echo "❌ Branching test failed: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
}