# QTI 3.0 Factory Layer Documentation

## Overview

The **Factory Layer** is responsible for mapping QTI 3.0 element names to their corresponding marshaller classes. This is the first layer in the two-layer QTI 3.0 architecture.

## File: Qti30MarshallerFactory.php

### Purpose
Maps QTI 3.0 element names (with `qti-` prefix) to existing marshaller classes, enabling the system to know which marshaller to instantiate when encountering specific XML elements.

### Key Responsibilities
1. **Element-to-Class Mapping**: `'qti-assessment-item' => AssessmentItemMarshaller::class`
2. **QTI 2.x Removal**: Removes conflicting QTI 2.x element mappings
3. **Version Context**: Ensures all marshallers are created with QTI 3.0 version context

## Implementation Structure

### Constructor Logic
```php
public function __construct()
{
    parent::__construct();
    $this->setWebComponentFriendly(true);
    
    // 1. Remove QTI 2.x conflicting mappings
    $this->removeMappingEntry('assessmentItem');
    $this->removeMappingEntry('responseDeclaration');
    // ... (35+ removals)
    
    // 2. Add QTI 3.0 element mappings
    $this->addMappingEntry('qti-assessment-item', AssessmentItemMarshaller::class);
    $this->addMappingEntry('qti-response-declaration', ResponseDeclarationMarshaller::class);
    // ... (60+ additions)
}
```

### Marshaller Instantiation
```php
protected function instantiateMarshaller(ReflectionClass $class, array $args): Marshaller
{
    array_unshift($args, '3.0.0'); // Inject QTI 3.0 version
    return Reflection::newInstance($class, $args);
}
```

## Complete Element Mappings

### Core Assessment Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-assessment-item` | AssessmentItemMarshaller | Assessment item root |
| `qti-assessment-test` | AssessmentTestMarshaller | Assessment test root |
| `qti-test-part` | TestPartMarshaller | Test part structure |
| `qti-assessment-section` | AssessmentSectionMarshaller | Test section |
| `qti-assessment-item-ref` | AssessmentItemRefMarshaller | Item reference |

### Variable Declarations
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-response-declaration` | ResponseDeclarationMarshaller | Response variable |
| `qti-outcome-declaration` | OutcomeDeclarationMarshaller | Outcome variable |
| `qti-template-declaration` | TemplateDeclarationMarshaller | Template variable |
| `qti-correct-response` | CorrectResponseMarshaller | Correct response |
| `qti-default-value` | DefaultValueMarshaller | Default value |
| `qti-value` | ValueMarshaller | Value container |

### Processing Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-response-processing` | ResponseProcessingMarshaller | Response processing |
| `qti-outcome-processing` | OutcomeProcessingMarshaller | Outcome processing |
| `qti-template-processing` | TemplateProcessingMarshaller | Template processing |
| `qti-response-condition` | ResponseConditionMarshaller | Response condition |
| `qti-outcome-condition` | OutcomeConditionMarshaller | Outcome condition |

### Control Flow Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-response-if` | ResponseControlMarshaller | Response if condition |
| `qti-response-else-if` | ResponseControlMarshaller | Response else-if |
| `qti-response-else` | ResponseControlMarshaller | Response else |
| `qti-outcome-if` | OutcomeControlMarshaller | Outcome if condition |
| `qti-outcome-else-if` | OutcomeControlMarshaller | Outcome else-if |
| `qti-outcome-else` | OutcomeControlMarshaller | Outcome else |

### Interaction Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-choice-interaction` | ChoiceInteractionMarshaller | Multiple choice |
| `qti-order-interaction` | ChoiceInteractionMarshaller | Ordering |
| `qti-associate-interaction` | AssociateInteractionMarshaller | Association |
| `qti-match-interaction` | MatchInteractionMarshaller | Matching |
| `qti-gap-match-interaction` | GapMatchInteractionMarshaller | Gap matching |
| `qti-inline-choice-interaction` | InlineChoiceInteractionMarshaller | Inline choice |
| `qti-text-entry-interaction` | TextInteractionMarshaller | Text entry |
| `qti-extended-text-interaction` | TextInteractionMarshaller | Extended text |
| `qti-hottext-interaction` | HottextInteractionMarshaller | Hottext |
| `qti-hotspot-interaction` | HotspotInteractionMarshaller | Hotspot |
| `qti-select-point-interaction` | SelectPointInteractionMarshaller | Point selection |
| `qti-graphic-order-interaction` | GraphicOrderInteractionMarshaller | Graphic ordering |
| `qti-graphic-associate-interaction` | GraphicAssociateInteractionMarshaller | Graphic association |
| `qti-graphic-gap-match-interaction` | GraphicGapMatchInteractionMarshaller | Graphic gap match |
| `qti-position-object-interaction` | PositionObjectInteractionMarshaller | Object positioning |
| `qti-slider-interaction` | SliderInteractionMarshaller | Slider |
| `qti-drawing-interaction` | DrawingInteractionMarshaller | Drawing |
| `qti-upload-interaction` | UploadInteractionMarshaller | File upload |
| `qti-media-interaction` | MediaInteractionMarshaller | Media |
| `qti-custom-interaction` | CustomInteractionMarshaller | Custom |
| `qti-end-attempt-interaction` | EndAttemptInteractionMarshaller | End attempt |

