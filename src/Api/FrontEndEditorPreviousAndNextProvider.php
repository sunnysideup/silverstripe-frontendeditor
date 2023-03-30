<?php

namespace SunnySideUp\FrontendEditor\Api;

use SilverStripe\Core\Injector\Injector;
use SunnySideUp\FrontendEditor\Api\FrontEndEditorPreviousAndNextSequencer;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SunnySideUp\FrontendEditor\Model\Explanations\FrontEndEditorSequencerExplanation;
use SilverStripe\View\ArrayData;
use SunnySideUp\FrontendEditor\Interfaces\FrontEndEditable;
use SilverStripe\View\ViewableData;

/**
 *
 * this class manages the previous and next step
 * it provides functions that are independent from the
 * sequencer being used so that it can run any type of sequence.
 */



class FrontEndEditorPreviousAndNextProvider extends ViewableData
{
    /**
     * cached variable for a singleton pattern
     * @var FrontEndEditorPreviousAndNextProvider
     */
    private static $_me_cached = null;

    /**
     * returns a singleton
     * @param string|null $sequencerClassName
     * @param FrontEndEditable|null $currentRecordBeingEdited
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public static function inst($sequencerClassName = null, $currentRecordBeingEdited = null): FrontEndEditorPreviousAndNextProvider
    {
        if (self::$_me_cached === null) {
            self::$_me_cached = Injector::inst()->get(FrontEndEditorPreviousAndNextProvider::class);
        }
        if ($sequencerClassName) {
            self::$_me_cached->setSequenceProvider($sequencerClassName);
        }
        if ($currentRecordBeingEdited) {
            self::$_me_cached->setCurrentRecordBeingEdited($currentRecordBeingEdited);
        }

        return self::$_me_cached;
    }

    /**
     * returns a list of sequences available to the current member
     *
     * @param Member $member
     *
     * @return ArrayList
     */
    public function ListOfSequences($member = null): ArrayList
    {
        $array = [];
        $list = ClassInfo::subclassesFor(FrontEndEditorPreviousAndNextSequencer::class);
        unset($list[FrontEndEditorPreviousAndNextSequencer::class]);
        $currentSequencerClassName = $this->getClassName();
        $al = ArrayList::create();

        foreach ($list as $className) {

            $classObject = Injector::inst()->get($className);
            if ($classObject->canView($member = null)) {

                $explanation = FrontEndEditorSequencerExplanation::add_or_find_item($className);
                $array['Title'] = $classObject->Title();
                $array['Description'] = $explanation->ShortDescription;
                $array['Link'] = $classObject->Link();
                if ($classObject->class === $currentSequencerClassName) {
                    $array['LinkingMode'] = 'current';
                } else {
                    $array['LinkingMode'] = 'link';
                }
                $al->push(ArrayData::create($array));
            }
        }

        $this->extend('UpdateListOfSequences', $al);

        return $al;
    }

    public function ArrayOfClassesToSequence()
    {
        return $this->runOnSequencer('ArrayOfClassesToSequence', []);
    }

    /**
     *
     * @var string
     */
    protected $sequencerClassName = '';

    /**
     * @param string $className
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */


    public function setSequenceProvider($className): FrontEndEditorPreviousAndNextProvider
    {
        $list = ClassInfo::subclassesFor(FrontEndEditorPreviousAndNextSequencer::class);
        $list = array_change_key_case($list);

        if (isset($list[$className]) && $className !== FrontEndEditorPreviousAndNextSequencer::class) {

            $className = $list[$className];


            $this->sequencerClassName = $className;


            FrontEndEditorSessionManager::set_sequencer($className);
        } else {

            user_error($className.' does not extend FrontEndEditorPreviousAndNextSequencer.');
        }

        return $this;
    }

