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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\enums\Cardinality;
use qtism\data\expressions\operators\Member;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\Utils as CommonUtils;

/**
 * The MemberProcessor class aims at processing Member operators.
 *
 * From IMS QTI:
 *
 * The member operator takes two sub-expressions which must both have the same base-type. The first sub-expression must
 * have single cardinality and the second must be a multiple or ordered container. The result is a single boolean with a
 * value of true if the value given by the first sub-expression is in the container defined by the second sub-expression.
 * If either sub-expression is NULL then the result of the operator is NULL.
 *
 * The member operator should not be used on sub-expressions with a base-type of float because of the poorly defined comparison of values.
 * It must not be used on sub-expressions with a base-type of duration.
 */
class MemberProcessor extends OperatorProcessor
{
    /**
     * Process the Member operator.
     *
     * @return QtiBoolean Whether the first operand is contained by the second one as a boolean value, or NULL if any of the sub-expressions are NULL.
     * @throws OperatorProcessingException
     */
    public function process(): ?QtiBoolean
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->sameBaseType() === false) {
            $msg = 'The Member operator only accepts values with the same baseType.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $operand1 = $operands[0];
        $operand2 = $operands[1];

        // The first expression must have single cardinality.
        if (CommonUtils::inferCardinality($operand1) !== Cardinality::SINGLE) {
            $msg = 'The first operand of the Member operator must have a single cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        // The second expression must have multiple or ordered cardinality.
        // Extended to single cardinality to be a little bit less greedy.
        $cardinality = CommonUtils::inferCardinality($operand2);
        if ($cardinality === Cardinality::SINGLE) {
            $operand2 = new MultipleContainer($operand1->getBaseType(), [$operand2]);
        } elseif ($cardinality !== Cardinality::MULTIPLE && $cardinality !== Cardinality::ORDERED) {
            $msg = 'The second operand of the Member operator must have a single, multiple or ordered cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        return new QtiBoolean($operand2->contains($operand1));
    }

    /**
     * @return string
     */
    protected function getExpressionType(): string
    {
        return Member::class;
    }
}
