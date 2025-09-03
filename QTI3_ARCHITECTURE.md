# QTI 3.0 Full Support Architecture

## Problem Statement

The QTI-SDK currently has an **infinite loop issue** that prevents full QTI 3.0 unmarshalling due to circular dependencies in the marshaller-factory architecture. While element mappings are complete, the recursive marshaller system creates an endless cycle when processing nested QTI 3.0 elements.

## Root Cause Analysis

### Current Architecture Issues

```
RecursiveMarshaller::unmarshall()
    ↓
MarshallerFactory::createMarshaller()
    ↓
Qti30MarshallerFactory::instantiateMarshaller()
    ↓
Marshaller::__construct($version)
    ↓
Marshaller::setVersion()
    ↓
Marshaller::getMarshallerFactory()
    ↓
QtiVersion::create($version)->getMarshallerFactory()
    ↓
[INFINITE LOOP - Back to MarshallerFactory creation]
```

### Key Problems

1. **Circular Dependency**: Marshallers need factories, factories create marshallers
2. **Version Resolution**: Each marshaller recreates version context
3. **Recursive Creation**: Nested elements trigger infinite marshaller instantiation
4. **Factory Coupling**: Tight coupling between marshaller lifecycle and factory access

## Proposed Solution Architecture

### 1. Dependency Injection Pattern

#### MarshallerContext (New)
```php
class MarshallerContext
{
    private string $version;
    private MarshallerFactory $factory;
    private bool $webComponentFriendly;
    
    public function __construct(string $version, MarshallerFactory $factory, bool $webComponentFriendly = false)
    {
        $this->version = $version;
        $this->factory = $factory;
        $this->webComponentFriendly = $webComponentFriendly;
    }
    
    public function getFactory(): MarshallerFactory { return $this->factory; }
    public function getVersion(): string { return $this->version; }
    public function isWebComponentFriendly(): bool { return $this->webComponentFriendly; }
}
```

#### Modified Marshaller Base Class
```php
abstract class Marshaller
{
    private ?MarshallerContext $context = null;
    
    public function __construct(string $version, ?MarshallerContext $context = null)
    {
        $this->version = $version;
        $this->context = $context;
    }
    
    public function getMarshallerFactory(): MarshallerFactory
    {
        if ($this->context !== null) {
            return $this->context->getFactory();
        }
        
        // Fallback for backward compatibility
        if ($this->marshallerFactory === null) {
            $version = QtiVersion::create($this->version);
            $this->marshallerFactory = $version->getMarshallerFactory();
        }
        return $this->marshallerFactory;
    }
    
    public function setContext(MarshallerContext $context): void
    {
        $this->context = $context;
    }
}
```

### 2. Factory Registry Pattern

#### MarshallerRegistry (New)
```php
class MarshallerRegistry
{
    private static array $instances = [];
    private array $marshallers = [];
    private MarshallerContext $context;
    
    public static function getInstance(string $version): self
    {
        if (!isset(self::$instances[$version])) {
            $qtiVersion = QtiVersion::create($version);
            $factory = $qtiVersion->getMarshallerFactory();
            $context = new MarshallerContext($version, $factory);
            self::$instances[$version] = new self($context);
        }
        return self::$instances[$version];
    }
    
    private function __construct(MarshallerContext $context)
    {
        $this->context = $context;
        $this->preRegisterMarshallers();
    }
    
    private function preRegisterMarshallers(): void
    {
        $factory = $this->context->getFactory();
        foreach ($factory->getMappings() as $element => $marshallerClass) {
            $this->marshallers[$element] = null; // Lazy initialization
        }
    }
    
    public function getMarshaller(string $element): Marshaller
    {
        if (!isset($this->marshallers[$element])) {
            throw new MarshallerNotFoundException("No marshaller for element: $element");
        }
        
        if ($this->marshallers[$element] === null) {
            $factory = $this->context->getFactory();
            $marshaller = $factory->instantiateMarshaller($element);
            $marshaller->setContext($this->context);
            $this->marshallers[$element] = $marshaller;
        }
        
        return $this->marshallers[$element];
    }
}
```

### 3. Enhanced Factory Implementation

