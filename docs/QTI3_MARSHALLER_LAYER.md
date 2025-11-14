# QTI 3.0 Marshaller Layer Documentation

## Overview

The **Marshaller Layer** handles name conversion between QTI 2.x and QTI 3.0 within individual marshaller classes. This is the second layer in the two-layer QTI 3.0 architecture.

## File: VersionAwareMarshaller.php

### Purpose
Provides centralized name conversion methods that handle the differences between QTI 2.x and QTI 3.0 naming conventions without requiring changes to existing marshaller logic.

### Key Responsibilities
1. **Attribute Name Conversion**: `baseType` ↔ `base-type`
2. **Element Name Conversion**: `assessmentItem` ↔ `qti-assessment-item`
3. **Version-Aware Reading**: Automatically tries both naming conventions
4. **Version-Aware Writing**: Uses correct naming convention based on version

## Implementation Structure

### Trait Definition
```php
trait VersionAwareMarshaller
{
    private static array $attributeMap = [ /* 35+ mappings */ ];
    private static array $elementMap = [ /* 120+ mappings */ ];
    
    protected function getAttributeAs(DOMElement $element, string $name, ?string $type = null);
    protected function getVersionedElementName(string $name): string;
    protected function getVersionedAttributeName(string $name): string;
}
```

### Integration with Base Marshaller
```php
// In Marshaller.php
abstract class Marshaller
{
    use VersionAwareMarshaller; // All marshallers inherit trait automatically
    
    // Existing marshaller methods now have access to version-aware functionality
}
```

## Attribute Mappings (35+ mappings)

### Core Attributes
| QTI 2.x Attribute | QTI 3.0 Attribute | Usage |
|------------------|-------------------|-------|
| `timeDependent` | `time-dependent` | Assessment item timing |
| `toolName` | `tool-name` | Authoring tool name |
| `toolVersion` | `tool-version` | Authoring tool version |
| `baseType` | `base-type` | Variable base type |
| `defaultValue` | `default-value` | Default value reference |

### Interaction Attributes
| QTI 2.x Attribute | QTI 3.0 Attribute | Usage |
|------------------|-------------------|-------|
| `responseIdentifier` | `response-identifier` | Response variable ID |
| `maxChoices` | `max-choices` | Maximum selections |
| `minChoices` | `min-choices` | Minimum selections |
| `showHide` | `show-hide` | Feedback visibility |
| `expectedLength` | `expected-length` | Text input length |
| `patternMask` | `pattern-mask` | Input pattern |
| `placeholderText` | `placeholder-text` | Input placeholder |

### Processing Attributes
| QTI 2.x Attribute | QTI 3.0 Attribute | Usage |
|------------------|-------------------|-------|
| `outcomeIdentifier` | `outcome-identifier` | Outcome variable ID |
| `variableIdentifier` | `variable-identifier` | Variable reference |
| `weightIdentifier` | `weight-identifier` | Weight reference |
| `sectionIdentifier` | `section-identifier` | Section reference |
| `includeCategories` | `include-categories` | Category inclusion |
| `excludeCategories` | `exclude-categories` | Category exclusion |

### Test Structure Attributes
| QTI 2.x Attribute | QTI 3.0 Attribute | Usage |
|------------------|-------------------|-------|
| `navigationMode` | `navigation-mode` | Test navigation |
| `submissionMode` | `submission-mode` | Test submission |
| `longInterpretation` | `long-interpretation` | Score interpretation |
| `normalMaximum` | `normal-maximum` | Normal maximum score |
| `normalMinimum` | `normal-minimum` | Normal minimum score |
| `masteryValue` | `mastery-value` | Mastery threshold |
| `externalScored` | `external-scored` | External scoring flag |

### Mapping Attributes
| QTI 2.x Attribute | QTI 3.0 Attribute | Usage |
|------------------|-------------------|-------|
| `mapKey` | `map-key` | Mapping key |
| `mappedValue` | `mapped-value` | Mapped value |
| `fieldIdentifier` | `field-identifier` | Field identifier |
| `sourceValue` | `source-value` | Source value |
| `targetValue` | `target-value` | Target value |
| `lowerBound` | `lower-bound` | Lower boundary |
| `upperBound` | `upper-bound` | Upper boundary |
| `maxStrings` | `max-strings` | Maximum strings |
| `minStrings` | `min-strings` | Minimum strings |

