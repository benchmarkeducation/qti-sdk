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
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\QtiComponent;
use qtism\data\storage\Utils;

/**
 * Marshalling/Unmarshalling implementation for BaseValue.
 */
class BaseValueMarshaller extends Marshaller
{
    /**
     * Marshall a BaseValue object into a DOMElement object.
     *
     * @param QtiComponent $component A BaseValue object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $elementName = ($this->getVersion() === '3.0.0') ? 'qti-base-value' : 'baseValue';
        $element = $this->createElement($component, $elementName);

        $baseTypeAttr = ($this->getVersion() === '3.0.0') ? 'base-type' : 'baseType';
        $this->setDOMElementAttribute($element, $baseTypeAttr, BaseType::getNameByConstant($component->getBaseType()));
        self::setDOMElementValue($element, $component->getValue());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI baseValue element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return BaseValue A BaseValue object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): BaseValue
    {
        $baseTypeAttr = ($this->getVersion() === '3.0.0') ? 'base-type' : 'baseType';
        if (($baseType = $this->getDOMElementAttributeAs($element, $baseTypeAttr, 'string')) !== null) {
            $value = $element->nodeValue;
            $baseTypeCst = BaseType::getConstantByName($baseType);

            // A little bit of cleaning...
            if ($baseTypeCst !== BaseType::STRING) {
                $value = trim($value);
            }

            return new BaseValue($baseTypeCst, Utils::stringToDatatype($value, $baseTypeCst));
        } else {
            $msg = "The mandatory attribute 'baseType' is missing from element '" . $element->localName . "'.";
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
        
        $expectedNames = ['baseValue', 'qti-base-value'];
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
