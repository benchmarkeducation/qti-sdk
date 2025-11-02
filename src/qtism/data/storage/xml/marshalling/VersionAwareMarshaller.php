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
        'minChoices' => 'min-choices',
        'outcomeIdentifier' => 'outcome-identifier',
        'showHide' => 'show-hide',
        'variableIdentifier' => 'variable-identifier',
        'weightIdentifier' => 'weight-identifier',
        'sectionIdentifier' => 'section-identifier',
        'includeCategories' => 'include-categories',
        'excludeCategories' => 'exclude-categories',
        'navigationMode' => 'navigation-mode',
        'submissionMode' => 'submission-mode',
        'longInterpretation' => 'long-interpretation',
        'normalMaximum' => 'normal-maximum',
        'normalMinimum' => 'normal-minimum',
        'masteryValue' => 'mastery-value',
        'externalScored' => 'external-scored',
        'fieldIdentifier' => 'field-identifier',
        'sourceValue' => 'source-value',
        'targetValue' => 'target-value',
        'lowerBound' => 'lower-bound',
        'upperBound' => 'upper-bound',
        'expectedLength' => 'expected-length',
        'patternMask' => 'pattern-mask',
        'placeholderText' => 'placeholder-text',
        'maxStrings' => 'max-strings',
        'minStrings' => 'min-strings'
    ];

    private static array $elementMap = [
        // Core elements
        'assessmentItem' => 'qti-assessment-item',
        'assessmentTest' => 'qti-assessment-test',
        'testPart' => 'qti-test-part',
        'assessmentSection' => 'qti-assessment-section',
        'assessmentItemRef' => 'qti-assessment-item-ref',
        'responseDeclaration' => 'qti-response-declaration',
        'outcomeDeclaration' => 'qti-outcome-declaration',
        'templateDeclaration' => 'qti-template-declaration',
        'itemBody' => 'qti-item-body',
        'responseProcessing' => 'qti-response-processing',
        'templateProcessing' => 'qti-template-processing',
        'outcomeProcessing' => 'qti-outcome-processing',
        'correctResponse' => 'qti-correct-response',
        'defaultValue' => 'qti-default-value',
        'value' => 'qti-value',
        'mapping' => 'qti-mapping',
        'mapEntry' => 'qti-map-entry',
        'areaMapping' => 'qti-area-mapping',
        'areaMapEntry' => 'qti-area-map-entry',
        'stylesheet' => 'qti-stylesheet',
        'modalFeedback' => 'qti-modal-feedback',
        
        // Content elements
        'rubricBlock' => 'qti-rubric-block',
        'contentBody' => 'qti-content-body',
        'feedbackBlock' => 'qti-feedback-block',
        'feedbackInline' => 'qti-feedback-inline',
        'templateBlock' => 'qti-template-block',
        'templateInline' => 'qti-template-inline',
        'printedVariable' => 'qti-printed-variable',
        'prompt' => 'qti-prompt',
        'infoControl' => 'qti-info-control',
        
        // Interactions
        'choiceInteraction' => 'qti-choice-interaction',
        'orderInteraction' => 'qti-order-interaction',
        'associateInteraction' => 'qti-associate-interaction',
        'matchInteraction' => 'qti-match-interaction',
        'gapMatchInteraction' => 'qti-gap-match-interaction',
        'inlineChoiceInteraction' => 'qti-inline-choice-interaction',
        'textEntryInteraction' => 'qti-text-entry-interaction',
        'extendedTextInteraction' => 'qti-extended-text-interaction',
        'hottextInteraction' => 'qti-hottext-interaction',
        'hotspotInteraction' => 'qti-hotspot-interaction',
        'selectPointInteraction' => 'qti-select-point-interaction',
        'graphicOrderInteraction' => 'qti-graphic-order-interaction',
        'graphicAssociateInteraction' => 'qti-graphic-associate-interaction',
        'graphicGapMatchInteraction' => 'qti-graphic-gap-match-interaction',
        'positionObjectInteraction' => 'qti-position-object-interaction',
        'sliderInteraction' => 'qti-slider-interaction',
        'drawingInteraction' => 'qti-drawing-interaction',
        'uploadInteraction' => 'qti-upload-interaction',
        'mediaInteraction' => 'qti-media-interaction',
        'customInteraction' => 'qti-custom-interaction',
        'endAttemptInteraction' => 'qti-end-attempt-interaction',
        
        // Choices
        'simpleChoice' => 'qti-simple-choice',
        'simpleAssociableChoice' => 'qti-simple-associable-choice',
        'inlineChoice' => 'qti-inline-choice',
        'hottext' => 'qti-hottext',
        'hotspotChoice' => 'qti-hotspot-choice',
        'associableHotspot' => 'qti-associable-hotspot',
        'gap' => 'qti-gap',
        'gapText' => 'qti-gap-text',
        'gapImg' => 'qti-gap-img',
        
        // Response processing
        'responseCondition' => 'qti-response-condition',
        'responseIf' => 'qti-response-if',
        'responseElseIf' => 'qti-response-else-if',
        'responseElse' => 'qti-response-else',
        'setOutcomeValue' => 'qti-set-outcome-value',
        'setCorrectResponse' => 'qti-set-correct-response',
        'setDefaultValue' => 'qti-set-default-value',
        'setTemplateValue' => 'qti-set-template-value',
        'exitResponse' => 'qti-exit-response',
        'lookupOutcomeValue' => 'qti-lookup-outcome-value',
        
        // Outcome processing
        'outcomeCondition' => 'qti-outcome-condition',
        'outcomeIf' => 'qti-outcome-if',
        'outcomeElseIf' => 'qti-outcome-else-if',
        'outcomeElse' => 'qti-outcome-else',
        'exitTest' => 'qti-exit-test',
        
        // Expressions
        'baseValue' => 'qti-base-value',
        'variable' => 'qti-variable',
        'default' => 'qti-default',
        'correct' => 'qti-correct',
        'mapResponse' => 'qti-map-response',
        'mapResponsePoint' => 'qti-map-response-point',
        'null' => 'qti-null',
        'randomInteger' => 'qti-random-integer',
        'randomFloat' => 'qti-random-float',
        'mathConstant' => 'qti-math-constant',
        'testVariables' => 'qti-test-variables',
        'outcomeMaximum' => 'qti-outcome-maximum',
        'outcomeMinimum' => 'qti-outcome-minimum',
        'numberCorrect' => 'qti-number-correct',
        'numberIncorrect' => 'qti-number-incorrect',
        'numberResponded' => 'qti-number-responded',
        'numberPresented' => 'qti-number-presented',
        'numberSelected' => 'qti-number-selected',
        
        // Operators
        'and' => 'qti-and',
        'or' => 'qti-or',
        'not' => 'qti-not',
        'match' => 'qti-match',
        'stringMatch' => 'qti-string-match',
        'patternMatch' => 'qti-pattern-match',
        'equal' => 'qti-equal',
        'equalRounded' => 'qti-equal-rounded',
        'inside' => 'qti-inside',
        'lt' => 'qti-lt',
        'lte' => 'qti-lte',
        'gt' => 'qti-gt',
        'gte' => 'qti-gte',
        'durationLT' => 'qti-duration-lt',
        'durationGTE' => 'qti-duration-gte',
        'sum' => 'qti-sum',
        'product' => 'qti-product',
        'subtract' => 'qti-subtract',
        'divide' => 'qti-divide',
        'power' => 'qti-power',
        'integerDivide' => 'qti-integer-divide',
        'integerModulus' => 'qti-integer-modulus',
        'truncate' => 'qti-truncate',
        'round' => 'qti-round',
        'integerToFloat' => 'qti-integer-to-float',
        'max' => 'qti-max',
        'min' => 'qti-min',
        'gcd' => 'qti-gcd',
        'lcm' => 'qti-lcm',
        'multiple' => 'qti-multiple',
        'ordered' => 'qti-ordered',
        'containerSize' => 'qti-container-size',
        'isNull' => 'qti-is-null',
        'index' => 'qti-index',
        'fieldValue' => 'qti-field-value',
        'random' => 'qti-random',
        'member' => 'qti-member',
        'delete' => 'qti-delete',
        'contains' => 'qti-contains',
        'substring' => 'qti-substring',
        'anyN' => 'qti-any-n',
        'mathOperator' => 'qti-math-operator',
        'statsOperator' => 'qti-stats-operator',
        'roundTo' => 'qti-round-to',
        'customOperator' => 'qti-custom-operator',
        'repeat' => 'qti-repeat',
        
        // Branching and flow control
        'branchRule' => 'qti-branch-rule',
        'preCondition' => 'qti-pre-condition'
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