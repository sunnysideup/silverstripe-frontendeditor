<?php

/**
 *
 * this class can be extended for building a custom sequence
 * it knows about the record being edited.
 *
 * You start with an array sequence (ArrayOfClassesToSequence)
 * You may also add in a Class+ (creates one class)
 * or Class++ where the user is kept asked to add more until there are enough
 */

abstract class FrontEndEditorPreviousAndNextSequencer extends ViewableData
{

    /**
     * @return array
     */
    abstract public function ArrayOfClassesToSequence() : array;

    /**
     * This method myst set the first record being edited...
     */
    abstract public function StartSequence();

    /**
     * e.g. Enter Rates
     * @return string
     */
    abstract public function Title() : string;

    /**
     * e.g. Allows a merchant to enter all rates relating to their area.
     * @return string
     */
    abstract public function Description() : string;

    private static $singular_name = 'Please override';
    abstract public function i18n_singular_name();
    private static $plural_name = 'Please override';
    abstract public function i18n_plural_name();

    /**
     * @return string
     */
    public function Link(): string
    {
        $page = DataObject::get_one('FrontEndEditorPage');
        if($page) {
            return $page->Link('startsequence/'.strtolower(get_class($this)));
        }

        return '404-front-end-editor-page-not-found';
    }

    /**
     * This is for the whole sequence, not just one of the steps ....
     * You may want to extend this for more specific purposes.
     * @return bool
     */
    public function canView($member = null) : bool
    {
        $obj = $this->getCurrentRecordBeingEdited();
        if($obj && $obj->canEdit($member)) {
            return true;
        }

        return Permission::check('Admin', 'any', $member);
    }

    /**
     * this is the key function where you calculate the next step.
     * @param  FrontEndEditable (DataObject)
     *
     * @return string the URL to the next page ...
     */
    public function getPageLink($item = null) : string
    {
        if($item === null) {
            $item = $this->getCurrentRecordBeingEdited();
        }
        if($item) {
            return $item->FrontEndEditLink();
        } else {
            $page = DataObject::get_one('FrontEndEditorPage');
            if($page) {
                return $page->Link();
            }
        }

        return '404-can-not-find-page-for-sequence';
    }

    /**
     *
     * @var null|ArrayList
     */
    protected $_allPages = null;

    /**
     * You may want to customise this method
     * It requires the current record being edited ...
     *
     * @return ArrayList
     */
    public function AllPages() : ArrayList
    {
        if($this->_allPages === null) {
            $currentObject = $this->getCurrentRecordBeingEdited();
            if($currentObject) {
                $parent = $currentObject->FrontEndParentObject();
                if($parent) {
                    $this->_allPages = ArrayList::create();
                    $array = $this->ArrayOfClassesToSequence();
                    foreach($array as $className) {
                        $items =  $parent->FrontEndFindChildObjects($className)->sort(['ID' => 'ASC']);
                        if($items->count() < 30) {
                            foreach($items as $item) {
                                $this->_allPages->push($item);
                            }
                        }
                    }
                }
            }
        }
        if(! $this->_allPages) {
            return ArrayList::create();
        }
        print_r($this->_allPages->column('ID'));
        return $this->_allPages;
    }

    protected $currentRecordBeingEdited = null;

    /**
     *
     * @param FrontEndEditable $currentRecordBeingEdited [description]
     *
     * @return FrontEndEditorPreviousAndNextSequencer
     */
    public function setCurrentRecordBeingEdited($currentRecordBeingEdited) : FrontEndEditorPreviousAndNextSequencer
    {
        if($currentRecordBeingEdited && $currentRecordBeingEdited->exists()) {
            $this->currentRecordBeingEdited = $currentRecordBeingEdited;
            FrontEndEditorSessionManager::set_record_being_edited($currentRecordBeingEdited);
        }

        return $this;
    }

    /**
     *
     * @return FrontEndEditable|null
     */
    public function getCurrentRecordBeingEdited()
    {
        if($this->currentRecordBeingEdited && $this->currentRecordBeingEdited->exists()) {
            return $this->currentRecordBeingEdited;
        } else {
            return FrontEndEditorSessionManager::get_record_being_edited();
        }

    }

    public function TotalNumberOfPages() : int
    {
        return $this->AllPages()->count();
    }



}
