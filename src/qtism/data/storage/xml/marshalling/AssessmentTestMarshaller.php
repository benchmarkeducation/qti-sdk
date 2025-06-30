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
use qtism\data\AssessmentTest;
use qtism\data\QtiComponent;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\TestFeedbackCollection;
use qtism\data\TestPartCollection;

/**
 * Marshalling/Unmarshalling implementation for assessmentTest.
 */
class AssessmentTestMarshaller extends SectionPartMarshaller
{
    /**
     * Marshall an AssessmentTest object into a DOMElement object.
     *
     * @param QtiComponent $component An AssessmentTest object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'title', $component->getTitle());

        $toolName = $component->getToolName();
        if (!empty($toolName)) {
            $toolNameAttr = ($this->getVersion() === '3.0.0') ? 'tool-name' : 'toolName';
            $this->setDOMElementAttribute($element, $toolNameAttr, $component->getToolName());
        }

        $toolVersion = $component->getToolVersion();
        if (!empty($toolVersion)) {
            $toolVersionAttr = ($this->getVersion() === '3.0.0') ? 'tool-version' : 'toolVersion';
            $this->setDOMElementAttribute($element, $toolVersionAttr, $component->getToolVersion());
        }

        foreach ($component->getOutcomeDeclarations() as $outcomeDeclaration) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclaration);
            $element->appendChild($marshaller->marshall($outcomeDeclaration));
        }

        if ($component->hasTimeLimits() === true) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getTimeLimits());
            $element->appendChild($marshaller->marshall($component->getTimeLimits()));
        }

        foreach ($component->getTestParts() as $part) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($part);
            $element->appendChild($marshaller->marshall($part));
        }

        $outcomeProcessing = $component->getOutcomeProcessing();
        if (!empty($outcomeProcessing)) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeProcessing);
            $element->appendChild($marshaller->marshall($outcomeProcessing));
        }

        foreach ($component->getTestFeedbacks() as $feedback) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($feedback);
            $element->appendChild($marshaller->marshall($feedback));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI outcomeProcessing element.
     *
     * If $assessmentTest is provided, it will be decorated with the unmarshalled data and returned,
     * instead of creating a new AssessmentTest object.
     *
     * @param DOMElement $element A DOMElement object.
     * @param AssessmentTest|null $assessmentTest An AssessmentTest object to decorate.
     * @return AssessmentTest An OutcomeProcessing object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element, AssessmentTest $assessmentTest = null): AssessmentTest
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($title = $this->getDOMElementAttributeAs($element, 'title')) !== null) {
                if (empty($assessmentTest)) {
                    $object = new AssessmentTest($identifier, $title);
                } else {
                    $object = $assessmentTest;
                    $object->setIdentifier($identifier);
                    $object->setTitle($title);
                }

                // Get the test parts.
                $testPartTag = ($this->getVersion() === '3.0.0') ? 'qti-test-part' : 'testPart';
                $testPartsElts = $this->getChildElementsByTagName($element, $testPartTag);

                if (count($testPartsElts) > 0) {
                    $testParts = new TestPartCollection();

                    foreach ($testPartsElts as $partElt) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($partElt);
                        $testParts[] = $marshaller->unmarshall($partElt);
                    }

                    $object->setTestParts($testParts);

                    $toolNameAttr = ($this->getVersion() === '3.0.0') ? 'tool-name' : 'toolName';
                    if (($toolName = $this->getDOMElementAttributeAs($element, $toolNameAttr)) !== null) {
                        $object->setToolName($toolName);
                    }

                    $toolVersionAttr = ($this->getVersion() === '3.0.0') ? 'tool-version' : 'toolVersion';
                    if (($toolVersion = $this->getDOMElementAttributeAs($element, $toolVersionAttr)) !== null) {
                        $object->setToolVersion($toolVersion);
                    }

                    $testFeedbackElts = $this->getChildElementsByTagName($element, 'testFeedback');
                    if (count($testFeedbackElts) > 0) {
                        $testFeedbacks = new TestFeedbackCollection();

                        foreach ($testFeedbackElts as $feedbackElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($feedbackElt);
                            $testFeedbacks[] = $marshaller->unmarshall($feedbackElt);
                        }

                        $object->setTestFeedbacks($testFeedbacks);
                    }

                    $outcomeDeclarationTag = ($this->getVersion() === '3.0.0') ? 'qti-outcome-declaration' : 'outcomeDeclaration';
                    $outcomeDeclarationElts = $this->getChildElementsByTagName($element, $outcomeDeclarationTag);
                    if (count($outcomeDeclarationElts) > 0) {
                        $outcomeDeclarations = new OutcomeDeclarationCollection();

                        foreach ($outcomeDeclarationElts as $outcomeDeclarationElt) {
                            $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeDeclarationElt);
                            $outcomeDeclarations[] = $marshaller->unmarshall($outcomeDeclarationElt);
                        }

                        $object->setOutcomeDeclarations($outcomeDeclarations);
                    }

                    $outcomeProcessingTag = ($this->getVersion() === '3.0.0') ? 'qti-outcome-processing' : 'outcomeProcessing';
                    $outcomeProcessingElts = $this->getChildElementsByTagName($element, $outcomeProcessingTag);
                    if (isset($outcomeProcessingElts[0])) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($outcomeProcessingElts[0]);
                        $object->setOutcomeProcessing($marshaller->unmarshall($outcomeProcessingElts[0]));
                    }

                    $timeLimitsElts = $this->getChildElementsByTagName($element, 'timeLimits');
                    if (isset($timeLimitsElts[0])) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($timeLimitsElts[0]);
                        $object->setTimeLimits($marshaller->unmarshall($timeLimitsElts[0]));
                    }

                    return $object;
                } else {
                    $msg = "An 'assessmentTest' element must contain at least one 'testPart' child element. None found.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory attribute 'title' is missing from element 'assessmentTest'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element 'assessmentTest'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'assessmentTest';
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
        
        $expectedNames = ['assessmentTest', 'qti-assessment-test'];
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