## Element Mappings (120+ mappings)

### Core Assessment Elements
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `assessmentItem` | `qti-assessment-item` | Assessment item root |
| `assessmentTest` | `qti-assessment-test` | Assessment test root |
| `testPart` | `qti-test-part` | Test part structure |
| `assessmentSection` | `qti-assessment-section` | Test section |
| `assessmentItemRef` | `qti-assessment-item-ref` | Item reference |

### Variable Declarations
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `responseDeclaration` | `qti-response-declaration` | Response variable |
| `outcomeDeclaration` | `qti-outcome-declaration` | Outcome variable |
| `templateDeclaration` | `qti-template-declaration` | Template variable |
| `correctResponse` | `qti-correct-response` | Correct response |
| `defaultValue` | `qti-default-value` | Default value |
| `value` | `qti-value` | Value container |
| `mapping` | `qti-mapping` | Value mapping |
| `mapEntry` | `qti-map-entry` | Map entry |
| `areaMapping` | `qti-area-mapping` | Area mapping |
| `areaMapEntry` | `qti-area-map-entry` | Area map entry |

### Processing Elements
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `responseProcessing` | `qti-response-processing` | Response processing |
| `outcomeProcessing` | `qti-outcome-processing` | Outcome processing |
| `templateProcessing` | `qti-template-processing` | Template processing |
| `responseCondition` | `qti-response-condition` | Response condition |
| `responseIf` | `qti-response-if` | Response if |
| `responseElseIf` | `qti-response-else-if` | Response else-if |
| `responseElse` | `qti-response-else` | Response else |
| `outcomeCondition` | `qti-outcome-condition` | Outcome condition |
| `outcomeIf` | `qti-outcome-if` | Outcome if |
| `outcomeElseIf` | `qti-outcome-else-if` | Outcome else-if |
| `outcomeElse` | `qti-outcome-else` | Outcome else |

### Processing Actions
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `setOutcomeValue` | `qti-set-outcome-value` | Set outcome |
| `setCorrectResponse` | `qti-set-correct-response` | Set correct response |
| `setDefaultValue` | `qti-set-default-value` | Set default value |
| `setTemplateValue` | `qti-set-template-value` | Set template value |
| `exitResponse` | `qti-exit-response` | Exit response |
| `exitTest` | `qti-exit-test` | Exit test |
| `lookupOutcomeValue` | `qti-lookup-outcome-value` | Lookup outcome |

### Expression Elements
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `baseValue` | `qti-base-value` | Base value |
| `variable` | `qti-variable` | Variable reference |
| `default` | `qti-default` | Default value |
| `correct` | `qti-correct` | Correct response |
| `mapResponse` | `qti-map-response` | Map response |
| `mapResponsePoint` | `qti-map-response-point` | Map response point |
| `null` | `qti-null` | Null value |
| `randomInteger` | `qti-random-integer` | Random integer |
| `randomFloat` | `qti-random-float` | Random float |
| `mathConstant` | `qti-math-constant` | Math constant |
| `testVariables` | `qti-test-variables` | Test variables |

### Statistical Expressions
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `outcomeMaximum` | `qti-outcome-maximum` | Outcome maximum |
| `outcomeMinimum` | `qti-outcome-minimum` | Outcome minimum |
| `numberCorrect` | `qti-number-correct` | Number correct |
| `numberIncorrect` | `qti-number-incorrect` | Number incorrect |
| `numberResponded` | `qti-number-responded` | Number responded |
| `numberPresented` | `qti-number-presented` | Number presented |
| `numberSelected` | `qti-number-selected` | Number selected |

### Operator Elements
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `and` | `qti-and` | Logical AND |
| `or` | `qti-or` | Logical OR |
| `not` | `qti-not` | Logical NOT |
| `match` | `qti-match` | Match comparison |
| `stringMatch` | `qti-string-match` | String match |
| `patternMatch` | `qti-pattern-match` | Pattern match |
| `equal` | `qti-equal` | Equal comparison |
| `equalRounded` | `qti-equal-rounded` | Rounded equal |
| `inside` | `qti-inside` | Inside check |
| `lt` | `qti-lt` | Less than |
| `lte` | `qti-lte` | Less than equal |
| `gt` | `qti-gt` | Greater than |
| `gte` | `qti-gte` | Greater than equal |
| `durationLT` | `qti-duration-lt` | Duration less than |
| `durationGTE` | `qti-duration-gte` | Duration greater equal |

