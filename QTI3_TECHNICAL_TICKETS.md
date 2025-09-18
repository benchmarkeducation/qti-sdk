# QTI 3.0 Technical Implementation Tickets

## Critical Path - Response Processing Engine

### QTISDK-001: Fix Qti30MarshallerFactory Missing Mappings
**Type:** Bug Fix  
**Priority:** P0 - Blocker  
**Estimate:** 2 hours

**Problem:**
`RecursiveMarshaller` enters infinite loop when encountering unmapped QTI 3.0 elements in response processing.

**Technical Implementation:**
```php
// File: src/qtism/data/storage/xml/marshalling/Qti30MarshallerFactory.php
// Add missing mappings:
$this->addMappingEntry('qti-response-processing', ResponseProcessingMarshaller::class);
$this->addMappingEntry('qti-response-condition', ResponseConditionMarshaller::class);
$this->addMappingEntry('qti-response-if', ResponseIfMarshaller::class);
$this->addMappingEntry('qti-response-else-if', ResponseElseIfMarshaller::class);
$this->addMappingEntry('qti-response-else', ResponseElseMarshaller::class);
```

**Acceptance Criteria:**
- No infinite loops when loading QTI 3.0 files with `<qti-response-processing>`
- Factory returns correct marshaller instances for QTI 3.0 elements

### QTISDK-002: Implement ResponseProcessingMarshaller QTI 3.0 Support
**Type:** Feature Enhancement  
**Priority:** P0 - Blocker  
**Estimate:** 4 hours

**Files to Modify:**
- `src/qtism/data/storage/xml/marshalling/ResponseProcessingMarshaller.php`

**Implementation Pattern:**
```php
protected function checkUnmarshallerImplementation($element): void
{
    $expectedNames = ['responseProcessing', 'qti-response-processing'];
    if (!in_array($element->localName, $expectedNames)) {
        throw new RuntimeException("Unsupported element: {$element->localName}");
    }
}

// Version-aware child element lookup
$responseConditionTag = ($this->getVersion() === '3.0.0') ? 'qti-response-condition' : 'responseCondition';
$responseConditionElts = $this->getChildElementsByTagName($element, $responseConditionTag);
```

**Dependencies:** QTISDK-001

### QTISDK-003: Implement ResponseConditionMarshaller QTI 3.0 Support
**Type:** Feature Enhancement  
**Priority:** P0 - Blocker  
**Estimate:** 3 hours

**Files to Modify:**
- `src/qtism/data/storage/xml/marshalling/ResponseConditionMarshaller.php`

**Implementation:**
- Add dual element name support: `responseCondition` / `qti-response-condition`
- Update child element lookups for `qti-response-if`, `qti-response-else-if`, `qti-response-else`
- Follow existing marshaller pattern

**Dependencies:** QTISDK-002

### QTISDK-004: Implement Response Condition Logic Marshallers
**Type:** Feature Enhancement  
**Priority:** P0 - Blocker  
**Estimate:** 6 hours

**Files to Create/Modify:**
- `ResponseIfMarshaller.php` - Handle `qti-response-if`
- `ResponseElseIfMarshaller.php` - Handle `qti-response-else-if` 
- `ResponseElseMarshaller.php` - Handle `qti-response-else`

**Implementation Pattern:**
```php
// Each marshaller follows same pattern:
protected function checkUnmarshallerImplementation($element): void
{
    $expectedNames = ['responseIf', 'qti-response-if']; // adjust per marshaller
    // ... validation logic
}

// Version-aware expression and action handling
$expressionTag = ($this->getVersion() === '3.0.0') ? 'qti-match' : 'match';
$actionTag = ($this->getVersion() === '3.0.0') ? 'qti-set-outcome-value' : 'setOutcomeValue';
```

**Dependencies:** QTISDK-003

---

## Response Processing Expressions

### QTISDK-005: Implement Expression Marshallers for QTI 3.0
**Type:** Feature Enhancement  
**Priority:** P0 - Blocker  
**Estimate:** 8 hours

**Files to Modify:**
- `MatchMarshaller.php` - Add `qti-match` support
- `VariableMarshaller.php` - Add `qti-variable` support  
- `CorrectMarshaller.php` - Add `qti-correct` support
- `BaseValueMarshaller.php` - Add `qti-base-value` support

