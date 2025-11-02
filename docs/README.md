# QTI 3.0 Documentation Index

## Overview

The QTI-SDK now provides **complete QTI 3.0 support** with a clean two-layer architecture that handles both QTI 2.x and QTI 3.0 seamlessly. This documentation covers the implementation, architecture, and usage of QTI 3.0 features.

## 📚 Documentation Structure

### Core Architecture Documentation
1. **[QTI 3.0 Architecture Overview](QTI3_ARCHITECTURE_OVERVIEW.md)**
   - Complete architecture explanation
   - Two-layer design principles
   - Implementation benefits and design decisions
   - File organization and testing coverage

2. **[QTI 3.0 Factory Layer](QTI3_FACTORY_LAYER.md)**
   - Element-to-marshaller mapping details
   - Complete element mappings (120+ elements)
   - Factory implementation and maintenance guidelines
   - Processing flow and debugging tips

3. **[QTI 3.0 Marshaller Layer](QTI3_MARSHALLER_LAYER.md)**
   - Name conversion and trait documentation
   - Comprehensive attribute mappings (35+ attributes)
   - Complete element mappings with purposes
   - Core methods and usage patterns

### Usage and Implementation
4. **[QTI 3.0 Usage Guide](QTI3_USAGE_GUIDE.md)**
   - Complete usage examples and best practices
   - QTI 3.0 vs QTI 2.x differences
   - Working with different interaction types
   - Error handling and debugging

5. **[QTI 3.0 Implementation Status](QTI3_IMPLEMENTATION_STATUS.md)**
   - Detailed implementation status
   - Complete feature coverage matrix
   - Test results and performance metrics
   - Migration guide and future roadmap

## 🚀 Quick Start

### Loading QTI 3.0 Files
```php
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\tests\AssessmentItemSession;

// Load QTI 3.0 assessment item
$doc = new XmlDocument('3.0');
$doc->load('qti3-item.xml', false); // false = no validation

// Create item session (works the same as QTI 2.x)
$itemSession = new AssessmentItemSession($doc->getDocumentComponent());
$itemSession->beginItemSession();
```

### Key Differences QTI 2.x → QTI 3.0
| QTI 2.x | QTI 3.0 | Type |
|---------|---------|------|
| `<assessmentItem>` | `<qti-assessment-item>` | Element |
| `<choiceInteraction>` | `<qti-choice-interaction>` | Element |
| `baseType="identifier"` | `base-type="identifier"` | Attribute |
| `timeDependent="true"` | `time-dependent="true"` | Attribute |

## 🏗️ Architecture Summary

### Two-Layer Design
1. **Factory Layer** (`Qti30MarshallerFactory.php`)
   - Maps QTI 3.0 element names to marshaller classes
   - `'qti-assessment-item' => AssessmentItemMarshaller::class`

2. **Marshaller Layer** (`VersionAwareMarshaller.php` trait)
   - Handles attribute/element name conversion within marshallers
   - `getAttributeAs('baseType')` finds both `baseType` and `base-type`

### Benefits
- ✅ **Complete QTI 3.0 Support**: All elements and attributes
- ✅ **Backward Compatibility**: QTI 2.x code unchanged
- ✅ **Clean Architecture**: Separation of concerns
- ✅ **No Performance Overhead**: Same speed as QTI 2.x
- ✅ **Comprehensive Testing**: 5467 tests pass

## 📊 Implementation Coverage

### Core Elements (100% Complete)
- Assessment structure: Items, tests, sections, parts
- Variable declarations: Response, outcome, template
- Processing: Response processing, outcome processing
- All interactions: Choice, text, graphic, media, custom
- All expressions: Variables, operators, math functions
- Content elements: Body, feedback, templates, rubrics

### Attributes (100% Complete)
- Core attributes: `time-dependent`, `tool-name`, `base-type`
- Interaction attributes: `response-identifier`, `max-choices`
- Processing attributes: `outcome-identifier`, `variable-identifier`
- Test attributes: `navigation-mode`, `submission-mode`

## 🧪 Testing

### Unit Tests
```bash
# All existing tests pass with QTI 3.0 support
./vendor/bin/phpunit test
# Result: OK (5467 tests, 18250 assertions)
```

### QTI 3.0 Gold Standard
```bash
# Test complete QTI 3.0 functionality
php qti-tests/scripts/test-qti3-comprehensive.php
# Result: 3/3 components working (manifest, test, item)
```

## 🔧 Development Guidelines

### Adding New QTI 3.0 Elements
1. **Add factory mapping** in `Qti30MarshallerFactory.php`:
   ```php
   $this->addMappingEntry('qti-new-element', ExistingMarshaller::class);
   ```

2. **Add name mappings** in `VersionAwareMarshaller.php` (if needed):
   ```php
   'newAttribute' => 'new-attribute',
   'newElement' => 'qti-new-element',
   ```

3. **Test** with sample QTI 3.0 XML containing the new element

### Debugging QTI 3.0 Issues
- **"No marshaller found"**: Check factory mappings
- **"Attribute not found"**: Check trait attribute mappings
- **Version conflicts**: Ensure correct version ('3.0') used

## 📈 Performance & Compatibility

### Performance
- **Loading Speed**: Same as QTI 2.x (no overhead)
- **Memory Usage**: No additional memory requirements
- **Processing**: Efficient trait-based name conversion

### Compatibility
- **Backward**: 100% - All QTI 2.x code works unchanged
- **Forward**: Ready for future QTI versions
- **Cross-Version**: Handle both QTI 2.x and 3.0 in same application

## 🎯 Use Cases

### Educational Platforms
- Load and process QTI 3.0 assessment content
- Maintain compatibility with existing QTI 2.x content
- Seamless migration path from QTI 2.x to 3.0

### Assessment Authoring Tools
- Create QTI 3.0 compliant content programmatically
- Convert between QTI 2.x and 3.0 formats
- Validate QTI 3.0 content structure

### Testing Systems
- Run QTI 3.0 assessments with full session support
- Process complex QTI 3.0 tests with outcome processing
- Handle mixed QTI version content libraries

## 📞 Support

### Documentation
- Read the detailed documentation files in this directory
- Check the main README.md for general QTI-SDK information
- Review test files in `qti-tests/` for examples

### Issues
- Report QTI 3.0 specific issues on GitHub
- Include QTI 3.0 XML samples when reporting problems
- Specify QTI version and SDK version in issue reports

### Contributing
- Follow existing code patterns when adding QTI 3.0 features
- Add comprehensive tests for new functionality
- Update documentation for any new QTI 3.0 capabilities

---

This documentation provides comprehensive coverage of the QTI 3.0 implementation in the QTI-SDK. For specific technical details, usage examples, and implementation guidance, refer to the individual documentation files listed above.