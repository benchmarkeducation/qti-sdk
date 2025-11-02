# QTI 3.0 Usage Guide

## Quick Start

### Loading QTI 3.0 Files
```php
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;

// Load QTI 3.0 assessment item
$doc = new XmlDocument('3.0');
$doc->load('qti3-item.xml', false); // false = no validation (recommended)

// Create item session (works the same as QTI 2.x)
$itemSession = new AssessmentItemSession($doc->getDocumentComponent());
$itemSession->beginItemSession();
```

### Processing QTI 3.0 Tests
```php
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentTestSession;

// Load QTI 3.0 assessment test
$testDoc = new XmlDocument('3.0');
$testDoc->load('qti3-test.xml', false);

// Create test session
$testSession = new AssessmentTestSession($testDoc->getDocumentComponent());
$testSession->beginTestSession();
```

## QTI 3.0 vs QTI 2.x Differences

### Element Names
| QTI 2.x | QTI 3.0 | Description |
|---------|---------|-------------|
| `<assessmentItem>` | `<qti-assessment-item>` | Assessment item root |
| `<responseDeclaration>` | `<qti-response-declaration>` | Response variable |
| `<choiceInteraction>` | `<qti-choice-interaction>` | Multiple choice |
| `<correctResponse>` | `<qti-correct-response>` | Correct response |
| `<outcomeProcessing>` | `<qti-outcome-processing>` | Outcome processing |

### Attribute Names
| QTI 2.x | QTI 3.0 | Description |
|---------|---------|-------------|
| `timeDependent="true"` | `time-dependent="true"` | Time dependency |
| `baseType="identifier"` | `base-type="identifier"` | Variable type |
| `responseIdentifier="RESPONSE"` | `response-identifier="RESPONSE"` | Response ID |
| `maxChoices="2"` | `max-choices="2"` | Maximum choices |
| `toolName="QTI-SDK"` | `tool-name="QTI-SDK"` | Tool name |

## Complete Usage Examples

### 1. Basic QTI 3.0 Item Loading
```php
<?php
use qtism\data\storage\xml\XmlDocument;

// Create QTI 3.0 document
$doc = new XmlDocument('3.0');

try {
    // Load QTI 3.0 file (validation disabled for QTI 3.0)
    $doc->load('/path/to/qti3-item.xml', false);
    
    // Get the assessment item
    $item = $doc->getDocumentComponent();
    
    echo "Successfully loaded QTI 3.0 item: " . $item->getIdentifier() . "\n";
    echo "Title: " . $item->getTitle() . "\n";
    echo "Time dependent: " . ($item->isTimeDependent() ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "Error loading QTI 3.0 item: " . $e->getMessage() . "\n";
}
```

### 2. QTI 3.0 Item Session with Responses
```php
<?php
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\QtiIdentifier;

// Load QTI 3.0 choice interaction item
$doc = new XmlDocument('3.0');
$doc->load('qti3-choice-item.xml', false);
$item = $doc->getDocumentComponent();

// Create item session
$itemSession = new AssessmentItemSession($item);
$itemSession->beginItemSession();
$itemSession->beginAttempt();

// Create response (multiple choice with identifiers 'A' and 'B')
$responses = new State([
    new ResponseVariable(
        'RESPONSE',
        Cardinality::MULTIPLE,
        BaseType::IDENTIFIER,
        new MultipleContainer(
            BaseType::IDENTIFIER,
            [
                new QtiIdentifier('A'),
                new QtiIdentifier('B')
            ]
        )
    )
]);

// Submit response and end attempt
$itemSession->endAttempt($responses);

// Display results
echo "Response: " . $itemSession['RESPONSE'] . "\n";
echo "Score: " . $itemSession['SCORE'] . "\n";
echo "Completion Status: " . $itemSession['completionStatus'] . "\n";

$itemSession->endItemSession();
```