**Implementation Pattern:**
```php
// For each expression marshaller:
protected function checkUnmarshallerImplementation($element): void
{
    $expectedNames = ['match', 'qti-match']; // adjust per marshaller
    if (!in_array($element->localName, $expectedNames)) {
        throw new RuntimeException("Unsupported element: {$element->localName}");
    }
}

// Version-aware attribute handling
$baseTypeAttr = ($this->getVersion() === '3.0.0') ? 'base-type' : 'baseType';
$baseType = $this->getDOMElementAttributeAs($element, $baseTypeAttr);
```

**Factory Mappings Required:**
```php
$this->addMappingEntry('qti-match', MatchMarshaller::class);
$this->addMappingEntry('qti-variable', VariableMarshaller::class);
$this->addMappingEntry('qti-correct', CorrectMarshaller::class);
$this->addMappingEntry('qti-base-value', BaseValueMarshaller::class);
```

**Dependencies:** QTISDK-004

### QTISDK-006: Implement Response Processing Actions
**Type:** Feature Enhancement  
**Priority:** P0 - Blocker  
**Estimate:** 4 hours

**Files to Modify:**
- `SetOutcomeValueMarshaller.php` - Add `qti-set-outcome-value` support

**Implementation:**
```php
protected function checkUnmarshallerImplementation($element): void
{
    $expectedNames = ['setOutcomeValue', 'qti-set-outcome-value'];
    if (!in_array($element->localName, $expectedNames)) {
        throw new RuntimeException("Unsupported element: {$element->localName}");
    }
}

// Version-aware expression child handling
$expressionTags = ($this->getVersion() === '3.0.0') ? 
    ['qti-base-value', 'qti-variable', 'qti-correct'] : 
    ['baseValue', 'variable', 'correct'];
```

**Factory Mapping:**
```php
$this->addMappingEntry('qti-set-outcome-value', SetOutcomeValueMarshaller::class);
```

**Dependencies:** QTISDK-005

---

## Outcome Processing Engine

### QTISDK-007: Implement OutcomeProcessingMarshaller QTI 3.0 Support
**Type:** Feature Enhancement  
**Priority:** P1 - High  
**Estimate:** 4 hours

**Files to Modify:**
- `src/qtism/data/storage/xml/marshalling/OutcomeProcessingMarshaller.php`

**Implementation:**
- Add dual element name support: `outcomeProcessing` / `qti-outcome-processing`
- Update child element lookups for `qti-outcome-condition`
- Follow response processing marshaller pattern

**Factory Mapping:**
```php
$this->addMappingEntry('qti-outcome-processing', OutcomeProcessingMarshaller::class);
```

### QTISDK-008: Implement Outcome Processing Logic Marshallers
**Type:** Feature Enhancement  
**Priority:** P1 - High  
**Estimate:** 6 hours

**Files to Create/Modify:**
- `OutcomeConditionMarshaller.php` - Handle `qti-outcome-condition`
- `OutcomeIfMarshaller.php` - Handle `qti-outcome-if`
- `OutcomeElseIfMarshaller.php` - Handle `qti-outcome-else-if`
- `OutcomeElseMarshaller.php` - Handle `qti-outcome-else`

**Factory Mappings:**
```php
$this->addMappingEntry('qti-outcome-condition', OutcomeConditionMarshaller::class);
$this->addMappingEntry('qti-outcome-if', OutcomeIfMarshaller::class);
$this->addMappingEntry('qti-outcome-else-if', OutcomeElseIfMarshaller::class);
$this->addMappingEntry('qti-outcome-else', OutcomeElseMarshaller::class);
```

**Dependencies:** QTISDK-007

### QTISDK-009: Implement Outcome Processing Expressions
**Type:** Feature Enhancement  
**Priority:** P1 - High  
**Estimate:** 8 hours

**Files to Create/Modify:**
- `SumMarshaller.php` - Add `qti-sum` support
- `TestVariablesMarshaller.php` - Add `qti-test-variables` support
- `GteMarshaller.php` - Add `qti-gte` support
- `LtMarshaller.php` - Add `qti-lt` support

**Factory Mappings:**
```php
$this->addMappingEntry('qti-sum', SumMarshaller::class);
$this->addMappingEntry('qti-test-variables', TestVariablesMarshaller::class);
$this->addMappingEntry('qti-gte', GteMarshaller::class);
$this->addMappingEntry('qti-lt', LtMarshaller::class);
```

**Dependencies:** QTISDK-008

---

## Interaction and Feedback Support

### QTISDK-010: Implement ModalFeedbackMarshaller QTI 3.0 Support
**Type:** Feature Enhancement  
**Priority:** P2 - Medium  
**Estimate:** 3 hours

**Files to Modify:**
- `src/qtism/data/storage/xml/marshalling/ModalFeedbackMarshaller.php`