#### Modified MarshallerFactory
```php
abstract class MarshallerFactory
{
    private ?MarshallerRegistry $registry = null;
    
    public function createMarshaller($elementOrComponent): Marshaller
    {
        $elementName = $this->getElementName($elementOrComponent);
        
        if ($this->registry === null) {
            $this->registry = MarshallerRegistry::getInstance($this->getVersion());
        }
        
        return $this->registry->getMarshaller($elementName);
    }
    
    public function instantiateMarshaller(string $element): Marshaller
    {
        $mappings = $this->getMappings();
        if (!isset($mappings[$element])) {
            throw new MarshallerNotFoundException("No marshaller mapping for: $element");
        }
        
        $marshallerClass = $mappings[$element];
        return new $marshallerClass($this->getVersion());
    }
    
    abstract public function getMappings(): array;
    abstract public function getVersion(): string;
}
```

### 4. QTI 3.0 Specific Implementation

#### Enhanced Qti30MarshallerFactory
```php
class Qti30MarshallerFactory extends MarshallerFactory
{
    public function getVersion(): string
    {
        return '3.0.0';
    }
    
    public function getMappings(): array
    {
        return array_merge(
            parent::getMappings(),
            [
                // QTI 3.0 specific mappings with qti- prefix
                'qti-assessment-item' => AssessmentItemMarshaller::class,
                'qti-outcome-processing' => OutcomeProcessingMarshaller::class,
                'qti-response-processing' => ResponseProcessingMarshaller::class,
                'qti-sum' => OperatorMarshaller::class,
                'qti-gte' => OperatorMarshaller::class,
                // ... all other QTI 3.0 mappings
            ]
        );
    }
}
```

## Implementation Plan

### Phase 1: Core Architecture (Week 1-2)

1. **Create MarshallerContext class**
   - Version and factory injection
   - Web component friendly flag

2. **Implement MarshallerRegistry**
   - Singleton pattern per version
   - Lazy marshaller instantiation
   - Pre-registration of mappings

3. **Modify Marshaller base class**
   - Context injection support
   - Backward compatibility maintenance

### Phase 2: Factory Enhancement (Week 2-3)

1. **Update MarshallerFactory**
   - Registry integration
   - Enhanced element name resolution

2. **Enhance version-specific factories**
   - Mapping method implementation
   - Version identification

3. **Update RecursiveMarshaller**
   - Context-aware processing
   - Circular dependency elimination

### Phase 3: Testing & Validation (Week 3-4)

1. **Unit tests for new components**
2. **Integration tests for QTI 3.0 processing**
3. **Performance benchmarking**
4. **Backward compatibility validation**

## Benefits

### Immediate
- **Resolves infinite loop** - Breaks circular dependency
- **Enables QTI 3.0 unmarshalling** - Full document processing
- **Maintains backward compatibility** - QTI 2.x continues working

### Long-term
- **Performance improvement** - Marshaller reuse and caching
- **Memory efficiency** - Reduced object creation
- **Extensibility** - Easy addition of new QTI versions
- **Maintainability** - Clear separation of concerns

## Migration Strategy

### Backward Compatibility
- Existing code continues working without changes
- Gradual migration to new context-based approach
- Deprecation warnings for old patterns

### New Usage Pattern
```php
// Old approach (still works)
$doc = new XmlDocument('3.0');
$doc->load('qti3-item.xml', false);

// New approach (recommended)
$context = new MarshallerContext('3.0.0', new Qti30MarshallerFactory());
$doc = new XmlDocument('3.0', $context);
$doc->load('qti3-item.xml', false);
```

## Risk Mitigation

### Technical Risks
- **Breaking changes**: Comprehensive testing and backward compatibility
- **Performance impact**: Benchmarking and optimization
- **Complex refactoring**: Incremental implementation with rollback capability

### Business Risks
- **Development time**: Phased approach with early validation
- **Regression issues**: Extensive test coverage
- **Adoption challenges**: Clear migration documentation

## Success Metrics

1. **QTI 3.0 files load completely** without infinite loops
2. **All existing QTI 2.x functionality** remains intact
3. **Performance improvement** of 10-20% due to marshaller reuse
4. **Memory usage reduction** of 15-25% for large documents
5. **Zero breaking changes** for existing API consumers

This architecture provides a robust foundation for full QTI 3.0 support while maintaining the stability and performance of the existing QTI-SDK.