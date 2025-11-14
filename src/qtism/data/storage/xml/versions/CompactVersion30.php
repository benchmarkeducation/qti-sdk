<?php

namespace qtism\data\storage\xml\versions;

use qtism\data\storage\xml\marshalling\Qti30MarshallerFactory;

/**
 * QTI Compact version 3.0.0
 */
class CompactVersion30 extends QtiVersion
{
    public const XMLNS = 'http://www.imsglobal.org/xsd/imsqtiasi_v3p0';

    public function getMarshallerFactory(): Qti30MarshallerFactory
    {
        return new Qti30MarshallerFactory();
    }
}