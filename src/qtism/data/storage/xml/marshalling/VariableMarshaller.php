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
use qtism\data\expressions\Variable;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for variable.
 */
class VariableMarshaller extends Marshaller
{
    /**
     * Marshall a Variable object into a DOMElement object.
     *
     * @param QtiComponent $component A Variable object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $elementName = ($this->getVersion() === '3.0.0') ? 'qti-variable' : 'variable';
        $element = $this->createElement($component, $elementName);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        $weightIdentifier = $component->getWeightIdentifier();
        if (!empty($weightIdentifier)) {
            $weightIdentifierAttr = ($this->getVersion() === '3.0.0') ? 'weight-identifier' : 'weightIdentifier';
            $this->setDOMElementAttribute($element, $weightIdentifierAttr, $weightIdentifier);
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI Variable element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return Variable A Variable object.
     * @throws UnmarshallingException If the mandatory attribute 'identifier' is not set in $element.
     */
    protected function unmarshall(DOMElement $element): Variable
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            $object = new Variable($identifier);

            $weightIdentifierAttr = ($this->getVersion() === '3.0.0') ? 'weight-identifier' : 'weightIdentifier';
            if (($weightIdentifier = $this->getDOMElementAttributeAs($element, $weightIdentifierAttr)) !== null) {
                $object->setWeightIdentifier($weightIdentifier);
            }

            return $object;
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
        
        $expectedNames = ['variable', 'qti-variable'];
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
