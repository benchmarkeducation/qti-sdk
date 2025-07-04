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

namespace qtism\runtime\rendering\markup\xhtml;

use DOMDocumentFragment;
use qtism\data\QtiComponent;
use qtism\data\View;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 * RubricBlock renderer. Rendered components will be transformed as
 * 'div' elements with a 'qti-rubricBlock' additional CSS class.
 *
 * Moreover, if the view information will be added ass CSS additional classes.
 * For instance, if qti:rubricBlock->view = 'proctor candidate', the resulting
 * element will be '<div class="qti-rubricBlock qti-view-candidate qti-view-proctor>...</div>".
 *
 * More over, the following data-x attributes will be set:
 *
 * * data-view = qti:rubricBlock->view
 */
class RubricBlockRenderer extends BodyElementRenderer
{
    /**
     * Create a new RubricBlockRenderer object.
     *
     * @param AbstractMarkupRenderingEngine $renderingEngine
     */
    public function __construct(?AbstractMarkupRenderingEngine $renderingEngine = null)
    {
        parent::__construct($renderingEngine);
        $this->transform('div');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-rubricBlock');

        $dataView = [];

        if ($component->getViews()->contains(View::AUTHOR)) {
            $this->additionalUserClass('qti-view-author');
            $dataView[] = 'author';
        }

        if ($component->getViews()->contains(View::CANDIDATE)) {
            $this->additionalUserClass('qti-view-candidate');
            $dataView[] = 'candidate';
        }

        if ($component->getViews()->contains(View::PROCTOR)) {
            $this->additionalUserClass('qti-view-proctor');
            $dataView[] = 'proctor';
        }

        if ($component->getViews()->contains(View::SCORER)) {
            $this->additionalUserClass('qti-view-scorer');
            $dataView[] = 'scorer';
        }

        if ($component->getViews()->contains(View::TEST_CONSTRUCTOR)) {
            $this->additionalUserClass('qti-view-testConstructor');
            $dataView[] = 'testConstructor';
        }

        if ($component->getViews()->contains(View::TUTOR)) {
            $this->additionalUserClass('qti-view-tutor');
            $dataView[] = 'tutor';
        }

        $fragment->firstChild->setAttribute('data-view', implode("\x20", $dataView));
    }
}
