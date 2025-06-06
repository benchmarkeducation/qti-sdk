<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\content\InlineCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\html5\Rp;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;

class Html5RpMarshallerTest extends Html5ElementMarshallerTest
{
    private const SUBJECT_QTI_CLASS_NAME = 'rp';

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5MarshallingOnlyInQti22AndAbove(new Rp(), self::SUBJECT_QTI_CLASS_NAME);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $id = 'id';
        $class = 'testclass';

        $expected = sprintf(
            '<%1$s id="%2$s" class="%3$s">text content</%4$s>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
            $id,
            $class,
            $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $object = new Rp(null, null, $id, $class);
        $object->setContent(new InlineCollection([new TextRun('text content')]));

        $this->assertMarshalling($expected, $object);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = sprintf(
            '<%s>text content</%s>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
            $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $object = new Rp();
        $object->setContent(new InlineCollection([new TextRun('text content')]));

        $this->assertMarshalling($expected, $object);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove(
            sprintf(
                '<%s></%s>',
                $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
                $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
            ),
            self::SUBJECT_QTI_CLASS_NAME
        );
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $id = 'id';
        $class = 'testclass';

        $xml = sprintf(
            '<%1$s id="%2$s" class="%3$s">text content</%4$s>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
            $id,
            $class,
            $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $expected = new Rp(null, null, $id, $class);
        $expected->setContent(new InlineCollection([new TextRun('text content')]));

        $this->assertUnmarshalling($expected, $xml);
    }

    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = sprintf(
            '<%s>text content</%s>',
            $this->namespaceTag(self::SUBJECT_QTI_CLASS_NAME),
            $this->prefixTag(self::SUBJECT_QTI_CLASS_NAME)
        );

        $expected = new Rp();
        $expected->setContent(new InlineCollection([new TextRun('text content')]));

        $this->assertUnmarshalling($expected, $xml);
    }
}
