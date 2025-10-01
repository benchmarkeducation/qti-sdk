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
use qtism\data\rules\ResponseElseIf;
use qtism\data\rules\ResponseIf;
use qtism\data\rules\ResponseRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use ReflectionClass;
use ReflectionException;

/**
 * A Marshaller used to marshall/unmarshall ResponseCondition components.
 */
class ResponseControlMarshaller extends RecursiveMarshaller
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
            $qti3Names = [];
            foreach ($expressionNames as $name) {
                $qti3Names[] = 'qti-' . $name;
            }
            $expressionNames = array_merge($expressionNames, $qti3Names);
        }
        
        $expressionElts = $this->getChildElementsByTagName($element, $expressionNames);
        $expression = null;

        if (count($expressionElts) > 0) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElts[0]);
            $expression = $marshaller->unmarshall($expressionElts[0]);
        } elseif ((in_array($element->localName, ['responseIf', 'qti-response-if', 'responseElseIf', 'qti-response-else-if'])) && count($expressionElts) == 0) {
            $msg = "A '" . $element->localName . "' must contain an 'expression' element. None found at line " . $element->getLineNo() . "'.";
            throw new UnmarshallingException($msg, $element);
        }

        $elementName = $element->localName;
        $mappedName = $this->mapElementName($elementName);
        
        if (in_array($elementName, ['responseIf', 'qti-response-if', 'responseElseIf', 'qti-response-else-if'])) {
            $className = 'qtism\\data\\rules\\' . ucfirst($mappedName);
            $class = new ReflectionClass($className);
            $object = Reflection::newInstance($class, [$expression, $children]);
        } else {
            $className = 'qtism\\data\\rules\\' . ucfirst($mappedName);
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
        $elementName = $this->getQti30ElementName($component);
        $element = $this->createElement($component, $elementName);

        if ($component instanceof ResponseIf || $component instanceof ResponseElseIf) {
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
            'exitResponse', 'qti-exit-response',
            'lookupOutcomeValue', 'qti-lookup-outcome-value', 
            'setOutcomeValue', 'qti-set-outcome-value'
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
        $tags = ($this->getVersion() === '3.0.0') ? [
            'qti-exit-response',
            'qti-lookup-outcome-value',
            'qti-set-outcome-value',
            'qti-response-condition',
        ] : [
            'exitResponse',
            'lookupOutcomeValue',
            'setOutcomeValue',
            'responseCondition',
        ];
        
        return $this->getChildElementsByTagName($element, $tags);
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component): array
    {
        return $component->getResponseRules()->getArrayCopy();
    }

    /**
     * @param DOMElement $currentNode
     * @return ResponseRuleCollection
     */
    protected function createCollection(DOMElement $currentNode): ResponseRuleCollection
    {
        return new ResponseRuleCollection();
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return '';
    }
    
    private function mapElementName(string $elementName): string
    {
        $mapping = [
            'qti-response-if' => 'responseIf',
            'qti-response-else-if' => 'responseElseIf', 
            'qti-response-else' => 'responseElse'
        ];
        
        return $mapping[$elementName] ?? $elementName;
    }
    
    private function getQti30ElementName(QtiComponent $component): string
    {
        if ($this->getVersion() !== '3.0.0') {
            return null;
        }
        
        $className = get_class($component);
        $shortName = substr($className, strrpos($className, '\\') + 1);
        
        $mapping = [
            'ResponseIf' => 'qti-response-if',
            'ResponseElseIf' => 'qti-response-else-if',
            'ResponseElse' => 'qti-response-else'
        ];
        
        return $mapping[$shortName] ?? null;
    }
}