### 3. QTI 3.0 Test Session Processing
```php
<?php
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\RouteItem;

// Load QTI 3.0 test
$testDoc = new XmlDocument('3.0');
$testDoc->load('qti3-test.xml', false);
$test = $testDoc->getDocumentComponent();

// Create test session
$testSession = new AssessmentTestSession($test);
$testSession->beginTestSession();

// Navigate through test items
while ($testSession->getRoute()->valid()) {
    $routeItem = $testSession->getRoute()->current();
    
    if ($routeItem instanceof RouteItem) {
        echo "Processing item: " . $routeItem->getAssessmentItemRef()->getIdentifier() . "\n";
        
        // Begin item session
        $testSession->beginAttempt();
        
        // Create sample response (adapt based on interaction type)
        $responses = new State([
            new ResponseVariable(
                'RESPONSE',
                Cardinality::SINGLE,
                BaseType::IDENTIFIER,
                new QtiIdentifier('A')
            )
        ]);
        
        // Submit response
        $testSession->endAttempt($responses);
        
        // Move to next item
        $testSession->moveNext();
    }
}

// End test session
$testSession->endTestSession();

echo "Test completed. Final score: " . $testSession['SCORE'] . "\n";
```

### 4. Creating QTI 3.0 Content Programmatically
```php
<?php
use qtism\data\AssessmentItem;
use qtism\data\content\ItemBody;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\ResponseDeclaration;
use qtism\data\CorrectResponse;
use qtism\data\Value;
use qtism\data\storage\xml\XmlDocument;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

// Create response declaration
$responseDeclaration = new ResponseDeclaration('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE);
$correctResponse = new CorrectResponse([new Value('A', BaseType::IDENTIFIER)]);
$responseDeclaration->setCorrectResponse($correctResponse);

// Create choice interaction
$choice1 = new SimpleChoice('A');
$choice1->setContent('Correct answer');
$choice2 = new SimpleChoice('B');
$choice2->setContent('Incorrect answer');

$choiceInteraction = new ChoiceInteraction('RESPONSE', [$choice1, $choice2]);
$choiceInteraction->setMaxChoices(1);

// Create item body
$itemBody = new ItemBody();
$itemBody->setContent([$choiceInteraction]);

// Create assessment item
$assessmentItem = new AssessmentItem('item-1', 'Sample QTI 3.0 Item', false);
$assessmentItem->setResponseDeclarations([$responseDeclaration]);
$assessmentItem->setItemBody($itemBody);

// Save as QTI 3.0 XML
$doc = new XmlDocument('3.0');
$doc->setDocumentComponent($assessmentItem);
$doc->save('generated-qti3-item.xml');

echo "QTI 3.0 item created and saved\n";
```

### 5. Converting QTI 2.x to QTI 3.0
```php
<?php
use qtism\data\storage\xml\XmlDocument;

// Load QTI 2.x file
$qti2Doc = new XmlDocument('2.1');
$qti2Doc->load('qti2-item.xml');
$item = $qti2Doc->getDocumentComponent();

// Save as QTI 3.0
$qti3Doc = new XmlDocument('3.0');
$qti3Doc->setDocumentComponent($item);
$qti3Doc->save('converted-qti3-item.xml');

echo "Converted QTI 2.x to QTI 3.0\n";
```

## Working with Different Interaction Types

### Choice Interaction (QTI 3.0)
```xml
<qti-choice-interaction response-identifier="RESPONSE" max-choices="1">
    <qti-prompt>Select the correct answer:</qti-prompt>
    <qti-simple-choice identifier="A">Option A</qti-simple-choice>
    <qti-simple-choice identifier="B">Option B</qti-simple-choice>
</qti-choice-interaction>
```

### Text Entry Interaction (QTI 3.0)
```xml
<qti-text-entry-interaction response-identifier="RESPONSE" expected-length="10">
    <qti-prompt>Enter your answer:</qti-prompt>
</qti-text-entry-interaction>
```

### Hottext Interaction (QTI 3.0)
```xml
<qti-hottext-interaction response-identifier="RESPONSE" max-choices="2">
    <p>Select the <qti-hottext identifier="hot1">correct</qti-hottext> words from this 
    <qti-hottext identifier="hot2">sentence</qti-hottext>.</p>
</qti-hottext-interaction>
```

## Response Processing Examples

### Basic Response Processing (QTI 3.0)
```xml
<qti-response-processing>
    <qti-response-condition>
        <qti-response-if>
            <qti-match>
                <qti-variable identifier="RESPONSE"/>
                <qti-correct identifier="RESPONSE"/>
            </qti-match>
            <qti-set-outcome-value identifier="SCORE">
                <qti-base-value base-type="float">1.0</qti-base-value>
            </qti-set-outcome-value>
        </qti-response-if>
        <qti-response-else>
            <qti-set-outcome-value identifier="SCORE">
                <qti-base-value base-type="float">0.0</qti-base-value>
            </qti-set-outcome-value>
        </qti-response-else>
    </qti-response-condition>
</qti-response-processing>
```