    /**
     *
     * @param  FrontEndEditable (DataObject)
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public function setCurrentRecordBeingEdited($currentRecordBeingEdited): FrontEndEditorPreviousAndNextProvider
    {
        $this->runOnSequencer('setCurrentRecordBeingEdited', null, $params = [$currentRecordBeingEdited]);

        return $this;
    }

    /**
     *
     * @return FrontEndEditable|null
     */
    public function getCurrentRecordBeingEdited()
    {
        return $this->runOnSequencer('getCurrentRecordBeingEdited', null);
    }

    /**
     * @alias
     * @return bool
     */
    public function InSequence()
    {
        return $this->HasSequencer();
    }

    /**
     * @return bool
     */
    public function NotInSequence()
    {
        return ! $this->HasSequencer();
    }

    /**
     * a sequencer has been set ...
     * @return bool
     */
    public function HasSequencer(): bool
    {
        return $this->getSequencer() ? true : false;
    }

    /**
     *
     * @return bool
     */
    public function HasCurrentRecordBeingEdited(): bool
    {
        return $this->HasSequencer() && $this->getCurrentRecordBeingEdited() ? true : false;
    }

    private static $_my_sequencer = null;

    /**
     *
     * @return FrontEndEditorPreviousAndNextSequencer
     */
    public function getSequencer()
    {
        if (self::$_my_sequencer === null) {

            $className = $this->getClassName();


            if ($className) {

                self::$_my_sequencer = Injector::inst()->get($className);
            }
        }

        return self::$_my_sequencer;
    }

    /**
     *
     * @return string
     */
    protected function getClassName(): string
    {
        if (! $this->sequencerClassName) {
            $this->sequencerClassName = FrontEndEditorSessionManager::get_sequencer();
        }

        return strval($this->sequencerClassName);
    }

    /**
     * to kick start a new sequence
     * this method must set the first record being edited.
     *
     * @return FrontEndEditorPreviousAndNextProvider [description]
     */
    public function StartSequence(): FrontEndEditorPreviousAndNextProvider
    {
        $this->runOnSequencer('StartSequence', null);

        return $this;
    }

    /**
     * force to go to a new page
     * you either pass the new record or the relative position of the new page (e.g. -1 / 1, 2)
     *
     * @param  FrontEndEditable|int $newRecordBeingEditedOrRelativePageNumber
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public function setPage($newRecordBeingEditedOrRelativePageNumber): FrontEndEditorPreviousAndNextProvider
    {
        $item = null;
        if (is_int($newRecordBeingEditedOrRelativePageNumber)) {
            //find all links
            $links = $this->AllPages();
            $linksAsArray = $links->toArray();
            //find new page number
            $currentPageNumber = $this->getPageNumber() - 1;
            $newPageNumber = $currentPageNumber + $newRecordBeingEditedOrRelativePageNumber;
            if (isset($linksAsArray[$newPageNumber])) {
                $item = $linksAsArray[$newPageNumber];
                if (! $item->exists()) {
                    $item = $this->AddAnotherOfThisClass($item->ClassName);
                }
            } else {
                //run again to show error
                user_error('Page set is not valid: '.$newRecordBeingEditedOrRelativePageNumber);

                return $this;
            }
        } elseif ($newRecordBeingEditedOrRelativePageNumber instanceof FrontEndEditable) {
            $item = $newRecordBeingEditedOrRelativePageNumber;
        } else {
            user_error('Page set is not valid: '.print_r($newRecordBeingEditedOrRelativePageNumber, 1));

            return $this;
        }
        if ($item !== null) {
            $this->setCurrentRecordBeingEdited($item);
        } else {
            user_error('Could not find item');
        }

        return $this;
    }


    /**
     *
     * @return string
     */
    public function Link(): string
    {
        return $this->getPageLink(0);
    }


    /**
     * @param int $offSetFromCurrent
     *
     * @return string
     */
    public function getPageLink($offSetFromCurrent = 0): string
    {
        $item = null;
        if ($offSetFromCurrent !== 0) {
            $item = $this->getPageItem($offSetFromCurrent);
        }
        return $this->runOnSequencer('getPageLink', '404-page-not-found-for-sequencer', $params = [$item]);
    }

