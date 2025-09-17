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
use qtism\data\processing\ResponseProcessing;
use qtism\data\QtiComponent;
use qtism\data\rules\ResponseRuleCollection;

/**
 * Marshalling/Unmarshalling implementation for responseProcessing.
 */
class ResponseProcessingMarshaller extends Marshaller
{
    /**
     * Marshall a ResponseProcessing object into a DOMElement object.
     *
     * @param QtiComponent $component A ResponseProcessing object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $elementName = ($this->getVersion() === '3.0.0') ? 'qti-response-processing' : 'responseProcessing';
        $element = $this->createElement($component, $elementName);

        if ($component->hasTemplate() === true) {
            $this->setDOMElementAttribute($element, 'template', $component->getTemplate());
        }

        if ($component->hasTemplateLocation() === true) {
            $templateLocationAttr = ($this->getVersion() === '3.0.0') ? 'template-location' : 'templateLocation';
            $this->setDOMElementAttribute($element, $templateLocationAttr, $component->getTemplateLocation());
        }

        foreach ($component->getResponseRules() as $responseRule) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseRule);
            $element->appendChild($marshaller->marshall($responseRule));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI responseProcessing element.
     *
     * @param DOMElement $element A DOMElement object.
     * @param ResponseProcessing|null $responseProcessing
     * @return ResponseProcessing A ResponseProcessing object.
     * @throws MarshallerNotFoundException
     */
    protected function unmarshall(
        DOMElement $element,
        ?ResponseProcessing $responseProcessing = null
    ): ResponseProcessing {
        $responseRuleElts = self::getChildElements($element);

        $responseRules = new ResponseRuleCollection();
        for ($i = 0; $i < count($responseRuleElts); $i++) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($responseRuleElts[$i]);
            $responseRules[] = $marshaller->unmarshall($responseRuleElts[$i]);
        }

        if ($responseProcessing === null) {
            $object = new ResponseProcessing($responseRules);
        } else {
            $object = $responseProcessing;
            $object->setResponseRules($responseRules);
        }

        if (($template = $this->getDOMElementAttributeAs($element, 'template')) !== null) {
            $object->setTemplate($template);
        }

        $templateLocationAttr = ($this->getVersion() === '3.0.0') ? 'template-location' : 'templateLocation';
        if (($templateLocation = $this->getDOMElementAttributeAs($element, $templateLocationAttr)) !== null) {
            $object->setTemplateLocation($templateLocation);
        }

        return $object;
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
        
        $expectedNames = ['responseProcessing', 'qti-response-processing'];
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