**Implementation:**
- Add dual element name support: `modalFeedback` / `qti-modal-feedback`
- Handle kebab-case attributes: `show-hide` instead of `showHide`

**Factory Mapping:**
```php
$this->addMappingEntry('qti-modal-feedback', ModalFeedbackMarshaller::class);
```

### QTISDK-011: Extend Interaction Marshallers for QTI 3.0
**Type:** Feature Enhancement  
**Priority:** P2 - Medium  
**Estimate:** 12 hours

**Files to Modify:**
- `ChoiceInteractionMarshaller.php` - Add `qti-choice-interaction` support
- `TextEntryInteractionMarshaller.php` - Add `qti-text-entry-interaction` support
- `MatchInteractionMarshaller.php` - Add `qti-match-interaction` support
- `OrderInteractionMarshaller.php` - Add `qti-order-interaction` support

**Implementation Pattern:**
```php
// For each interaction marshaller:
protected function checkUnmarshallerImplementation($element): void
{
    $expectedNames = ['choiceInteraction', 'qti-choice-interaction']; // adjust per marshaller
    // ... validation
}

// Kebab-case attribute handling
$maxChoicesAttr = ($this->getVersion() === '3.0.0') ? 'max-choices' : 'maxChoices';
$shuffleAttr = ($this->getVersion() === '3.0.0') ? 'shuffle' : 'shuffle'; // no change for this one
```

**Factory Mappings:**
```php
$this->addMappingEntry('qti-choice-interaction', ChoiceInteractionMarshaller::class);
$this->addMappingEntry('qti-text-entry-interaction', TextEntryInteractionMarshaller::class);
$this->addMappingEntry('qti-match-interaction', MatchInteractionMarshaller::class);
$this->addMappingEntry('qti-order-interaction', OrderInteractionMarshaller::class);
```

---

## Runtime and Session Fixes

### QTISDK-012: Fix Built-in Variables Access in QTI 3.0 Sessions
**Type:** Bug Fix  
**Priority:** P2 - Medium  
**Estimate:** 6 hours

**Problem:**
Built-in variables like `numAttempts` not accessible in QTI 3.0 item sessions.

**Files to Investigate:**
- `src/qtism/runtime/tests/AssessmentItemSession.php`
- `src/qtism/runtime/common/State.php`
- Built-in variable handling classes

**Implementation:**
- Debug variable access in QTI 3.0 context
- Ensure session state management works with QTI 3.0 items
- Fix any version-specific variable handling issues

---

## Testing and Quality Assurance

### QTISDK-013: Expand Gold Standard Test Package
**Type:** Test Enhancement  
**Priority:** P3 - Low  
**Estimate:** 8 hours

**Files to Create:**
- `qti-tests/xml-files/gold-standard/choice-interaction.xml`
- `qti-tests/xml-files/gold-standard/text-entry-interaction.xml`
- `qti-tests/xml-files/gold-standard/match-interaction.xml`
- `qti-tests/xml-files/gold-standard/complex-response-processing.xml`
- `qti-tests/xml-files/gold-standard/outcome-processing-test.xml`

**Test Script Updates:**
- Extend `qti-tests/scripts/test-qti3-comprehensive.php`
- Add validation for each new test file
- Ensure backward compatibility testing

---

## Implementation Order and Dependencies

```
Phase 1 (Critical - Week 1):
QTISDK-001 → QTISDK-002 → QTISDK-003 → QTISDK-004

Phase 2 (Critical - Week 2):
QTISDK-005 → QTISDK-006

Phase 3 (High Priority - Week 3):
QTISDK-007 → QTISDK-008 → QTISDK-009

Phase 4 (Medium Priority - Week 4):
QTISDK-010 → QTISDK-011 → QTISDK-012

Phase 5 (Quality - Week 5):
QTISDK-013
```

## Testing Strategy

**Unit Tests:**
- Each marshaller must have corresponding unit tests
- Test both QTI 2.x and 3.0 element handling
- Verify attribute mapping (camelCase ↔ kebab-case)

**Integration Tests:**
- Use gold standard package for end-to-end validation
- Test complete response processing workflows
- Verify session state management

**Regression Tests:**
- Ensure all existing QTI 2.x functionality unchanged
- Performance benchmarking
- Memory usage validation

## Code Review Checklist

- [ ] Dual element name support implemented
- [ ] Version-aware attribute handling
- [ ] Factory mappings added
- [ ] Unit tests written
- [ ] Backward compatibility verified
- [ ] Documentation updated
- [ ] Performance impact assessed