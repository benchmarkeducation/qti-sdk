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
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\AndOperator;
use qtism\data\expressions\operators\CustomOperator;
use qtism\data\expressions\operators\MatchOperator;
use qtism\data\expressions\operators\NotOperator;
use qtism\data\expressions\operators\Operator;
use qtism\data\expressions\operators\OrOperator;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\storage\xml\Utils;
use ReflectionClass;
use ReflectionException;

/**
 * The OperatorMarshaller class focuses on Marshaller/Unmarshalling
 * the QTI Operators (a.k.a. hierarchical expressions).
 */
class OperatorMarshaller extends RecursiveMarshaller
{
    private static $operators = [
        'roundTo',
        'statsOperator',
        'max',
        'min',
        'mathOperator',
        'gcd',
        'lcm',
        'repeat',
        'multiple',
        'ordered',
        'containerSize',
        'isNull',
        'index',
        'fieldValue',
        'random',
        'member',
        'delete',
        'contains',
        'substring',
        'not',
        'and',
        'or',
        'anyN',
        'match',
        'stringMatch',
        'patternMatch',
        'equal',
        'equalRounded',
        'inside',
        'lt',
        'gt',
        'lte',
        'gte',
        'durationLT',
        'durationGTE',
        'sum',
        'product',
        'subtract',
        'divide',
        'power',
        'integerDivide',
        'integerModulus',
        'truncate',
        'round',
        'integerToFloat',
        'customOperator',
    ];

    private static $expressions = [
        'baseValue',
        'variable',
        'default',
        'correct',
        'mapResponse',
        'mapResponsePoint',
        'mathConstant',
        'null',
        'randomInteger',
        'randomFloat',
        'testVariables',
        'outcomeMaximum',
        'outcomeMinimum',
        'numberCorrect',
        'numberIncorrect',
        'numberResponded',
        'numberPresented',
        'numberSelected',
    ];

    /**
     * Get the list of operator QTI class names.
     *
     * @return array An array of string.
     */
    public static function getOperators(): array
    {
        return self::$operators;
    }

    /**
     * Get the list of expression QTI class names.
     *
     * @return array An array of string.
     */
    public static function getExpressions(): array
    {
        return self::$expressions;
    }

    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws ReflectionException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        // Some exceptions applies on instanciation e.g. the And operator is named
        // AndOperator because of PHP reserved words restriction.

        $elementName = $this->mapQti30ElementName($element->localName);
        
        if ($elementName === 'and') {
            $className = AndOperator::class;
        } elseif ($elementName === 'or') {
            $className = OrOperator::class;
        } elseif ($elementName === 'not') {
            $className = NotOperator::class;
        } elseif ($elementName === 'match') {
            $className = MatchOperator::class;
        } else {
            $className = 'qtism\\data\\expressions\\operators\\' . ucfirst($elementName);
        }

        $class = new ReflectionClass($className);
        $params = [$children];

        if ($elementName === 'customOperator') {
            // Retrieve XML content as a string.
            $frag = $element->ownerDocument->createDocumentFragment();
            $element = $element->cloneNode(true);
            $frag->appendChild($element);
            $params[] = $frag->ownerDocument->saveXML($frag);
            $component = Reflection::newInstance($class, $params);

            if (($class = $this->getDOMElementAttributeAs($element, 'class')) !== null) {
                $component->setClass($class);
            }

            if (($definition = $this->getDOMElementAttributeAs($element, 'definition')) !== null) {
                $component->setDefinition($definition);
            }

            return $component;
        } else {
            return Reflection::newInstance($class, $params);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);
        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        if ($component instanceof CustomOperator) {
            if ($component->hasClass() === true) {
                $this->setDOMElementAttribute($element, 'class', $component->getClass());
            }

            if ($component->hasDefinition() === true) {
                $this->setDOMElementAttribute($element, 'definition', $component->getDefinition());
            }

            // Now, we have to extract the LAX content of the custom operator and put it into
            // what we are putting out. (It is possible to have no LAX content at all, it is not mandatory).
            $xml = $component->getXml();
            $operatorElt = $xml->documentElement->cloneNode(true);
            $qtiOperatorElts = $this->getChildElementsByTagName($operatorElt, array_merge(self::getOperators(), self::getExpressions()));

            foreach ($qtiOperatorElts as $qtiOperatorElt) {
                $operatorElt->removeChild($qtiOperatorElt);
            }

            Utils::importChildNodes($operatorElt, $element);
            Utils::importAttributes($operatorElt, $element);
        }

        return $element;
    }

    /**
     * @param DOMNode $element
     * @return bool
     */
    protected function isElementFinal(DOMNode $element): bool
    {
        $elementName = $this->mapQti30ElementName($element->localName);
        return !in_array($elementName, static::getOperators());
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component): bool
    {
        return !$component instanceof Operator;
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element): array
    {
        $qti2Names = array_merge(self::getOperators(), self::getExpressions());
        // Map specific camelCase to kebab-case for QTI 3.0 elements that use kebab-case
        $kebabCaseMap = [
            'isNull' => 'is-null',
            'mapResponse' => 'map-response',
            'baseValue' => 'base-value',
            'setOutcomeValue' => 'set-outcome-value'
        ];
        
        $qti3Names = array_map(function($name) use ($kebabCaseMap) { 
            $kebabName = isset($kebabCaseMap[$name]) ? $kebabCaseMap[$name] : $name;
            return 'qti-' . $kebabName;
        }, $qti2Names);
        return $this->getChildElementsByTagName($element, array_merge($qti2Names, $qti3Names));
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component): array
    {
        if ($component instanceof Operator) {
            return $component->getExpressions()->getArrayCopy();
        } else {
            return [];
        }
    }

    /**
     * @param DOMElement $currentNode
     * @return ExpressionCollection
     */
    protected function createCollection(DOMElement $currentNode): ExpressionCollection
    {
        return new ExpressionCollection();
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return '';
    }
    
    private function mapQti30ElementName(string $elementName): string
    {
        if (strpos($elementName, 'qti-') === 0) {
            $withoutPrefix = substr($elementName, 4); // Remove 'qti-' prefix
            // Convert kebab-case back to camelCase
            return lcfirst(str_replace('-', '', ucwords($withoutPrefix, '-')));
        }
        return $elementName;
    }
}
