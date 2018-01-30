<?php

/**
 *
 * this class manages the previous and next step
 * it provides functions that are independent from the
 * sequencer being used ....
 */

class FrontEndEditorPreviousAndNextProvider extends Object
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
    public static function inst($sequencerClassName = null, $currentRecordBeingEdited = null) : FrontEndEditorPreviousAndNextProvider
    {
        if(self::$_me_cached === null) {
            self::$_me_cached = Injector::inst()->get('FrontEndEditorPreviousAndNextProvider');
        }
        if($sequencerClassName) {
            self::$_me_cached->setSequenceProvider($sequencerClassName);
        }
        if($currentRecordBeingEdited) {
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
    public function ListOfSequences($member = null) : ArrayList
    {
        $array = [];
        $list = ClassInfo::subclassesFor('FrontEndEditorPreviousAndNextSequencer');
        unset($list['FrontEndEditorPreviousAndNextSequencer']);
        $currentSequencerClassName = $this->getClassName();
        foreach($list as $className) {
            $class = Injector::inst()->get($className);
            if($class->canView($member)) {
                $explanation = FrontEndEditorSequencerExplanation::add_or_find_item($className);
                $class->Description = $explanation->ShortDescription;
                if($class->class === $currentSequencerClassName) {
                    $class->LinkingMode = 'current';
                } else {
                    $class->LinkingMode = 'link';
                }
                $array[] = $class;
            }
        }
        $arrayList = ArrayList::create($array);

        $this->extend('UpdateListOfSequences', $arrayList);

        return $arrayList;
    }

    public function ArrayOfClassesToSequence()
    {
        $sequencer = $this->getSequencer();
        if($sequencer) {
            return $sequencer->ArrayOfClassesToSequence()
        }
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
    public function setSequenceProvider($className) : FrontEndEditorPreviousAndNextProvider
    {
        $list = ClassInfo::subclassesFor('FrontEndEditorPreviousAndNextSequencer');
        $list = array_change_key_case($list);
        if(isset($list[$className]) && $className !== 'FrontEndEditorPreviousAndNextSequencer') {
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
    public function setCurrentRecordBeingEdited($currentRecordBeingEdited) : FrontEndEditorPreviousAndNextProvider
    {
        //set in custom sequencer
        $obj = $this->getSequencer();
        if($obj) {
            $obj->setCurrentRecordBeingEdited($currentRecordBeingEdited);
        }

        return $this;
    }

    /**
     *
     * @return FrontEndEditable|null
     */
    public function getCurrentRecordBeingEdited()
    {
        $obj = $this->getSequencer();
        if($obj) {
            return $obj->getCurrentRecordBeingEdited();
        }
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
        if(self::$_my_sequencer === null) {
            $className = $this->getClassName();
            if($className) {
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

        if(! $this->sequencerClassName) {
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
    public function StartSequence() : FrontEndEditorPreviousAndNextProvider
    {
        $obj = $this->getSequencer();
        if($obj) {
            $obj->StartSequence();
        }

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
    public function setPage($newRecordBeingEditedOrRelativePageNumber) : FrontEndEditorPreviousAndNextProvider
    {
        $item = null;
        if(is_int($newRecordBeingEditedOrRelativePageNumber)) {
            //find all links
            $links = $this->AllPages();
            $linksAsArray = $links->toArray();
            //find new page number
            $currentPageNumber = $this->getPageNumber();
            $newPageNumber = $currentPageNumber + $newRecordBeingEditedOrRelativePageNumber;
            if(isset($linksAsArray[$newPageNumber])) {
                $item = $linksAsArray[$newPageNumber];
            } else {
                //run again to show error
                user_error('Page set is not valid: '.$newRecordBeingEditedOrRelativePageNumber);

                return $this;
            }
        } elseif($newRecordBeingEditedOrRelativePageNumber instanceof FrontEndEditable) {
            $item = $newRecordBeingEditedOrRelativePageNumber;
        } else {
            user_error('Page set is not valid: '.print_r($newRecordBeingEditedOrRelativePageNumber, 1));

            return $this;
        }
        if($item !== null) {
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
    public function Link() : string
    {
        return $this->getPageLink(0);
    }


    /**
     * @param int $offSetFromCurrent
     *
     * @return string
     */
    public function getPageLink($offSetFromCurrent = 0) : string
    {
        $item = null;
        if($offSetFromCurrent !== 0) {
            $item = $this->getPageItem($offSetFromCurrent);
        }
        $obj = $this->getSequencer();
        if($obj) {
            return $obj->getPageLink($item);
        }
        return '404-page-not-found-for-sequencer';
    }

    /**
     *
     * @return int
     */
    public function TotalNumberOfPages() : int
    {
        $obj = $this->getSequencer();
        if($obj) {
            return $obj->TotalNumberOfPages();
        }

        return 0;
    }

    /**
     *
     * @return ArrayList
     */
    public function AllPages() : ArrayList
    {
        $obj = $this->getSequencer();
        if($obj) {
            return $obj->AllPages();
        }

        return ArrayList::create();
    }


    public function AddAnotherOfThisClass($className = null)
    {
        $obj = $this->getSequencer();
        if($obj) {
            return $obj->AddAnotherOfThisClass($className);
        }


    }

    /**
     *
     * @return string
     */
    public function PreviousLink() : string
    {
        return $this->getPageLink(-1);
    }

    /**
     *
     * @return string
     */
    public function NextPageLink() : string
    {
        return $this->getPageLink(1);
    }

    /**
     *
     * @return string
     */
    public function goNextPage() : string
    {
        $this->setPage(1);

        return $this->getPageLink(0);
    }

    /**
     *
     * @return string
     */
    public function goPreviousPage() : string
    {
        $this->setPage(-1);

        return $this->getPageLink(0);
    }

    /**
     * @param int|string|null
     *
     * @return FrontEndEditable
     */
    protected function getPageItem($pageNumberOrFrontEndUID) : FrontEndEditable
    {
        if($pageNumberOrFrontEndUID === null) {
            $pageNumberOrFrontEndUID = $this->FrontEndUID();
        }
        foreach($this->AllPages() as $count => $item) {
            if(
                $count === $pageNumberOrFrontEndUID ||
                $item->FrontEndUID() === $pageNumberOrFrontEndUID
            ) {
                return $item;
            }
        }

        return user_error('Can not find page item for '.$pageNumberOrFrontEndUID);
    }

    /**
     *
     * @return int
     */
    protected function getPageNumber() : int
    {
        $string = $this->FrontEndUID();
        foreach($this->AllPages() as $count => $item) {
            if($item->FrontEndUID() === $string) {
                return $count + 1;
            }
        }

        return 0;
    }

    protected function FrontEndUID() : string
    {
        $obj = $this->getCurrentRecordBeingEdited();

        return FrontEndEditorSessionManager::object_to_string($obj);
    }


}
