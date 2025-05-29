<?php

require 'vendor/autoload.php';

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\tests\AssessmentItemSession;

// Instantiate a new QTI XML document, and load a QTI XML document.
// $itemDoc = new XmlDocument('2.1');
// $itemDoc->load('/Users/kvalentine/Repositories/qti-sdk/item1.xml');

$itemDoc = new XmlDocument('3.0');
$itemDoc->load('/Users/kvalentine/Repositories/qti-sdk/item.xml');

/* 
 * A QTI XML document can be used to load various pieces of QTI content such as assessmentItem,
 * assessmentTest, responseProcessing, ... components. Our target is an assessmentItem, which is the
 * root component of our document.
 */
$item = $itemDoc->getDocumentComponent();

/* 
 * The item session represents the collected and computed data related to the interactions a
 * candidate performs on a single assessmentItem. As per the QTI specification, "an item session
 * is the accumulation of all attempts at a particular instance of an assessmentItem made by
 * a candidate.
 */
$itemSession = new AssessmentItemSession($item);

// The candidate is entering the item session, and is beginning his first attempt.
$itemSession->beginItemSession();
$itemSession->beginAttempt();

/* 
 * We instantiate the responses provided by the candidate for this assessmentItem, for the current
 * item session. For this assessmentItem, the data collected from the candidate is represented by 
 * a State, composed of a single ResponseVariable named 'RESPONSE'.
 */
$responses = new State(
    array(
        // The 'RESPONSE' ResponseVariable has a QTI multiple cardinality, and a QTI identifier baseType.
        new ResponseVariable(
            'RESPONSE',
            Cardinality::MULTIPLE,
            BaseType::IDENTIFIER,
            /* 
             * The ResponseVariable value is a Container with multiple cardinality and an identifier
             * baseType to meet the cardinality and baseType requirements of the ResponseVariable.
             */
            new MultipleContainer(
                BaseType::IDENTIFIER,
                /*
                 * The values composing the Container are identifiers 'H' and 'O', which represent
                 * the correct response to this item.
                 */
                array(
                    new QtiIdentifier('H'),
                    new QtiIdentifier('O')
                )
            )
        )
    )
);

/*
 * The candidate is finishing the current attempt, by providing a correct response.
 * ResponseProcessing takes place to produce a new value for the 'SCORE' OutcomeVariable.
 */
$itemSession->endAttempt($responses);

// The item session variables and their values can be accessed by their identifier.
echo 'numAttempts: ' . $itemSession['numAttempts'] . "\n";
echo 'completionStatus: ' . $itemSession['completionStatus'] . "\n";
echo 'RESPONSE: ' . $itemSession['RESPONSE'] . "\n";
echo 'SCORE: ' . $itemSession['SCORE'] . "\n";

/*
 * numAttempts: 1
 * completionStatus: completed
 * RESPONSE: ['H'; 'O']
 * SCORE: 2
 */

// End the current item session.
$itemSession->endItemSession();