    /**
     * returns 1 - [ number of pages in sequence]
     * @return int
     */
    public function CurrentRecordPositionInSequence()
    {
        return $this->runOnSequencer('CurrentRecordPositionInSequence', 1);
    }

    /**
     *
     * @return int
     */
    public function TotalNumberOfPages(): int
    {
        return $this->runOnSequencer('TotalNumberOfPages', 0);
    }

    /**
     * @param string $className OPTIONAL
     * @return ArrayList
     */


    public function AllPages($className = null): ArrayList
    {
        return $this->runOnSequencer('AllPages', ArrayList::create(), $params = [$className]);
    }

    /**
     *
     * @param string $className (OPTIONAL)
     *
     * @return FrontEndEditable|null
     */


    public function AddAnotherOfThisClass($className = null)
    {
        return $this->runOnSequencer('AddAnotherOfThisClass', null, $params = [$className]);
    }

    /**
     * is there another page to work through?
     *
     * @param  string|null $classNMame
     * @return bool
     */


    public function HasNextPage($className = null): bool
    {
        return $this->runOnSequencer('HasNextPage', false, $params = [$className]);
    }

    /**
     *
     * @return string
     */
    public function NextPageLink(): string
    {
        return $this->getPageLink(1);
    }

    /**
     * @return FrontEndEditable|null
     */
    public function NextPageObject()
    {
        return $this->runOnSequencer('NextPageObject', null);
    }

    /**
     *
     * @return string
     */
    public function goNextPage(): string
    {
        if ($canGo = $this->runOnSequencer('PrepareForNextPage', false)) {
            $this->setPage(1);
            $link = $this->getPageLink(0);
            return $link;
        }

        return $this->getPageLink(0);
    }

    /**
     *
     * @return string
     */
    public function goAddAnother(): string
    {
        if ($canGo = $this->runOnSequencer('PrepareAddAnother', false)) {
            $this->setPage(1);
            $link = $this->getPageLink(0);
            return $link;
        }

        return $this->getPageLink(0);
    }

    /**
     * is there a previous page to work through?
     *
     * @param  string|null $classNMame
     * @return bool
     */


    public function HasPreviousPage($className = null): bool
    {
        return $this->runOnSequencer('HasPreviousPage', false, $params = [$className]);
    }

    /**
     *
     * @return string
     */
    public function PreviousLink(): string
    {
        return $this->getPageLink(-1);
    }

    /**
     * @return FrontEndEditable|null
     */
    public function PreviousPageObject()
    {
        return $this->runOnSequencer('PreviousPageObject', null);
    }



    /**
     *
     * @return bool
     */
    public function canGoPreviousOrNextPage(): bool
    {
        return $this->canGoPreviousPage() || $this->canGoNextPage();
    }


    /**
     *
     * @return bool
     */
    public function canGoPreviousPage(): bool
    {
        $currentPageNumber = $this->getPageNumber();

        if ($currentPageNumber === 1) {
            return false;
        }
        /** @var DataObject $object */
        $object = $this->PreviousPageObject();

        return $object && $object->exists() ? true : false;
    }

    /**
     *
     * @return bool
     */
    public function canGoNextPage(): bool
    {
        $currentPageNumber = $this->getPageNumber();

        if ($currentPageNumber === $this->TotalNumberOfPages()) {
            return false;
        }
        /** @var DataObject $object */
        $object = $this->NextPageObject();

        return $object && $object->exists() ? true : false;
    }

    /**
     *
     * @return string
     */
    public function goPreviousPage(): string
    {
        $this->setPage(-1);

        return $this->getPageLink(0);
    }



    /**
     * @param string $className
     *
     * @return FrontEndEditable|null
     */


