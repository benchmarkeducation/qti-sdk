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

namespace qtism\common\storage;

use Exception;

/**
 * The MemoryStreamException represents errors that might occur while
 * dealing with a MemoryStream object.
 */
class MemoryStreamException extends StreamException
{
    /**
     * Create a new MemoryStreamException.
     *
     * @param string $message The human-readable message describing the error.
     * @param IStream $source The IStream object where in the error occurred.
     * @param int $code A code describing the error.
     * @param Exception $previous An optional previous exception.
     */
    public function __construct($message, IStream $source, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $source, $code, $previous);
    }
}
