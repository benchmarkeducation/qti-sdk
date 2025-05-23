<?php

namespace qtismtest\common\beans;

use InvalidArgumentException;
use qtism\common\beans\Bean;
use qtism\common\beans\BeanException;
use qtismtest\common\beans\mocks\NotStrictConstructorBean;
use qtismtest\common\beans\mocks\NotStrictMissingSetterBean;
use qtismtest\common\beans\mocks\SimpleBean;
use qtismtest\common\beans\mocks\StrictBean;
use qtismtest\QtiSmTestCase;
use qtism\common\beans\BeanMethod;
use qtism\common\beans\BeanProperty;

/**
 * Class BeanTest
 */
class BeanTest extends QtiSmTestCase
{
    public function testSimpleBean(): void
    {
        $mock = new SimpleBean('Mister Bean', 'Mini Cooper');
        $bean = new Bean($mock);
        $this::assertInstanceOf(Bean::class, $bean);

        // --- Try to get information about property existence.
        $this::assertTrue($bean->hasProperty('name'));
        // This property simply does not exist.
        $this::assertFalse($bean->hasProperty('miniCooper'));
        // This property exists but is not annotated with @qtism-bean-property.
        $this::assertFalse($bean->hasProperty('uselessProperty'));

        // --- Try to retrieve some bean properties.
        $this::assertInstanceOf(BeanProperty::class, $bean->getProperty('name'));

        // The property does not exist.
        try {
            $beanProperty = $bean->getProperty('miniCooper');
            $this::assertFalse(true, 'An exception must be thrown because the property does not exist in the bean.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NO_PROPERTY, $e->getCode());
        }

        // The property exists but is not annotated.
        try {
            $beanProperty = $bean->getProperty('uselessProperty');
            $this::assertFalse(true, 'An exception must be thrown because the property is not annotated.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NO_PROPERTY, $e->getCode());
        }

        // The annotated properties are ['name', 'car'].
        $names = ['name', 'car'];
        $beanProperties = $bean->getProperties();
        for ($i = 0; $i < count($names); $i++) {
            $this::assertEquals($names[$i], $beanProperties[$i]->getName());
        }

        // --- Try to get information about getter existence.
        $this::assertNotFalse($bean->hasGetter('name'));
        // Simply does not exist.
        $this::assertFalse($bean->hasGetter('miniCooper'));
        // Exists but not related to an annotated property.
        $this::assertFalse($bean->hasGetter('uselessProperty'));

        // --- Try to retrieve some bean methods.
        $this::assertInstanceOf(BeanMethod::class, $bean->getGetter('name'));

        // The getter does not exist.
        try {
            $beanMethod = $bean->getGetter('miniCooper');
            $this::assertTrue(false, 'An exception must thrown because the getter does not exist in the bean.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NO_METHOD, $e->getCode());
        }

        // The getter exists but is not related to a valid bean property.
        try {
            $beanMethod = $bean->getGetter('uselessProperty');
            $this::assertTrue(false, 'An exception must be thrown because the property targeted by the getter is not an annotated bean property.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NO_METHOD, $e->getCode());
        }

        $beanGetters = $bean->getGetters();
        for ($i = 0; $i < count($names); $i++) {
            $this::assertEquals('get' . ucfirst($names[$i]), $beanGetters[$i]->getName());
        }

        // --- Try to get information about setter existence.
        $this::assertTrue($bean->hasSetter('name'));
        // Simply does not exist.
        $this::assertFalse($bean->hasSetter('miniCooper'));
        // Exists but not related to an annotated property.
        $this::assertFalse($bean->hasSetter('uselessProperty'));

        // --- Try to retrieve some bean methods.
        $this::assertInstanceOf(BeanMethod::class, $bean->getSetter('name'));

        // The getter does not exist.
        try {
            $beanMethod = $bean->getSetter('miniCooper');
            $this::assertTrue(false, 'An exception must thrown because the setter does not exist in the bean.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NO_METHOD, $e->getCode());
        }

        // The getter exists but is not related to a valid bean property.
        try {
            $beanMethod = $bean->getSetter('uselessProperty');
            $this::assertTrue(false, 'An exception must be thrown because the property targeted by the getter is not an annotated bean property.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NO_METHOD, $e->getCode());
        }

        $beanGetters = $bean->getSetters();
        for ($i = 0; $i < count($names); $i++) {
            $this::assertEquals('set' . ucfirst($names[$i]), $beanGetters[$i]->getName());
        }

        // --- Play with the constructor
        $beanParams = $bean->getConstructorParameters();
        // The constructor has 3 parameters but only parameters with the same
        // name as a valid bean property are returned.
        $this::assertCount(2, $beanParams);

        for ($i = 0; $i < count($names); $i++) {
            $this::assertEquals($names[$i], $beanParams[$i]->getName());
        }

        $ctorGetters = $bean->getConstructorGetters();
        $this::assertCount(2, $ctorGetters);

        for ($i = 0; $i < count($names); $i++) {
            $this::assertEquals('get' . ucfirst($names[$i]), $ctorGetters[$i]->getName());
        }

        $ctorSetters = $bean->getConstructorSetters();
        $this::assertCount(2, $ctorSetters);

        for ($i = 0; $i < count($names); $i++) {
            $this::assertEquals('set' . ucfirst($names[$i]), $ctorSetters[$i]->getName());
        }

        // The SimpleBean class cannot be considered as a strict bean.
        try {
            $bean = new Bean($mock, true);
            $this::assertTrue(false, 'An exception must be thrown because the SimpleBean class is not a strict bean implementation.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NOT_STRICT, $e->getCode());
        }
    }

    public function testNotStrictBeanBecauseOfConstructor(): void
    {
        // must work in unstrict mode.
        $mock = new NotStrictConstructorBean('John', 'Dunbar', 'red');
        $bean = new Bean($mock);
        $this::assertInstanceOf(Bean::class, $bean);

        try {
            $bean = new Bean($mock, true);
            $this::assertFalse(true, 'An exception must be thrown because the NotStrictConstructorBean class provides an invalid constructor.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NOT_STRICT, $e->getCode());
        }
    }

    public function testNotStrictBeanBecauseOfMissingSetter(): void
    {
        // must work if no strict mode.
        $mock = new NotStrictMissingSetterBean('John', 'Dunbar', 'brown');
        $bean = new Bean($mock);
        $this::assertInstanceOf(Bean::class, $bean);

        try {
            $bean = new Bean($mock, true);
            $this::assertTrue(false, 'An exception must be thrown because the NotStrictMissingSetterBean class has a protected bean setter that should be public.');
        } catch (BeanException $e) {
            $this::assertEquals(BeanException::NOT_STRICT, $e->getCode());
        }
    }

    public function testStrictBean(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'blond', true);
        $bean = new Bean($mock, true);
        $this::assertInstanceOf(Bean::class, $bean);

        $this::assertTrue($bean->hasConstructorParameter('firstName'));
        $this::assertTrue($bean->hasConstructorParameter('lastName'));
        $this::assertTrue($bean->hasConstructorParameter('hair'));
        $this::assertTrue($bean->hasConstructorParameter('cool'));
        $this::assertTrue($bean->hasProperty('firstName'));
        $this::assertTrue($bean->hasProperty('lastName'));
        $this::assertTrue($bean->hasProperty('hair'));
        $this::assertTrue($bean->hasProperty('cool'));
        $this::assertNotFalse($bean->hasGetter('firstName'));
        $this::assertNotFalse($bean->hasGetter('lastName'));
        $this::assertNotFalse($bean->hasGetter('hair'));
        $this::assertNotFalse($bean->hasGetter('cool'));
        $this::assertTrue($bean->hasSetter('firstName'));
        $this::assertTrue($bean->hasSetter('lastName'));
        $this::assertTrue($bean->hasSetter('hair'));
        $this::assertTrue($bean->hasSetter('cool'));

        $this::assertEquals('isCool', $bean->getGetter('cool')->getName());

        $this::assertCount(4, $bean->getGetters());
        $this::assertCount(0, $bean->getGetters(true));

        $this::assertCount(4, $bean->getSetters());
        $this::assertCount(0, $bean->getSetters(true));
    }

    public function testGetGetterByBeanProperty(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'white', false);
        $bean = new Bean($mock, true);

        $property = $bean->getProperty('cool');
        $getter = $bean->getGetter($property);
        $this::assertEquals('isCool', $getter->getName());
    }

    public function testGetSetterByBeanProperty(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'white', false);
        $bean = new Bean($mock, true);

        $property = $bean->getProperty('cool');
        $setter = $bean->getSetter($property);
        $this::assertEquals('setCool', $setter->getName());
    }

    public function testHasGetterByBeanProperty(): void
    {
        $mock = new StrictBean('Mickael', 'Dundie', 'black', true);
        $bean = new Bean($mock);

        $property = $bean->getProperty('firstName');
        $this::assertNotFalse($bean->hasGetter($property));
    }

    public function testHasSetterByBeanProperty(): void
    {
        $mock = new StrictBean('Mickael', 'Dundie', 'black', true);
        $bean = new Bean($mock);

        $property = $bean->getProperty('firstName');
        $this::assertTrue($bean->hasSetter($property));
    }

    public function testWrongInstanciation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The given 'object' argument is not an object.");
        new Bean(10);
    }

    public function testInvalidGetGetterCall(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'white', false);
        $bean = new Bean($mock, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'property' argument must be a string or a BeanProperty object.");
        $bean = new Bean($mock);
        $getter = $bean->getGetter(null);
    }

    public function testInvalidGetSetterCall(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'white', false);
        $bean = new Bean($mock, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'property' argument must be a string or a BeanProperty object.");
        $bean = new Bean($mock);
        $getter = $bean->getSetter(false);
    }

    public function testUnknownSetter(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'white', false);
        $bean = new Bean($mock, true);

        $this->expectException(BeanException::class);
        $this->expectExceptionMessage("The bean has no 'melissa' property.");
        $this->expectExceptionCode(BeanException::NO_METHOD);
        $bean = new Bean($mock);
        $getter = $bean->getSetter('melissa');
    }

    public function testInvalidHasGetterCall(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'white', false);
        $bean = new Bean($mock, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'property' argument must be a string or a BeanProperty object.");
        $bean = new Bean($mock);
        $getter = $bean->hasGetter(null);
    }

    public function testInvalidHasSetterCall(): void
    {
        $mock = new StrictBean('John', 'Dunbar', 'white', false);
        $bean = new Bean($mock, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'property' argument must be a string or a BeanProperty object.");
        $bean = new Bean($mock);
        $getter = $bean->hasSetter(null);
    }

    public function testPropertyButNoSetter(): void
    {
        $mock = new SimpleBean('Name', 'Car');
        $bean = new Bean($mock);

        $this->expectException(BeanException::class);
        $this->expectExceptionMessage("The bean has no public setter for a 'noGetter' property.");
        $setter = $bean->getSetter('noGetter', BeanException::NO_METHOD);
    }
}