    protected function CanAddAnotherOfThisClass($className, $currentRecordIsNew = false): bool
    {
        return $this->runOnSequencer('CanAddAnotherOfThisClass', false, $className, $params = [$currentRecordIsNew]);
    }


    /**
     * @param int|string|null $pageNumberOrFrontEndUID
     *
     * @return FrontEndEditable|null
     */
    protected function getPageItem($pageNumberOrFrontEndUID): FrontEndEditable
    {
        return $this->runOnSequencer('getPageItem', null, $params = [$pageNumberOrFrontEndUID]);
    }

    /**
     *
     * @return int
     */
    protected function getPageNumber(): int
    {
        return $this->runOnSequencer('getPageNumber', 0);
    }

    protected function FrontEndUID(): string
    {
        return $this->runOnSequencer('FrontEndUID', 'DataObject,0');
    }

    /**
     * run a method in the sequencer ..
     *
     * @param  string $method      the method to run
     * @param  mixed $backupValue  what to return when there is no sequencer
     * @param  mixed $param1       first parameter
     * @param  mixed $param2       second parameter
     * @param  mixed $param3       third parameter
     *
     * @return mixed
     */
    public function runOnSequencer($method, $backupValue, $params = [])
    {
        $sequencer = $this->getSequencer();
        if ($sequencer) {
            return call_user_func_array([$sequencer, $method], $params);
        }

        return $backupValue;
    }

    public function debug($currentRecordBeingEdited = null)
    {
        if ($currentRecordBeingEdited !== null) {
            $this->setCurrentRecordBeingEdited($currentRecordBeingEdited);
        }
        $html = '';
        $html .= '<h1>There is an active sequence</h1>';
        $html .= '<hr />';
        $html .= '<pre>';
        $html .= '<hr /><h3>Owner</h3>';
        $html .= '<hr />';
        $html .= print_r($this->getCurrentRecordBeingEdited()->Title, 1);
        $html .= '<hr /><h3>Has Next Page</h3>';
        $html .= '<hr />';
        $html .= print_r($this->HasNextPage() ? 'TRUE' : 'FALSE', 1);
        $html .= '<hr /><h3>Next Object</h3>';
        $html .= '<hr />';
        $html .= print_r($this->NextPageObject() ? $this->NextPageObject()->FrontEndUID() : 'N/A', 1);
        $html .= print_r(', ', 1);
        $html .= print_r($this->canGoNextPage() ? 'Accessible' : 'Not Accessible', 1);

        $html .= '<hr /><h3>Has Previous Page</h3>';
        $html .= '<hr />';
        $html .= print_r($this->HasPreviousPage() ? 'TRUE' : 'FALSE', 1);

        $html .= '<hr /><h3>Previous Object</h3>';
        $html .= '<hr />';
        $html .= print_r($this->PreviousPageObject() ? $this->PreviousPageObject()->FrontEndUID() : 'N/A', 1);
        $html .= print_r(', ', 1);
        $html .= print_r($this->canGoPreviousPage() ? 'Accessible' : 'Not Accessible', 1);

        $html .= '<hr /><h3>Total Number of Pages</h3>';
        $html .= '<hr />';
        $html .= print_r($this->TotalNumberOfPages(), 1);

        $html .= '<hr /><h3>Previous And Next Provider</h3>';
        $html .= '<hr />';
        $html .= print_r($this, 1);

        $html .= '<hr /><h3>Current Sequence</h3>';
        $html .= '<hr />';
        $html .= print_r($this->getSequencer()->class, 1);

        $html .= '<hr /><h3>All Pages</h3>';
        $html .= '<hr />';
        foreach ($this->AllPages() as $page) {
            $html .= print_r($page->FrontEndUID(), 1);
            $html .= '<br />';
        }

        $html .= '<hr /><h3>List of Sequences</h3>';
        $html .= '<hr />';
        $html .= print_r($this->ListOfSequences(), 1);
        $html .= '</pre>';

        return $html;
    }
}
