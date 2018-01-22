<?php

/**
 *
 * this class can be extended for building a custom sequence
 */

abstract class FrontEndEditorPreviousAndNextSequencer extends Object
{

    /**
     * @return array
     */
    abstract public function ArrayOfClassesToSequence() : array;

    /**
     * @return array
     */
    abstract public function StartSequence();


    /**
     * This is for the whole sequence, not just one of the steps ....
     * @return bool
     */
    public function canView($member = null) : boolean
    {
        return Permission::check('Admin', 'any', $member);
    }

    /**
     * this is the key function where you calculate the next step.
     * @param  FrontEndEditable (DataObject)
     *
     * @return string the URL to the next page ...
     */
    public function getPageLink($item = null)
    {
        if($item === null) {
            $item = $this->currentRecordBeingEdited;
        }
        return $item->FrontEndEditLink();
    }

    /**
     *
     * @var null|ArrayList
     */
    protected $allLinks = null;

    /**
     * You may want to customise this method
     * @return ArrayList
     */
    public function AllLinks() : ArrayList
    {
        if($this->allLinks === null) {
            $linkArray = [];
            $this->allLinks = ArrayList::create();
            $parent = $this->getCurrentRecordBeingEdited()->FrontEndParentObject();
            $array = $this->ArrayOfClassesToSequence();
            print_r($array);
            foreach($array as $className) {
                $items =  $parent->FrontEndFindChildObjects($className);
                foreach($items as $item) {
                    $this->allLinks->push($item);
                }
            }
        }

        return $this->allLinks;
    }



    protected $currentRecordBeingEdited = null;

    /**
     *
     * @param  FrontEndEditable $currentRecordBeingEdited [description]
     *
     * @return FrontEndEditorPreviousAndNextSequencer
     */
    public function setCurrentRecordBeingEdited($currentRecordBeingEdited) : FrontEndEditorPreviousAndNextSequencer
    {
        $this->currentRecordBeingEdited = $currentRecordBeingEdited;
        $this->setFrontEndRootParentObjectAsStringCurrent($currentRecordBeingEdited);

        return $this;
    }

    /**
     *
     * @return FrontEndEditable|null
     */
    public function getCurrentRecordBeingEdited()
    {
        if($this->currentRecordBeingEdited) {

            return $this->currentRecordBeingEdited;
        }

    }

    public function TotalNumberOfPages() : int
    {
        return $this->AllLinks()->count();
    }




    protected function getFrontEndRootParentObjectAsStringCurrent() : string
    {
        return Session::set('FrontEndRootParentObjectAsStringCurrent');
    }


    protected function setFrontEndRootParentObjectAsStringCurrent($stringOrObject) : FrontEndEditorPreviousAndNextSequencer
    {
        if($stringOrObject instanceof FrontEndEditable) {
            $stringOrObject = $stringOrObject->FrontEndRootParentObjectAsString();
        }
        Session::set('FrontEndRootParentObjectAsStringCurrent', $stringOrObject);

        return $this;
    }

}
