<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\TextEntryInteraction;
use qtismtest\QtiSmTestCase;

/**
 * Class TextInteractionTest
 */
class TextInteractionTest extends QtiSmTestCase
{
    public function testSetBaseWrongType(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'base' argument must be a positive (>= 0) integer value, 'boolean' given.");

        $textEntryInteraction->setBase(true);
    }

    public function testSetStringIdentifierWrongType(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'stringIdentifier' argument must be a valid QTI identifier or an empty string, '1' given.");

        $textEntryInteraction->setStringIdentifier(true);
    }

    public function testSetExpectedLengthWrongType(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLength" argument must be a non-negative integer (>= 0) or null, "boolean" given.');

        $textEntryInteraction->setExpectedLength(true);
    }

    /**
     * @dataProvider nonNegativeIntegersForExpectedLength
     * @param integer $expectedLength
     */
    public function testSetExpectedLengthToNonNegativeInteger($expectedLength): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength($expectedLength);

        $this::assertTrue($textEntryInteraction->hasExpectedLength());
        $this::assertEquals($expectedLength, $textEntryInteraction->getExpectedLength());
    }

    public function nonNegativeIntegersForExpectedLength(): array
    {
        return [
            [0],
            [1],
            [1012],
            [2 ** 31 - 1],
        ];
    }

    public function testSetExpectedLengthToNegativeIntegerThrowsException(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "expectedLength" argument must be a non-negative integer (>= 0) or null, "-1" given.');

        $textEntryInteraction->setExpectedLength(-1);
    }

    public function testUnsetExpectedLengthWithNull(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $textEntryInteraction->setExpectedLength(null);

        $this::assertFalse($textEntryInteraction->hasExpectedLength());
        $this::assertTrue($textEntryInteraction->getExpectedLength() === -1);
    }

    public function testSetPatternMaskWrongType(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'patternMask' argument must be a string value, 'boolean' given.");

        $textEntryInteraction->setPatternMask(true);
    }

    public function testSetPlaceholderTextWrongType(): void
    {
        $textEntryInteraction = new TextEntryInteraction('RESPONSE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'placeholderText' argument must be a string value, 'boolean' given.");

        $textEntryInteraction->setPlaceholderText(true);
    }
}