### Advanced Response Processing with Mapping
```xml
<qti-response-processing>
    <qti-set-outcome-value identifier="SCORE">
        <qti-map-response identifier="RESPONSE"/>
    </qti-set-outcome-value>
</qti-response-processing>
```

## Outcome Processing Examples

### Test-Level Outcome Processing (QTI 3.0)
```xml
<qti-outcome-processing>
    <qti-set-outcome-value identifier="TOTAL_SCORE">
        <qti-sum>
            <qti-test-variables variable-identifier="SCORE"/>
        </qti-sum>
    </qti-set-outcome-value>
    <qti-outcome-condition>
        <qti-outcome-if>
            <qti-gte>
                <qti-variable identifier="TOTAL_SCORE"/>
                <qti-base-value base-type="float">0.6</qti-base-value>
            </qti-gte>
            <qti-set-outcome-value identifier="PASS">
                <qti-base-value base-type="boolean">true</qti-base-value>
            </qti-set-outcome-value>
        </qti-outcome-if>
    </qti-outcome-condition>
</qti-outcome-processing>
```

## Error Handling and Debugging

### Common Issues and Solutions

#### 1. Validation Errors
```php
// QTI 3.0 validation may fail due to missing external schemas
// Always use validation=false for QTI 3.0
$doc = new XmlDocument('3.0');
$doc->load('qti3-file.xml', false); // false = skip validation
```

#### 2. Element Not Found Errors
```php
try {
    $doc = new XmlDocument('3.0');
    $doc->load('qti3-file.xml', false);
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'No marshaller found') !== false) {
        echo "Missing QTI 3.0 element mapping. Check Qti30MarshallerFactory.php\n";
    }
}
```

#### 3. Attribute Reading Issues
```php
// If attributes are not being read correctly, check VersionAwareMarshaller mappings
// The trait should handle both QTI 2.x and 3.0 attribute names automatically
```

### Debugging QTI 3.0 Processing
```php
// Enable debug mode to see processing details
$doc = new XmlDocument('3.0');
$doc->load('qti3-file.xml', false);

// Check document version
echo "Document version: " . $doc->getVersion() . "\n";

// Check root element
$root = $doc->getDocumentComponent();
echo "Root element type: " . get_class($root) . "\n";

// Check marshaller factory
$factory = $doc->getMarshallerFactory();
echo "Factory type: " . get_class($factory) . "\n";
```

## Performance Considerations

### 1. Validation Disabled
QTI 3.0 files should be loaded with validation disabled for better performance:
```php
$doc->load('qti3-file.xml', false); // false = no validation
```

### 2. Memory Usage
Large QTI 3.0 tests may consume significant memory. Monitor usage:
```php
echo "Memory usage: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
```

### 3. Session Persistence
Use binary persistence for better performance with large test sessions:
```php
use qtism\runtime\storage\binary\BinaryAssessmentTestSeeker;
use qtism\runtime\storage\binary\QtiBinaryStreamAccess;

$stream = new QtiBinaryStreamAccess(fopen('session.bin', 'w+'));
$testSession = new AssessmentTestSession($test, $stream);
```

## Best Practices

### 1. Always Use Version 3.0 for QTI 3.0 Files
```php
// Correct
$doc = new XmlDocument('3.0');

// Incorrect - will not handle QTI 3.0 elements properly
$doc = new XmlDocument('2.1');
```

### 2. Disable Validation for QTI 3.0
```php
// QTI 3.0 XSD validation may fail due to external dependencies
$doc->load('qti3-file.xml', false);
```

### 3. Handle Both QTI Versions in Applications
```php
function loadQtiFile($filename) {
    // Detect QTI version from file content
    $content = file_get_contents($filename);
    if (strpos($content, 'qti-assessment-item') !== false) {
        $doc = new XmlDocument('3.0');
    } else {
        $doc = new XmlDocument('2.1');
    }
    
    $doc->load($filename, false);
    return $doc;
}
```

### 4. Error Handling
```php
try {
    $doc = new XmlDocument('3.0');
    $doc->load('qti3-file.xml', false);
    $item = $doc->getDocumentComponent();
} catch (Exception $e) {
    error_log("QTI 3.0 loading failed: " . $e->getMessage());
    // Handle error appropriately
}
```

This usage guide provides comprehensive examples for working with QTI 3.0 files using the QTI-SDK's complete QTI 3.0 support.