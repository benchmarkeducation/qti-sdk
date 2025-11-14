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
use DOMNode;
use qtism\common\utils\Reflection;
use qtism\data\expressions\Expression;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\ExitTest;
use qtism\data\rules\LookupOutcomeValue;
use qtism\data\rules\OutcomeElseIf;
use qtism\data\rules\OutcomeIf;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use ReflectionClass;
use ReflectionException;

/**
 * Marshalling/Unmarshalling implementation for the abstract OutcomeControl QTI
 * component.
 */
class OutcomeControlMarshaller extends RecursiveMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     * @throws ReflectionException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        $expressionNames = Expression::getExpressionClassNames();
        if ($this->getVersion() === '3.0.0') {
            $qti3Names = array_map(function($name) { return 'qti-' . $name; }, $expressionNames);
            $expressionNames = array_merge($expressionNames, $qti3Names);
        }
        $expressionElts = $this->getChildElementsByTagName($element, $expressionNames);

        if (count($expressionElts) > 0) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElts[0]);
            $expression = $marshaller->unmarshall($expressionElts[0]);
        } elseif (($element->localName == 'outcomeIf' || $element->localName == 'outcomeElseIf' ||
                   $element->localName == 'qti-outcome-if' || $element->localName == 'qti-outcome-else-if') && count($expressionElts) == 0) {
            $msg = "An '" . $element->localName . "' must contain an 'expression' element. None found at line " . $element->getLineNo() . "'.";
            throw new UnmarshallingException($msg, $element);
        }

        // Convert QTI 3.0 element names to QTI 2.x equivalent for component class resolution
        $elementName = $element->localName;
        $qti2ElementName = $this->getQti2ElementName($elementName);
        
        if ($elementName == 'outcomeIf' || $elementName == 'outcomeElseIf' || 
            $elementName == 'qti-outcome-if' || $elementName == 'qti-outcome-else-if') {
            $className = 'qtism\\data\\rules\\' . ucfirst($qti2ElementName);
            $class = new ReflectionClass($className);
            $object = Reflection::newInstance($class, [$expression, $children]);
        } else {
            $className = 'qtism\\data\\rules\\' . ucfirst($qti2ElementName);
            $class = new ReflectionClass($className);
            $object = Reflection::newInstance($class, [$children]);
        }

        return $object;
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);

        if ($component instanceof OutcomeIf || $component instanceof OutcomeElseIf) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
            $element->appendChild($marshaller->marshall($component->getExpression()));
        }

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * @param DOMNode $element
     * @return bool
     */
    protected function isElementFinal(DOMNode $element): bool
    {
        return in_array($element->localName, [
            'exitTest', 'qti-exit-test',
            'lookupOutcomeValue', 'qti-lookup-outcome-value',
            'setOutcomeValue', 'qti-set-outcome-value',
        ]);
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component): bool
    {
        return ($component instanceof ExitTest ||
            $component instanceof LookupOutcomeValue ||
            $component instanceof SetOutcomeValue);
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element): array
    {
        return $this->getChildElementsByTagName($element, [
            'exitTest', 'qti-exit-test',
            'lookupOutcomeValue', 'qti-lookup-outcome-value',
            'setOutcomeValue', 'qti-set-outcome-value',
            'outcomeCondition', 'qti-outcome-condition',
        ]);
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component): array
    {
        return $component->getOutcomeRules()->getArrayCopy();
    }

    /**
     * @param DOMElement $currentNode
     * @return OutcomeRuleCollection
     */
    protected function createCollection(DOMElement $currentNode): OutcomeRuleCollection
    {
        return new OutcomeRuleCollection();
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return '';
    }
    
    /**
     * Convert QTI 3.0 element name to QTI 2.x equivalent for component class resolution
     * @param string $elementName
     * @return string
     */
    private function getQti2ElementName(string $elementName): string
    {
        // Convert qti-outcome-if -> outcomeIf, qti-outcome-else-if -> outcomeElseIf, etc.
        if (strpos($elementName, 'qti-') === 0) {
            $withoutPrefix = substr($elementName, 4); // Remove 'qti-'
            // Convert kebab-case to camelCase
            return lcfirst(str_replace('-', '', ucwords($withoutPrefix, '-')));
        }
        
        return $elementName;
    }
}