### Choice Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-simple-choice` | SimpleChoiceMarshaller | Simple choice option |
| `qti-inline-choice` | InlineChoiceMarshaller | Inline choice option |
| `qti-hottext` | HottextMarshaller | Hottext choice |

### Expression Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-variable` | VariableMarshaller | Variable reference |
| `qti-base-value` | BaseValueMarshaller | Base value |
| `qti-sum` | OperatorMarshaller | Sum operator |
| `qti-gte` | OperatorMarshaller | Greater than equal |
| `qti-not` | OperatorMarshaller | Not operator |
| `qti-is-null` | OperatorMarshaller | Is null check |
| `qti-map-response` | MapResponseMarshaller | Map response |
| `qti-test-variables` | TestVariablesMarshaller | Test variables |

### Content Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-item-body` | ItemBodyMarshaller | Item body content |
| `qti-printed-variable` | PrintedVariableMarshaller | Printed variable |
| `qti-prompt` | PromptMarshaller | Interaction prompt |
| `qti-feedback-block` | FeedbackElementMarshaller | Block feedback |
| `qti-template-block` | TemplateElementMarshaller | Template block |
| `qti-template-inline` | TemplateElementMarshaller | Template inline |
| `qti-rubric-block` | RubricBlockMarshaller | Rubric block |
| `qti-info-control` | InfoControlMarshaller | Info control |

### Metadata Elements
| QTI 3.0 Element | Marshaller Class | Purpose |
|----------------|------------------|---------|
| `qti-modal-feedback` | ModalFeedbackMarshaller | Modal feedback |
| `qti-stylesheet` | StylesheetMarshaller | Stylesheet reference |
| `qti-mapping` | MappingMarshaller | Value mapping |
| `qti-map-entry` | MapEntryMarshaller | Map entry |

## Processing Flow

### 1. Element Recognition
```
XML Parser encounters: <qti-assessment-item>
    ↓
Factory lookup: mappings['qti-assessment-item']
    ↓
Returns: AssessmentItemMarshaller::class
```

### 2. Marshaller Creation
```
Factory creates: new AssessmentItemMarshaller('3.0.0')
    ↓
Marshaller inherits: VersionAwareMarshaller trait
    ↓
Ready for: QTI 3.0 element processing
```

### 3. Version Context
```
All marshallers created with version = '3.0.0'
    ↓
VersionAwareMarshaller trait activated
    ↓
Automatic handling of QTI 3.0 naming conventions
```

## Maintenance Guidelines

### Adding New QTI 3.0 Elements
1. **Identify existing marshaller** that can handle the element logic
2. **Add mapping entry**:
   ```php
   $this->addMappingEntry('qti-new-element', ExistingMarshaller::class);
   ```
3. **Test with sample XML** containing the new element

### Removing Conflicting QTI 2.x Elements
When adding QTI 3.0 elements that conflict with QTI 2.x names:
1. **Remove QTI 2.x mapping**:
   ```php
   $this->removeMappingEntry('oldElementName');
   ```
2. **Add QTI 3.0 mapping**:
   ```php
   $this->addMappingEntry('qti-new-element-name', MarshallerClass::class);
   ```

### Debugging Factory Issues
Common issues and solutions:

**"No marshaller found for element 'qti-xyz'"**
- Add missing mapping entry in factory constructor

**"Marshaller created with wrong version"**
- Check `instantiateMarshaller()` method injects '3.0.0'

**"QTI 2.x element conflicts with QTI 3.0"**
- Ensure QTI 2.x element is removed before adding QTI 3.0 mapping

## Integration with Marshaller Layer

The Factory Layer works seamlessly with the Marshaller Layer:

1. **Factory**: Determines which marshaller class to use
2. **Trait**: Handles name conversion within the marshaller
3. **Marshaller**: Processes element using converted names

This separation ensures clean architecture and maintainable code for comprehensive QTI 3.0 support.