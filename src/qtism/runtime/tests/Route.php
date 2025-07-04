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

namespace qtism\runtime\tests;

use InvalidArgumentException;
use Iterator;
use OutOfBoundsException;
use OutOfRangeException;
use qtism\common\collections\IdentifierCollection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\NavigationMode;
use qtism\data\SubmissionMode;
use qtism\data\TestPart;
use qtism\runtime\common\VariableIdentifier;
use SplObjectStorage;

/**
 * The Route class represents a linear route to be taken accross a given
 * selection of AssessmentItemRef objects.
 *
 * A Route object is composed of RouteItem objects which are all composed
 * of three components:
 *
 * * An AssessmentItemRef object.
 * * An AssessmentSection object, which is the parent section of the AssessmentItemRef.
 * * A TestPart object, which is the parent object (direct or indirect) of the AssessmentSection.
 */
class Route implements Iterator
{
    /**
     * A collection that gathers all assessmentItemRefs
     * involved in the route.
     *
     * @var AssessmentItemRefCollection
     */
    private $assessmentItemRefs;

    /**
     * A map where items are gathered by category.
     *
     * @var array
     */
    private $assessmentItemRefCategoryMap;

    /**
     * A map where items are gathered by section identifier.
     *
     * @var array
     */
    private $assessmentItemRefSectionMap;

    /**
     * A map where each item is bound to a number of occurences.
     *
     * @var SplObjectStorage
     */
    private $assessmentItemRefOccurenceCount;

    /**
     * A map where each RouteItem is bound to a test part.
     *
     * @var SplObjectStorage
     */
    private $testPartMap;

    /**
     * A map where each RouteItem is bound to a test part identifier.
     *
     * @var array
     */
    private $testPartIdentifierMap;

    /**
     * A map where each RouteItem is bound to an assessment section.
     *
     * @var SplObjectStorage
     */
    private $assessmentSectionMap;

    /**
     * A map where each RouteItem is bound to an assessment section identifier.
     *
     * @var array
     */
    private $assessmentSectionIdentifierMap;

    /**
     * A map where each RouteItem is bound to an assessmentItemRef.
     *
     * @var SplObjectStorage
     */
    private $assessmentItemRefMap;

    /**
     * The RouteItem objects the Route is composed with.
     *
     * @var array
     */
    private $routeItems = [];

    /**
     * The current position in the route.
     *
     * @var int
     */
    private $position = 0;

    /**
     * A collection of identifier representing all the item categories
     * involved in the route.
     *
     * @var IdentifierCollection
     */
    private $categories;

    /**
     * Create a new Route object.
     */
    public function __construct()
    {
        $this->setPosition(0);
        $this->setAssessmentItemRefs(new AssessmentItemRefCollection());
        $this->setAssessmentItemRefCategoryMap([]);
        $this->setAssessmentItemRefSectionMap([]);
        $this->setAssessmentItemRefOccurenceMap(new SplObjectStorage());
        $this->setCategories(new IdentifierCollection());
        $this->setTestPartMap(new SplObjectStorage());
        $this->setAssessmentSectionMap(new SplObjectStorage());
        $this->setTestPartIdentifierMap([]);
        $this->setAssessmentSectionIdentifierMap([]);
        $this->setAssessmentItemRefMap(new SplObjectStorage());
    }

    /**
     * Get the current index position.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Set the current index position.
     *
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * Get a reference on the RouteItem objects contained
     * within this Route object.
     *
     * @return array
     */
    protected function &getRouteItems(): array
    {
        return $this->routeItems;
    }

    /**
     * Get the collection of AssessmentItemRef objects
     * that are involded in the route.
     *
     * @return AssessmentItemRefCollection A collection of AssessmentItemRef objects.
     */
    public function getAssessmentItemRefs(): AssessmentItemRefCollection
    {
        return $this->assessmentItemRefs;
    }

