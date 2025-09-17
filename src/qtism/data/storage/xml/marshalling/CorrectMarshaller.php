<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\expressions\Correct;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for correct.
 */
class CorrectMarshaller extends Marshaller
{
    /**
     * Marshall a Correct object into a DOMElement object.
     *
     * @param QtiComponent $component A Correct object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $elementName = ($this->getVersion() === '3.0.0') ? 'qti-correct' : 'correct';
        $element = $this->createElement($component, $elementName);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI correct element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return Correct A Correct object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): Correct
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
            return new Correct($identifier);
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return '';
    }

    /**
     * Override to handle both QTI 2.x and 3.0 element names
     */
    protected function checkUnmarshallerImplementation($element): void
    {
        if (!$element instanceof \DOMElement) {
            $nodeName = $this->getElementName($element);
            throw new \RuntimeException("No Marshaller implementation found while unmarshalling element '{$nodeName}'.");
        }
        
        $expectedNames = ['correct', 'qti-correct'];
        if (!in_array($element->localName, $expectedNames)) {
            $nodeName = $element->localName;
            throw new \RuntimeException("No Marshaller implementation found while unmarshalling element '{$nodeName}'.");
        }
    }

    private function getElementName($element): string
    {
        if ($element instanceof \DOMElement) {
            return $element->localName;
        }
        if (is_object($element)) {
            return get_class($element);
        }
        return $element;
    }
}