### Math Operators
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `sum` | `qti-sum` | Sum operation |
| `product` | `qti-product` | Product operation |
| `subtract` | `qti-subtract` | Subtraction |
| `divide` | `qti-divide` | Division |
| `power` | `qti-power` | Power operation |
| `integerDivide` | `qti-integer-divide` | Integer division |
| `integerModulus` | `qti-integer-modulus` | Modulus operation |
| `truncate` | `qti-truncate` | Truncate |
| `round` | `qti-round` | Round |
| `integerToFloat` | `qti-integer-to-float` | Type conversion |
| `max` | `qti-max` | Maximum |
| `min` | `qti-min` | Minimum |
| `gcd` | `qti-gcd` | Greatest common divisor |
| `lcm` | `qti-lcm` | Least common multiple |

### Container Operators
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `multiple` | `qti-multiple` | Multiple container |
| `ordered` | `qti-ordered` | Ordered container |
| `containerSize` | `qti-container-size` | Container size |
| `isNull` | `qti-is-null` | Null check |
| `index` | `qti-index` | Index access |
| `fieldValue` | `qti-field-value` | Field value |
| `random` | `qti-random` | Random selection |
| `member` | `qti-member` | Member check |
| `delete` | `qti-delete` | Delete operation |
| `contains` | `qti-contains` | Contains check |
| `substring` | `qti-substring` | Substring |
| `anyN` | `qti-any-n` | Any N operation |

### Advanced Operators
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `mathOperator` | `qti-math-operator` | Math operator |
| `statsOperator` | `qti-stats-operator` | Statistics operator |
| `roundTo` | `qti-round-to` | Round to precision |
| `customOperator` | `qti-custom-operator` | Custom operator |
| `repeat` | `qti-repeat` | Repeat operation |

### Interaction Elements
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `choiceInteraction` | `qti-choice-interaction` | Multiple choice |
| `orderInteraction` | `qti-order-interaction` | Ordering |
| `associateInteraction` | `qti-associate-interaction` | Association |
| `matchInteraction` | `qti-match-interaction` | Matching |
| `gapMatchInteraction` | `qti-gap-match-interaction` | Gap matching |
| `inlineChoiceInteraction` | `qti-inline-choice-interaction` | Inline choice |
| `textEntryInteraction` | `qti-text-entry-interaction` | Text entry |
| `extendedTextInteraction` | `qti-extended-text-interaction` | Extended text |
| `hottextInteraction` | `qti-hottext-interaction` | Hottext |
| `hotspotInteraction` | `qti-hotspot-interaction` | Hotspot |
| `selectPointInteraction` | `qti-select-point-interaction` | Point selection |
| `graphicOrderInteraction` | `qti-graphic-order-interaction` | Graphic ordering |
| `graphicAssociateInteraction` | `qti-graphic-associate-interaction` | Graphic association |
| `graphicGapMatchInteraction` | `qti-graphic-gap-match-interaction` | Graphic gap match |
| `positionObjectInteraction` | `qti-position-object-interaction` | Object positioning |
| `sliderInteraction` | `qti-slider-interaction` | Slider |
| `drawingInteraction` | `qti-drawing-interaction` | Drawing |
| `uploadInteraction` | `qti-upload-interaction` | File upload |
| `mediaInteraction` | `qti-media-interaction` | Media |
| `customInteraction` | `qti-custom-interaction` | Custom |
| `endAttemptInteraction` | `qti-end-attempt-interaction` | End attempt |

### Choice Elements
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `simpleChoice` | `qti-simple-choice` | Simple choice |
| `simpleAssociableChoice` | `qti-simple-associable-choice` | Associable choice |
| `inlineChoice` | `qti-inline-choice` | Inline choice |
| `hottext` | `qti-hottext` | Hottext |
| `hotspotChoice` | `qti-hotspot-choice` | Hotspot choice |
| `associableHotspot` | `qti-associable-hotspot` | Associable hotspot |
| `gap` | `qti-gap` | Gap |
| `gapText` | `qti-gap-text` | Gap text |
| `gapImg` | `qti-gap-img` | Gap image |

