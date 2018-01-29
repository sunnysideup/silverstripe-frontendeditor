<?php

/**
 *
 * this class can be extended for building a custom sequence
 * it knows about the record being edited.
 *
 * You start with an array sequence (ArrayOfClassesToSequence)
 * You may also add in a Class+ (creates one class)
 * or Class++ where the user is kept asked to add more until there are enough.
 *
 */

abstract class FrontEndEditorPreviousAndNextSequencer extends ViewableData
{

    /**
     *
     * This is the key method you need to extend ...
     *      MyBusinessOpeningHours => [
     *          'Min' => 0
     *          'Max' => 99,
     *          'Parameters' => MIXED
     *      ]
     *      MyScientificResults => [
     *          'Min' => 5
     *          'Max' => 5,
     *          'Parameters' => MIXED
     *      ]
     * If items already exists then the existing ones will be used....
     * @param string $className - set ClassName to get the data for only one item
     * @return array
     */
    abstract public function ArrayOfClassesToSequence($className = null) : array;

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
     * It is very like you will need to extend this method!
     * @param  string $className
     *
     * @return FrontEndEditable|null
     */
    abstract public function AddChild($className);

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
        if($obj && $obj instanceof FrontEndEditable && $obj->canEdit($member)) {
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
     * @param string|null $className
     *
     * @return ArrayList
     */
    public function AllPages($className = null) : ArrayList
    {
        if($this->_allPages === null) {
            echo 'A';
            $currentObject = $this->getCurrentRecordBeingEdited();
            if($currentObject) {
                echo 'B';
                $parent = $this->FrontEndParentObject();
                if($parent) {
                    $this->_allPages = ArrayList::create();
                    $array = $this->ArrayOfClassesToSequence();
                    foreach($array as $myClassName => $configs) {
                        if($className === null || $className === $myClassName) {
                            $items =  $this->FrontEndFindChildObjects($myClassName)->sort(['ID' => 'ASC'])->limit(50);
                            $count = $items->count();
                            if($count === 0) {
                                // $this->_allPages->push($className::create());
                            } else {
                                foreach($items as $count => $item) {
                                    $this->_allPages->push($item);
                                }
                            }
                        }
                    }
                }
            }
        }
        if(! $this->_allPages) {
            return ArrayList::create();
        }

        return $this->_allPages;
    }

    public function CountForClassName($className)
    {
        $allPages = $this->AllPages($className)->count();
    }

    public function CurrentRecordPositionInClassName($className)
    {
        $allPages = $this->AllPages($className)->count();
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
            //do nothing
        } else {
            $this->currentRecordBeingEdited = FrontEndEditorSessionManager::get_record_being_edited();
        }

        return $this->currentRecordBeingEdited;;

    }

    public function TotalNumberOfPages() : int
    {
        return $this->AllPages()->count();
    }


    public function MustAddChild($className) : bool
    {
        $existingChildren = $this->FrontEndFindChildObjects();
        $config = $this->ArrayOfClassesToSequence($className);
        $count = $existingChildren->count();

        return $count < $config['Min'];

    }

    public function CanAddChild($className) : bool
    {
        $existingChildren = $this->FrontEndFindChildObjects();
        $config = $this->ArrayOfClassesToSequence($className);
        $count = $existingChildren->count();

        return $count < $config['Max'];

    }


    /**
     * returns a datalist of objects of a particular class
     * (e.g. Page will include HomePage)
     * that share a particular root parent.
     * @param string $className [description]
     *
     * @return ArrayList
     */
    public function FrontEndFindChildObjects($className) : ArrayList
    {
        $parent = $this->FrontEndParentObject();
        if($parent && $parent->exists()) {
            return $parent->FrontEndFindChildObjects($className);
        }
        return ArrayList::create();
    }

    protected $_rootParent_cache = null;

    protected function FrontEndParentObject()
    {
        if(self::$_rootParent_cache === null) {
            $currentObject = $this->getCurrentRecordBeingEdited();
            if($currentObject) {
                self::$_rootParent_cache = $this->FrontEndParentObject();
            }
        }

        return self::$_rootParent_cache;
    }


}
