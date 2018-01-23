<?php

/**
 *
 * this class manages the previous and next step
 */

class FrontEndEditorPreviousAndNextProvider extends Object
{

    /**
     * cached variable
     * @var FrontEndEditorPreviousAndNextProvider
     */
    private static $_me_cached = null;

    /**
     * @param FrontEndEditable $currentRecordBeingEdited
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public static function inst($currentRecordBeingEdited = null) : FrontEndEditorPreviousAndNextProvider
    {
        if(self::$_me_cached === null) {
            self::$_me_cached = Injector::inst()->get('FrontEndEditorPreviousAndNextProvider');
        }
        if(self::$_me_cached->IsOn()) {
            if($currentRecordBeingEdited) {
                self::$_me_cached->setCurrentRecordBeingEdited($currentRecordBeingEdited);
            }
        }

        return self::$_me_cached;
    }

    /**
    * @return ArrayList
    */
    public function ListOfSequences() : ArrayList
    {
        $page = DataObject::get_one('FrontEndEditorPage');
        $array = [];
        if($page) {
            $list = ClassInfo::subclassesFor('FrontEndEditorPreviousAndNextSequencer');
            unset($list['FrontEndEditorPreviousAndNextSequencer']);
            foreach($list as $className) {
                $class = Injector::inst()->get($className);
                if($class->canView()) {
                    $array[] = ArrayData::create(
                        [
                        'Link' => $page->Link('startsequence/'.strtolower($class))
                        ]
                    );
                }
            }
        }
        $arrayList = ArrayList::create($array);

        $this->extend('UpdateListOfSequences', $arrayList, $this->getCurrentRecordBeingEdited());

        return $arrayList;
    }

    /**
     *
     * @var string
     */
    protected $sequenceProviderClassName = '';

    /**
    *
    * @param string
    *
    * @return this
    */
    public function setSequenceProvider($className)
    {
        $list = ClassInfo::subclassesFor('FrontEndEditorPreviousAndNextSequencer');
        if(isset($list[$className]) && $className !== 'FrontEndEditorPreviousAndNextSequencer') {
            $this->sequenceProviderClassName = $className;
            Session::set('FrontEndEditorPreviousAndNextProviderClassName', $className);
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
        $obj = $this->getSequenceProvider();
        $obj->setCurrentRecordBeingEdited($currentRecordBeingEdited);

        return $this;
    }

    public function getCurrentRecordBeingEdited()
    {
        $obj = $this->getSequenceProvider();

        return $obj->getCurrentRecordBeingEdited();
    }

    /**
     *
     * @return bool
     */
    public function isOn()
    {
        return $this->getClassName() ? true : false;
    }

    /**
     *
     * @return bool
     */
    public function isReady() : boolean
    {
        return $this->isOn() && $this->getCurrentRecordBeingEdited() ? true : false;
    }

    /**
     *
     * @param string
     * @return FrontEndEditorPreviousAndNextSequencer
     */
    protected function getSequenceProvider()
    {
        $className = $this->getClassName();
        if($className) {
            $obj =  Injector::inst()->get($className);
        } else {
            user_error('No sequence provider set.');
        }

        return $obj;
    }

    /**
     *
     * @return string
     */
    protected function getClassName()
    {
        if(! $this->sequenceProviderClassName) {
            $this->sequenceProviderClassName = Session::get('FrontEndEditorPreviousAndNextProviderClassName');
        }

        return $this->sequenceProviderClassName;
    }

    public function StartSequence() : FrontEndEditorPreviousAndNextProvider
    {
        $this->getSequenceProvider()->StartSequence();

        return $this;
    }



    /**
     *
     * @param  FrontEndEditable|int $currentRecordBeingEditedOrPageNumber
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public function setPage($currentRecordBeingEditedOrPageNumber) : FrontEndEditorPreviousAndNextProvider
    {
        $item = null;
        if(is_int($currentRecordBeingEditedOrPageNumber)) {
            //find all links
            $links = $this->AllLinks();
            $linksAsArray = $links->toArray();
            //find new page number
            $currentPageNumber = $this->getPageNumber();
            $newPageNumber = $currentPageNumber + $currentRecordBeingEdited;
            if(isset($linksAsArray[$newPageNumber])) {
                $item = $linksAsArray[$newPageNumber];
            } else {
                //run again to show error
                user_error('Page set is not valid: '.$currentRecordBeingEditedOrPageNumber);

                return $this;
            }
        } elseif($currentRecordBeingEditedOrPageNumber instanceof FrontEndEditable) {
            $item = $currentRecordBeingEditedOrPageNumber;
        } else {
            user_error('Page set is not valid: '.print_r($currentRecordBeingEditedOrPageNumber, 1));

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
     * @param int $offSetFromCurrent
     *
     * @return string
     */
    public function getPageLink($offSetFromCurrent = 0): string
    {
        $item = null;
        if($offSetFromCurrent !== 0) {
            $item = $this->getPageItem($offSetFromCurrent);
        }

        return $this->getSequenceProvider()->getPageLink($item);
    }

    /**
     *
     * @return int
     */
    public function TotalNumberOfPages() : int
    {
        return $this->getSequenceProvider()->TotalNumberOfPages();
    }

    /**
     *
     * @return ArrayList
     */
    public function AllLinks() : ArrayList
    {
        return $this->getSequenceProvider()->AllLinks();
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
    public function getLink() : string
    {
        return $this->getPageLink(0);
    }

    /**
     *
     * @return string
     */
    public function NextLink() : string
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
    protected function getPageItem($pageNumberOrFrontEndRootParentObjectAsString) : FrontEndEditable
    {
        if($pageNumberOrFrontEndRootParentObjectAsString === null) {
            $pageNumberOrFrontEndRootParentObjectAsString = $this->getFrontEndRootParentObjectAsStringCurrent();
        }
        foreach($this->AllLinks() as $count => $item) {
            if(
                $count === $pageNumberOrFrontEndRootParentObjectAsString ||
                $item->FrontEndRootParentObjectAsString() === $pageNumberOrFrontEndRootParentObjectAsString
            ) {
                return $item;
            }
        }

        return 0;
    }


    /**
     *
     * @return int
     */
    protected function getPageNumber() : int
    {
        $string = $this->getFrontEndRootParentObjectAsStringCurrent();
        foreach($this->AllLinks() as $count => $item) {
            if($item->FrontEndRootParentObjectAsString() === $string) {
                return $count;
            }
        }

        return 0;
    }



}