### Content Elements
| QTI 2.x Element | QTI 3.0 Element | Purpose |
|----------------|-----------------|---------|
| `itemBody` | `qti-item-body` | Item body |
| `rubricBlock` | `qti-rubric-block` | Rubric block |
| `contentBody` | `qti-content-body` | Content body |
| `feedbackBlock` | `qti-feedback-block` | Block feedback |
| `feedbackInline` | `qti-feedback-inline` | Inline feedback |
| `templateBlock` | `qti-template-block` | Template block |
| `templateInline` | `qti-template-inline` | Template inline |
| `printedVariable` | `qti-printed-variable` | Printed variable |
| `prompt` | `qti-prompt` | Prompt |
| `infoControl` | `qti-info-control` | Info control |
| `modalFeedback` | `qti-modal-feedback` | Modal feedback |
| `stylesheet` | `qti-stylesheet` | Stylesheet |

## Core Methods

### getAttributeAs()
```php
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
```

**Usage in Marshallers:**
```php
// Instead of: $element->getAttribute('baseType')
$baseType = $this->getAttributeAs($element, 'baseType');
// Automatically handles both 'baseType' (QTI 2.x) and 'base-type' (QTI 3.0)
```

### getVersionedElementName()
```php
protected function getVersionedElementName(string $name): string
{
    return $this->getVersion() === '3.0.0' && isset(self::$elementMap[$name]) 
        ? self::$elementMap[$name] 
        : $name;
}
```

**Usage in Marshallers:**
```php
// When creating elements
$elementName = $this->getVersionedElementName('assessmentItem');
// Returns 'qti-assessment-item' for QTI 3.0, 'assessmentItem' for QTI 2.x
```

### getVersionedAttributeName()
```php
protected function getVersionedAttributeName(string $name): string
{
    return $this->getVersion() === '3.0.0' && isset(self::$attributeMap[$name])
        ? self::$attributeMap[$name]
        : $name;
}
```

**Usage in Marshallers:**
```php
// When setting attributes
$attrName = $this->getVersionedAttributeName('baseType');
$element->setAttribute($attrName, $value);
// Uses 'base-type' for QTI 3.0, 'baseType' for QTI 2.x
```

## Usage Patterns

### Reading Attributes (Unmarshalling)
```php
// Old approach (version-specific)
if ($this->getVersion() === '3.0.0') {
    $baseType = $element->getAttribute('base-type');
} else {
    $baseType = $element->getAttribute('baseType');
}

// New approach (version-aware)
$baseType = $this->getAttributeAs($element, 'baseType');
```

### Writing Attributes (Marshalling)
```php
// Old approach (version-specific)
if ($this->getVersion() === '3.0.0') {
    $element->setAttribute('base-type', $value);
} else {
    $element->setAttribute('baseType', $value);
}

// New approach (version-aware)
$attrName = $this->getVersionedAttributeName('baseType');
$element->setAttribute($attrName, $value);
```

### Creating Child Elements
```php
// Old approach (version-specific)
if ($this->getVersion() === '3.0.0') {
    $childElement = $dom->createElement('qti-correct-response');
} else {
    $childElement = $dom->createElement('correctResponse');
}

// New approach (version-aware)
$elementName = $this->getVersionedElementName('correctResponse');
$childElement = $dom->createElement($elementName);
```

## Benefits

### 1. Centralized Conversion Logic
- All name mappings in one place
- No scattered version checks throughout codebase
- Easy to maintain and extend

### 2. Automatic Compatibility
- Marshallers work with both QTI 2.x and 3.0 without modification
- Backward compatibility maintained
- Forward compatibility for future versions

### 3. Clean Marshaller Code
- Marshallers focus on business logic
- Version handling abstracted away
- Consistent patterns across all marshallers

### 4. Comprehensive Coverage
- 35+ attribute mappings cover all QTI 3.0 attributes
- 120+ element mappings cover complete QTI 3.0 specification
- All interaction types and processing elements supported

This trait-based approach provides a robust foundation for handling QTI version differences while maintaining clean, maintainable code.