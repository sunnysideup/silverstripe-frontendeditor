<?php


abstract class FrontEndEditorPreviousAndNextSequencer extends Object
{


    /**
     * this is the key function where you calculate the next step.
     * @param  int    $pageNumber page number you are keen to get
     * @param  FrontEndEditable (DataObject)
     *
     * @return string the URL to the next page ...
     */
    abstract public function getLink(int $pageNumber) : string;

    /**
     *
     * @return ArrayList
     */
    abstract public function AllLinks() : ArrayList;

    /**
     * This is for the whole sequence, not just one of the steps ....
     * @return bool
     */
    abstract public function canView($member = null) : boolean;


    protected $currentRecordBeingEdited = null;

    public function setRecordBeingEdited($currentRecordBeingEdited) : FrontEndEditorPreviousAndNextSequencer
    {
        $this->currentRecordBeingEdited = $currentRecordBeingEdited;

        return $this;
    }

    /**
     *
     * @return FrontEndEditable
     */
    public function getRecordBeingEdited() : FrontEndEditable
    {
        return $this->currentRecordBeingEdited;
    }

    public function TotalNumberOfPages() : int
    {
        return $this->AllLinks()->count();
    }

}