    /**
     * Set the collection of AssessmentItemRef objects that are involved
     * in this route.
     *
     * @param AssessmentItemRefCollection $assessmentItemRefs A collection of AssessmentItemRefObjects.
     */
    public function setAssessmentItemRefs(AssessmentItemRefCollection $assessmentItemRefs): void
    {
        $this->assessmentItemRefs = $assessmentItemRefs;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are
     * stored by category.
     *
     * @return array A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same category.
     */
    protected function getAssessmentItemRefCategoryMap(): array
    {
        return $this->assessmentItemRefCategoryMap;
    }

    /**
     * Set the map where AssessmentItemRef objects involved in the route are stored
     * by category.
     *
     * @param array $assessmentItemRefCategoryMap A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef object of the same category.
     */
    protected function setAssessmentItemRefCategoryMap(array $assessmentItemRefCategoryMap): void
    {
        $this->assessmentItemRefCategoryMap = $assessmentItemRefCategoryMap;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * by section.
     *
     * @return array A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same section.
     */
    protected function getAssessmentItemRefSectionMap(): array
    {
        return $this->assessmentItemRefSectionMap;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * by section.
     *
     * @param array $assessmentItemRefSectionMap A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same section.
     */
    protected function setAssessmentItemRefSectionMap(array $assessmentItemRefSectionMap): void
    {
        $this->assessmentItemRefSectionMap = $assessmentItemRefSectionMap;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * with a number of occurence.
     *
     * @return SplObjectStorage
     */
    protected function getAssessmentItemRefOccurenceMap(): SplObjectStorage
    {
        return $this->assessmentItemRefOccurenceCount;
    }

    /**
     * Set the map where AssessmentItemRef objects involved in the route are stored
     * with a number of occurence.
     *
     * @param SplObjectStorage $assessmentItemRefOccurenceCount
     */
    protected function setAssessmentItemRefOccurenceMap(SplObjectStorage $assessmentItemRefOccurenceCount): void
    {
        $this->assessmentItemRefOccurenceCount = $assessmentItemRefOccurenceCount;
    }

    /**
     * Set the map where RouteItem objects are gathered by TestPart.
     *
     * @param SplObjectStorage $testPartMap
     */
    protected function setTestPartMap(SplObjectStorage $testPartMap): void
    {
        $this->testPartMap = $testPartMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by TestPart.
     *
     * @return SplObjectStorage
     */
    protected function getTestPartMap(): SplObjectStorage
    {
        return $this->testPartMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by TestPart identifier.
     *
     * @param array $testPartIdentifierMap
     */
    protected function setTestPartIdentifierMap(array $testPartIdentifierMap): void
    {
        $this->testPartIdentifierMap = $testPartIdentifierMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by TestPart identifier.
     *
     * @return array
     */
    protected function getTestPartIdentifierMap(): array
    {
        return $this->testPartIdentifierMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by AssessmentSection.
     *
     * @param SplObjectStorage $assessmentSectionMap
     */
    protected function setAssessmentSectionMap(SplObjectStorage $assessmentSectionMap): void
    {
        $this->assessmentSectionMap = $assessmentSectionMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by AssessmentSection.
     *
     * @return SplObjectStorage
     */
    protected function getAssessmentSectionMap(): SplObjectStorage
    {
        return $this->assessmentSectionMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by AssessmentSection identifier.
     *
     * @param array $assessmentSectionIdentifierMap
     */
    protected function setAssessmentSectionIdentifierMap(array $assessmentSectionIdentifierMap): void
    {
        $this->assessmentSectionIdentifierMap = $assessmentSectionIdentifierMap;
    }

    /**
     * Get the map where RouteItems objects are gathered by AssessmentSection identifier.
     *
     * @return array
     */
    protected function getAssessmentSectionIdentifierMap(): array
    {
        return $this->assessmentSectionIdentifierMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by AssessmentItemRef objects.
     *
     * @param SplObjectStorage $assessmentItemRefMap
     */
    protected function setAssessmentItemRefMap(SplObjectStorage $assessmentItemRefMap): void
    {
        $this->assessmentItemRefMap = $assessmentItemRefMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by AssessmentItemRef objects.
     *
     * @return SplObjectStorage
     */
    protected function getAssessmentItemRefMap(): SplObjectStorage
    {
        return $this->assessmentItemRefMap;
    }

    /**
     * Set the collection of item categories involved in the route.
     *
     * @param IdentifierCollection $categories A collection of QTI Identifiers.
     */
    protected function setCategories(IdentifierCollection $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * Get the collection of item categories involved in the route.
     *
     * @return IdentifierCollection A collection of QTI Identifiers.
     */
    public function getCategories(): IdentifierCollection
    {
        return $this->categories;
    }

    /**
     * Add a new RouteItem object at the end of the Route.
     *
     * @param AssessmentItemRef $assessmentItemRef
     * @param AssessmentSection|AssessmentSectionCollection $assessmentSections
     * @param TestPart $testPart
     * @param AssessmentTest $assessmentTest
     */
    public function addRouteItem(AssessmentItemRef $assessmentItemRef, $assessmentSections, TestPart $testPart, AssessmentTest $assessmentTest): void
    {
        // Push the routeItem in the track :) !
        $routeItem = new RouteItem($assessmentItemRef, $assessmentSections, $testPart, $assessmentTest);
        $this->registerAssessmentItemRef($routeItem);
        $this->registerTestPart($routeItem);
        $this->registerAssessmentSection($routeItem);
    }

    /**
     * Add a new RouteItem object at the end of the Route.
     *
     * @param RouteItem $routeItem A RouteItemObject.
     */
    public function addRouteItemObject(RouteItem $routeItem): void
    {
        $this->registerAssessmentItemRef($routeItem);
        $this->registerTestPart($routeItem);
        $this->registerAssessmentSection($routeItem);
    }

    public function rewind(): void
    {
        $this->setPosition(0);
    }

    /**
     * Get the current RouteItem object.
     *
     * @return RouteItem A RouteItem object.
     */
    public function current(): RouteItem
    {
        $routeItems = &$this->getRouteItems();
        $position = $this->getPosition();
        if (!isset($routeItems[$position])) {
            throw new OutOfBoundsException('No RouteItem object found at position ' . $position);
        }

        return $routeItems[$position];
    }

    /**
     * Get the current key corresponding to the current RouteItem object.
     *
     * @return int The returned key is the position of the current RouteItem object in the Route.
     */
    public function key(): int
    {
        return $this->getPosition();
    }

    /**
     * Set the Route as its previous position in the RouteItem sequence. If the current
     * RouteItem is the first one prior to call next(), the Route remains in the same position.
     */
    public function previous(): void
    {
        $position = $this->getPosition();
        if ($position > 0) {
            $this->setPosition(--$position);
        }
    }

    /**
     * Set the Route as its next position in the RouteItem sequence. If the current
     * RouteItem is the last one prior to call next(), the iterator becomes invalid.
     */
    public function next(): void
    {
        $this->setPosition($this->getPosition() + 1);
    }

    /**
     * Whether the Route is still valid while iterating.
     *
     * @return bool
     */
    public function valid(): bool
    {
        $routeItems = &$this->getRouteItems();

        return isset($routeItems[$this->getPosition()]);
    }

    /**
     * Whether the current RouteItem is the last of the route.
     *
     * @return bool
     */
    public function isLast(): bool
    {
        $nextPosition = $this->getPosition() + 1;
        $routeItems = &$this->getRouteItems();

        return !isset($routeItems[$nextPosition]);
    }

    /**
     * Whether the current RouteItem is the first of the route.
     *
     * @return bool
     */
    public function isFirst(): bool
    {
        return $this->getPosition() === 0;
    }

    /**
     * Whether the current RouteItem in the route is in linear
     * navigation mode.
     *
     * @return bool
     */
    public function isNavigationLinear(): bool
    {
        return $this->current()->getTestPart()->getNavigationMode() === NavigationMode::LINEAR;
    }

    /**
     * Whether the current RouteItem in the route is in non-linear
     * navigation mode.
     *
     * @return bool
     */
    public function isNavigationNonLinear(): bool
    {
        return !$this->isNavigationLinear();
    }

    /**
     * Whether the current RouteItem in the route is in individual
     * submission mode.
     *
     * @return bool
     */
    public function isSubmissionIndividual(): bool
    {
        return $this->current()->getTestPart()->getSubmissionMode() === SubmissionMode::INDIVIDUAL;
    }

    /**
     * Whether the current RouteItem in the route is in simultaneous
     * submission mode.
     *
     * @return bool
     */
    public function isSubmissionSimultaneous(): bool
    {
        return !$this->isSubmissionIndividual();
    }

    /**
     * Append all the RouteItem objects contained in $route
     * to this Route.
     *
     * @param Route $route A Route object.
     */
    public function appendRoute(Route $route): void
    {
        foreach ($route as $routeItem) {
            // @todo find why it must be cloned, I can't remember.
            $clone = clone $routeItem;

            $this->registerAssessmentItemRef($clone);
            $this->registerTestPart($clone);
            $this->registerAssessmentSection($clone);
        }
    }

    /**
     * For more convience, the processing related to the AssessmentItemRef object contained
     * in a newly added RouteItem object is gathered in this method. The following process
     * will occur:
     *
     * * The RouteItem object is inserted in the RouteItem array for storage.
     * * The assessmentItemRef is added to the occurence map.
     * * The assessmentItemRef is added to the category map.
     * * The assessmentItemRef is added to the section map.
     *
     * @param RouteItem $routeItem
     */
    protected function registerAssessmentItemRef(RouteItem $routeItem): void
    {
        array_push($this->routeItems, $routeItem);

        // For more convenience ;)
        $assessmentItemRef = $routeItem->getAssessmentItemRef();

        // Count the number of occurences for the assessmentItemRef.
        if (isset($this->assessmentItemRefOccurenceCount[$assessmentItemRef]) === false) {
            $this->assessmentItemRefOccurenceCount[$assessmentItemRef] = 0;
        }

        $this->assessmentItemRefOccurenceCount[$assessmentItemRef] += 1;
        $routeItem->setOccurence($this->assessmentItemRefOccurenceCount[$assessmentItemRef] - 1);

        // Reference the assessmentItemRef object of the RouteItem
        // for a later use.
        $this->assessmentItemRefs->attach($assessmentItemRef);

        // Reference the assessmentItemRef object of the RouteItem
        // by category for a later use.
        foreach ($assessmentItemRef->getCategories() as $category) {
            if (isset($this->assessmentItemRefCategoryMap[$category]) === false) {
                $this->assessmentItemRefCategoryMap[$category] = new AssessmentItemRefCollection();
            }
            $this->assessmentItemRefCategoryMap[$category][] = $assessmentItemRef;

            if ($this->categories->contains($category) === false) {
                $this->categories[] = $category;
            }
        }

        // Reference the AssessmentItemRef object of the RouteItem
        // by section for a later use.
        foreach ($routeItem->getAssessmentSections() as $s) {
            $assessmentSectionIdentifier = $s->getIdentifier();
            if (isset($this->assessmentItemRefSectionMap[$assessmentSectionIdentifier]) === false) {
                $this->assessmentItemRefSectionMap[$assessmentSectionIdentifier] = new AssessmentItemRefCollection();
            }
            $this->assessmentItemRefSectionMap[$assessmentSectionIdentifier][] = $assessmentItemRef;
        }

        // Reference the AssessmentItemRef by routeItem.
        if (isset($this->assessmentItemRefMap[$assessmentItemRef]) === false) {
            $this->assessmentItemRefMap[$assessmentItemRef] = new RouteItemCollection();
        }
        $this->assessmentItemRefMap[$assessmentItemRef][] = $routeItem;
    }

    /**
     * Register all needed information about the TestPart involved in a given
     * $routeItem.
     *
     * @param RouteItem $routeItem A RouteItem object.
     */
    protected function registerTestPart(RouteItem $routeItem): void
    {
        // Register the RouteItem in the testPartMap.
        $testPart = $routeItem->getTestPart();

        if (isset($this->testPartMap[$testPart]) === false) {
            $this->testPartMap[$testPart] = [];
        }

        $target = $this->testPartMap[$testPart];
        $target[] = $routeItem;
        $this->testPartMap[$testPart] = $target;

        // Register the RouteItem in the testPartIdentifierMap.
        $id = $testPart->getIdentifier();

        if (isset($this->testPartIdentifierMap[$id]) === false) {
            $this->testPartIdentifierMap[$id] = [];
        }

        $this->testPartIdentifierMap[$id][] = $routeItem;
    }

    /**
     * Register all needed information about the AssessmentSection involved in a given
     * $routeItem.
     *
     * @param RouteItem $routeItem A RouteItem object.
     */
    protected function registerAssessmentSection(RouteItem $routeItem): void
    {
        foreach ($routeItem->getAssessmentSections() as $assessmentSection) {
            if (isset($this->assessmentSectionMap[$assessmentSection]) === false) {
                $this->assessmentSectionMap[$assessmentSection] = [];
            }

            $target = $this->assessmentSectionMap[$assessmentSection];
            $target[] = $routeItem;
            $this->assessmentSectionMap[$assessmentSection] = $target;

            // Register the RouteItem in the assessmentSectionIdentifierMap.
            $id = $assessmentSection->getIdentifier();

            if (isset($this->assessmentSectionIdentifierMap[$id]) === false) {
                $assessmentSectionIdentifierMap[$id] = [];
            }

            $this->assessmentSectionIdentifierMap[$id][] = $routeItem;
        }
    }

    /**
     * Get the sequence of identifiers formed by the identifiers of each
     * assessmentItemRef object of the route, in the order they must be taken.
     *
     * @param bool $withSequenceNumber Whether to return the sequence number in the identifier or not.
     * @return IdentifierCollection
     */
    public function getIdentifierSequence($withSequenceNumber = true): IdentifierCollection
    {
        $routeItems = &$this->getRouteItems();
        $collection = new IdentifierCollection();

        foreach (array_keys($routeItems) as $k) {
            $virginIdentifier = $routeItems[$k]->getAssessmentItemRef()->getIdentifier();
            $collection[] = ($withSequenceNumber === true) ? $virginIdentifier . '.' . ($routeItems[$k]->getOccurence() + 1) : $virginIdentifier;
        }

        return $collection;
    }

    /**
     * Get the AssessmentItemRef objects involved in the route that belong
     * to a given $category.
     *
     * If no AssessmentItemRef involved in the route are found for the given $category,
     * the return AssessmentItemRefCollection is empty.
     *
     * @param string|IdentifierCollection $category A category identifier.
     * @return AssessmentItemRefCollection An collection of AssessmentItemRefCollection that belong to $category.
     */
    public function getAssessmentItemRefsByCategory($category): AssessmentItemRefCollection
    {
        $categoryMap = $this->getAssessmentItemRefCategoryMap();
        $categories = (is_string($category)) ? [$category] : $category->getArrayCopy();

        $result = new AssessmentItemRefCollection();

        foreach ($categories as $cat) {
            if (isset($categoryMap[$cat])) {
                foreach ($categoryMap[$cat] as $item) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * Get a subset of AssessmentItemRef objects by $sectionIdentifier. If no items are matching $sectionIdentifier,
     * an empty collection is returned.
     *
     * @param string $sectionIdentifier A section identifier.
     * @return AssessmentItemRefCollection A Collection of AssessmentItemRef objects that belong to the section $sectionIdentifier.
     */
    public function getAssessmentItemRefsBySection($sectionIdentifier): AssessmentItemRefCollection
    {
        $sectionMap = $this->getAssessmentItemRefSectionMap();

        return $sectionMap[$sectionIdentifier] ?? new AssessmentItemRefCollection();
    }

    /**
     * Get a subset of AssessmentItemRef objects. The criterias are the $sectionIdentifier
     * and categories to be included/excluded.
     *
     * @param string $sectionIdentifier The identifier of the section.
     * @param IdentifierCollection $includeCategories A collection of category identifiers to be included in the selection.
     * @param IdentifierCollection $excludeCategories A collection of category identifiers to be excluded from the selection.
     * @return AssessmentItemRefCollection A collection of filtered AssessmentItemRef objects.
     */
    public function getAssessmentItemRefsSubset($sectionIdentifier = '', ?IdentifierCollection $includeCategories = null, ?IdentifierCollection $excludeCategories = null): AssessmentItemRefCollection
    {
        $bySection = (empty($sectionIdentifier)) ? $this->getAssessmentItemRefs() : $this->getAssessmentItemRefsBySection($sectionIdentifier);

        if ($includeCategories !== null) {
            // We will perform the search by category inclusion.
            return $bySection->intersect($this->getAssessmentItemRefsByCategory($includeCategories));
        } elseif ($excludeCategories !== null) {
            // Perform the category by exclusion.
            return $bySection->diff($this->getAssessmentItemRefsByCategory($excludeCategories));
        } else {
            return $bySection;
        }
    }

    /**
     * Get the number of occurences found in the route for the given $assessmentItemRef.
     * If $assessmentItemRef is not involved in the route, the returned result is 0.
     *
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     * @return int The number of occurences found in the route for $assessmentItemRef.
     */
    public function getOccurenceCount(AssessmentItemRef $assessmentItemRef): int
    {
        $occurenceMap = $this->getAssessmentItemRefOccurenceMap();
        return $occurenceMap[$assessmentItemRef] ?? 0;
    }

    /**
     * Get the number of RoutItem objects held by the Route.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->getRouteItems());
    }

    /**
     * Get a RouteItem object at $position in the route sequence. Please be careful that the route sequence index
     * begins at 0. In other words, the first route item in the sequence will be found at position 0, the second
     * at position 1, ...
     *
     * @param int $position The position of the requested RouteItem object in the route sequence.
     * @return RouteItem The RouteItem found at $position.
     * @throws OutOfBoundsException If no RouteItem is found at $position.
     */
    public function getRouteItemAt($position): RouteItem
    {
        $routeItems = &$this->getRouteItems();

        if (isset($routeItems[$position])) {
            return $routeItems[$position];
        } else {
            $msg = "No RouteItem object found at position '{$position}'.";
            throw new OutOfBoundsException($msg);
        }
    }

    /**
     * Get the last RouteItem object composing the Route.
     *
     * @return RouteItem The last RouteItem of the Route.
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function getLastRouteItem(): RouteItem
    {
        $routeItems = &$this->getRouteItems();
        $routeItemsCount = count($routeItems);

        if ($routeItemsCount === 0) {
            $msg = 'Cannot get the last RouteItem of the Route while it is empty.';
            throw new OutOfBoundsException($msg);
        }

        return $routeItems[$routeItemsCount - 1];
    }

    /**
     * Get the first RouteItem object composing the Route.
     *
     * @return RouteItem The first RouteItem of the Route.
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function getFirstRouteItem(): RouteItem
    {
        $routeItems = &$this->getRouteItems();
        $routeItemsCount = count($routeItems);

        if ($routeItemsCount === 0) {
            $msg = 'Cannot get the first RouteItem of the Route while it is empty.';
            throw new OutOfBoundsException($msg);
        }

        return $routeItems[0];
    }

    /**
     * Whether the current RouteItem is the last of the current TestPart.
     *
     * @return bool
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function isLastOfTestPart(): bool
    {
        $count = $this->count();
        if ($count === 0) {
            $msg = 'Cannot determine if the current RouteItem is the last of its TestPart when the Route is empty.';
            throw new OutOfBoundsException($msg);
        }

        $nextPosition = $this->getPosition() + 1;
        if ($nextPosition >= $count) {
            // This is the last routeitem of the whole route.
            return true;
        } else {
            $currentTestPart = $this->current()->getTestPart();
            $nextTestPart = $this->getRouteItemAt($nextPosition)->getTestPart();

            return $currentTestPart !== $nextTestPart;
        }
    }

    /**
     * Whether the current RouteItem is the first of the current TestPart.
     *
     * @return bool
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function isFirstOfTestPart(): bool
    {
        $count = $this->count();
        if ($count === 0) {
            $msg = 'Cannot determine if the current RouteItem is the first of its TestPart when the Route is empty.';
            throw new OutOfBoundsException($msg);
        }

        $previousPosition = $this->getPosition() - 1;
        if ($previousPosition === -1) {
            // This is the very first RouteItem of the whole Route.
            return true;
        } elseif ($this->getRouteItemAt($previousPosition)->getTestPart() !== $this->current()->getTestPart()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the previous RouteItem in the route.
     *
     * @return RouteItem The previous RouteItem in the Route.
     * @throws OutOfBoundsException If there is no previous RouteItem in the route. In other words, the current RouteItem in the route is the first one of the sequence.
     */
    public function getPrevious(): RouteItem
    {
        $currentPosition = $this->getPosition();
        if ($currentPosition === 0) {
            $msg = 'The current RouteItem is the first one in the route. There is no previous RouteItem';
            throw new OutOfBoundsException($msg);
        }

        return $this->getRouteItemAt($currentPosition - 1);
    }

    /**
     * Get the next RouteItem in the route.
     *
     * @return RouteItem The previous RouteItem in the Route.
     * @throws OutOfBoundsException If there is no next RouteItem in the route. In other words, the current RouteItem in the route is the last one of the sequence.
     */
    public function getNext(): RouteItem
    {
        if ($this->isLast() === true) {
            $msg = 'The current RouteItem is the last one in the route. There is no next RouteItem.';
            throw new OutOfBoundsException($msg);
        }

        return $this->getRouteItemAt($this->getPosition() + 1);
    }

    /**
     * Whether the RouteItem at $position in the Route is in the given $testPart.
     *
     * @param int $position A position in the Route sequence.
     * @param TestPart $testPart A TestPart object involved in the Route.
     * @return bool
     * @throws OutOfBoundsException If $position is out of the Route bounds.
     */
    public function isInTestPart($position, TestPart $testPart): bool
    {
        try {
            $routeItem = $this->getRouteItemAt($position);

            return $routeItem->getTestPart() === $testPart;
        } catch (OutOfBoundsException $e) {
            // The position does not refer to any RouteItem. This is out of the bounds of the route.
            $msg = "The position '{$position}' is out of the bounds of the route.";
            throw new OutOfBoundsException($msg, 0, $e);
        }
    }

    /**
     * Get the RouteItem objects involved in the current TestPart.
     *
     * @return RouteItemCollection A collection of RouteItem objects involved in the current TestPart.
     */
    public function getCurrentTestPartRouteItems(): RouteItemCollection
    {
        return $this->getRouteItemsByTestPart($this->current()->getTestPart());
    }

    /**
     * Get the RouteItem objects involved in a given test part.
     *
     * @param string|TestPart An identifier or a TestPart object.
     * @return RouteItemCollection A collection of RouteItem objects involved in the current TestPart.
     * @throws OutOfBoundsException If $testPart is not referenced in the Route.
     * @throws OutOfRangeException If $testPart is not a string nor a TestPart object.
     */
    public function getRouteItemsByTestPart($testPart): RouteItemCollection
    {
        if (is_string($testPart)) {
            $map = $this->getTestPartIdentifierMap();

            if (isset($map[$testPart]) === false) {
                $msg = "No testPart with identifier '{$testPart}' is referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$testPart]);
        } elseif ($testPart instanceof TestPart) {
            $map = $this->getTestPartMap();

            if (isset($map[$testPart]) === false) {
                $msg = "The testPart '" . $testPart->getIdentifier() . "' is not referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$testPart]);
        } else {
            $msg = "The 'testPart' argument must be a string or a TestPart object.";
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * Get the RouteItem objects involved in a given AssessmentSection.
     *
     * @param string|AssessmentSection $assessmentSection An AssessmentSection object or an identifier.
     * @return RouteItemCollection A collection of RouteItem objects involved in $assessmentSection.
     * @throws OutOfBoundsException If $assessmentSection is not referenced in the Route.
     * @throws OutOfRangeException If $assessmentSection is not a string nor an AssessmentSection object.
     */
    public function getRouteItemsByAssessmentSection($assessmentSection): RouteItemCollection
    {
        if (is_string($assessmentSection)) {
            $map = $this->getAssessmentSectionIdentifierMap();

            if (isset($map[$assessmentSection]) === false) {
                $msg = "No assessmentSection with identifier '{$assessmentSection}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$assessmentSection]);
        } elseif ($assessmentSection instanceof AssessmentSection) {
            $map = $this->getAssessmentSectionMap();
            $routeItems = new RouteItemCollection();

            if (isset($map[$assessmentSection]) === false) {
                $msg = "The assessmentSection '" . $assessmentSection->getIdentifier() . "' is not referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$assessmentSection]);
        } else {
            $msg = "The 'assessmentSection' argument must be a string or an AssessmentSection object.";
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * Get the RouteItem object involved in a given AssessmentItemRef.
     *
     * @param string|AssessmentItemRef $assessmentItemRef An AssessmentItemRef object or an identifier.
     * @return RouteItemCollection A collection of RouteItem objects involved in $assessmentItemRef.
     * @throws OutOfRangeException If $assessmentItemRef is not a string nor an AssessmentItemRef object.
     * @throws OutOfBoundsException If $assessmentItemRef is not referenced in the Route.
     */
    public function getRouteItemsByAssessmentItemRef($assessmentItemRef): RouteItemCollection
    {
        if (is_string($assessmentItemRef)) {
            if (($ref = $this->assessmentItemRefs[$assessmentItemRef]) !== null) {
                return $this->assessmentItemRefMap[$ref];
            } else {
                $msg = "No AssessmentItemRef with identifier '{$assessmentItemRef}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }
        } elseif ($assessmentItemRef instanceof AssessmentItemRef) {
            if (isset($this->assessmentItemRefMap[$assessmentItemRef]) === true) {
                return $this->assessmentItemRefMap[$assessmentItemRef];
            } else {
                $msg = "No AssessmentItemRef with 'identifier' {$assessmentItemRef}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }
        } else {
            $msg = "The 'assessmentItemRef' argument must be a string or an AssessmentItemRef object.";
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * Get all the RouteItem objects composing the Route.
     *
     * @return RouteItemCollection A collection of RouteItem objects.
     */
    public function getAllRouteItems(): RouteItemCollection
    {
        return new RouteItemCollection($this->getRouteItems());
    }

    /**
     * Perform a branching on a TestPart, AssessmentSection or AssessmentItemRef with
     * the given $identifier.
     *
     * The target will be considered invalid if the following constraints are not fullfilled:
     *
     * From IMS QTI:
     * In the case of an item or section, the target must refer to an item or section in the same
     * testPart that has not yet been presented. For testParts, the target must refer to another testPart.
     *
     * @param string $identifier A QTI Identifier to be the target of the branching.
     * @throws OutOfBoundsException If an error occurs while branching e.g. the $identifier is not referenced in the route or the target is invalid.
     * @throws OutOfRangeException If $identifier is not a valid branching identifier.
     */
    public function branch($identifier): void
    {
        try {
            $identifier = new VariableIdentifier($identifier);

            $id = ($identifier->hasPrefix() === false) ? $identifier->getVariableName() : $identifier->getPrefix();
            $occurence = ($identifier->hasPrefix() === false) ? 0 : ($identifier->getVariableName() - 1);
        } catch (InvalidArgumentException $e) {
            $msg = "The given identifier '{$identifier}' is an invalid branching target.";
            throw new OutOfRangeException($msg);
        }

        // Check for an assessmentItemRef.
        $assessmentItemRefs = $this->getAssessmentItemRefs();
        if (isset($assessmentItemRefs[$id])) {
            $assessmentItemRefMap = $this->getAssessmentItemRefMap();
            $targetRouteItems = $assessmentItemRefMap[$assessmentItemRefs[$id]];

            if ($targetRouteItems[$occurence]->getTestPart() !== $this->current()->getTestPart()) {
                // From IMS QTI:
                // In the case of an item or section, the target must refer to an item or section in the same test-part
                // that has not yet been presented.
                $this->next();

                return;
            }

            $this->setPosition($this->getRouteItemPosition($targetRouteItems[$occurence]));

            return;
        }

        // Check for an assessmentSection.
        $assessmentSectionIdentifierMap = $this->getAssessmentSectionIdentifierMap();
        if (isset($assessmentSectionIdentifierMap[$id])) {
            if ($assessmentSectionIdentifierMap[$id][0]->getTestPart() !== $this->current()->getTestPart()) {
                // From IMS QTI:
                // In the case of an item or section, the target must refer to an item or section in the same test-part
                // that has not yet been presented.
                $this->next();

                return;
            }

            // We branch to the first RouteItem belonging to the section.
            $this->setPosition($this->getRouteItemPosition($assessmentSectionIdentifierMap[$id][0]));

            return;
        }

        // Check for a testPart.
        $testPartIdentifierMap = $this->getTestPartIdentifierMap();
        if (isset($testPartIdentifierMap[$id])) {
            // We branch to the first RouteItem belonging to the testPart.
            $this->setPosition($this->getRouteItemPosition($testPartIdentifierMap[$id][0]));

            return;
        }

        // No such identifier referenced in the route, cannot branch.
        $msg = "No such identifier '{$id}' found in the route for branching.";
        throw new OutOfBoundsException($msg);
    }

    /**
     * Get the position of $routeItem in the Route.
     *
     * @param RouteItem $routeItem A RouteItem you want to know the position.
     * @return int The position of the routeItem in the Route. The indexes begin at 0.
     * @throws OutOfBoundsException If no such $routeItem is referenced in the Route.
     */
    public function getRouteItemPosition(RouteItem $routeItem): int
    {
        if (($search = array_search($routeItem, $this->getRouteItems(), true)) !== false) {
            return $search;
        } else {
            $msg = 'No such RouteItem object referenced in the Route.';
            throw new OutOfBoundsException($msg);
        }
    }
}
