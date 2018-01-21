<?php


abstract class FrontEndEditorPreviousAndNextSequencer extends Object
{

    /**
     * this is the key function where you calculate the next step.
     * @param  int    $pageNumber page number you are keen to get
     * @return string the URL to the next page ...
     */
    public function getLink(int $pageNumber) : string;

    public function TotalNumberOfPages() : int
    {
        return $this->AllLinks()->count();
    }

    abstract public function AllLinks() : ArrayList;

    /**
     * This is for the whole sequence, not just one of the steps ....
     * @return bool
     */
    abstract public function canView($member = null) : boolean;

}
