<?php

namespace qtism\data\storage\xml\marshalling;

use DOMElement;

trait VersionAwareMarshaller
{
    private static array $attributeMap = [
        'timeDependent' => 'time-dependent',
        'toolName' => 'tool-name', 
        'toolVersion' => 'tool-version',
        'baseType' => 'base-type',
        'defaultValue' => 'default-value',
        'mapKey' => 'map-key',
        'mappedValue' => 'mapped-value',
        'responseIdentifier' => 'response-identifier',
        'maxChoices' => 'max-choices',
        'outcomeIdentifier' => 'outcome-identifier',
        'showHide' => 'show-hide'
    ];

    private static array $elementMap = [
        'assessmentItem' => 'qti-assessment-item',
        'assessmentTest' => 'qti-assessment-test',
        'testPart' => 'qti-test-part',
        'assessmentSection' => 'qti-assessment-section',
        'assessmentItemRef' => 'qti-assessment-item-ref',
        'responseDeclaration' => 'qti-response-declaration',
        'outcomeDeclaration' => 'qti-outcome-declaration',
        'itemBody' => 'qti-item-body',
        'responseProcessing' => 'qti-response-processing',
        'outcomeProcessing' => 'qti-outcome-processing',
        'correctResponse' => 'qti-correct-response',
        'defaultValue' => 'qti-default-value',
        'value' => 'qti-value',
        'mapping' => 'qti-mapping',
        'mapEntry' => 'qti-map-entry',
        'stylesheet' => 'qti-stylesheet',
        'rubricBlock' => 'qti-rubric-block',
        'contentBody' => 'qti-content-body',
        'hottextInteraction' => 'qti-hottext-interaction',
        'hottext' => 'qti-hottext',
        'setOutcomeValue' => 'qti-set-outcome-value',
        'baseValue' => 'qti-base-value',
        'responseCondition' => 'qti-response-condition',
        'responseIf' => 'qti-response-if',
        'outcomeCondition' => 'qti-outcome-condition',
        'outcomeIf' => 'qti-outcome-if',
        'not' => 'qti-not',
        'isNull' => 'qti-is-null',
        'variable' => 'qti-variable',
        'sum' => 'qti-sum',
        'mapResponse' => 'qti-map-response',
        'modalFeedback' => 'qti-modal-feedback',
        'testVariables' => 'qti-test-variables',
        'gte' => 'qti-gte'
    ];

    protected function getAttributeAs(DOMElement $element, string $name, ?string $type = null)
    {
        // Try QTI 2.x attribute name first
        if ($element->hasAttribute($name)) {
            $value = $element->getAttribute($name);
            return $type === 'boolean' ? ($value === 'true') : $value;
        }
        
        // Try QTI 3.0 attribute name if mapping exists
        if (isset(self::$attributeMap[$name]) && $element->hasAttribute(self::$attributeMap[$name])) {
            $value = $element->getAttribute(self::$attributeMap[$name]);
            return $type === 'boolean' ? ($value === 'true') : $value;
        }
        
        return null;
    }

    protected function getVersionedElementName(string $name): string
    {
        return $this->getVersion() === '3.0.0' && isset(self::$elementMap[$name]) 
            ? self::$elementMap[$name] 
            : $name;
    }

    protected function getVersionedAttributeName(string $name): string
    {
        return $this->getVersion() === '3.0.0' && isset(self::$attributeMap[$name])
            ? self::$attributeMap[$name]
            : $name;
    }
}