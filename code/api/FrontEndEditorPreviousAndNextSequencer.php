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
     * <code>
     * This is the key method you need to extend ...
     *      MyBusinessOpeningHours => [
     *          'Min' => 0
     *          'Max' => 99,
     *          'Parameters' => MIXED
     *      ],
     *      MyScientificResults => [
     *          'Min' => 5
     *          'Max' => 5,
     *          'Parameters' => MIXED
     *      ]
     * </code>
     *
     * Where MyBusinessOpeningHours and MyScientificResults are FrontEndEditable DataObjects.
     *
     *
     *
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
    abstract public function AddAnotherOfThisClass($className);

    /**
     * @return string
     */
    public function Link(): string
    {
        $page = DataObject::get_one('FrontEndEditorPage');
        if ($page) {
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
        if ($obj && $obj instanceof FrontEndEditable && $obj->canEdit($member)) {
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
        if ($item === null) {
            $item = $this->getCurrentRecordBeingEdited();
        }
        if ($item) {
            return $item->FrontEndEditLink();
        } else {
            $page = DataObject::get_one('FrontEndEditorPage');
            if ($page) {
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
        if ($this->_allPages === null) {
            $currentObject = $this->getCurrentRecordBeingEdited();
            if ($currentObject) {
                $parent = $this->FrontEndParentObject();
                if ($parent) {
                    $this->_allPages = ArrayList::create();
                    $array = $this->ArrayOfClassesToSequence();
                    foreach ($array as $myClassName => $configs) {
                        if ($className === null || $className === $myClassName) {
                            $items =  $this->FrontEndFindChildObjects($myClassName)->sort(['ID' => 'ASC'])->limit(50);
                            $count = $items->count();
                            if ($count === 0) {
                                // $this->_allPages->push($className::create());
                            } else {
                                foreach ($items as $count => $item) {
                                    $this->_allPages->push($item);
                                }
                            }
                        }
                    }
                }
            }
        }
        if (! $this->_allPages) {
            return ArrayList::create();
        }

        return $this->_allPages;
    }

    public function CountForClassName($className)
    {
        $allPages = $this->AllPages($className)->count();
    }

    /**
     * first position = 1
     * last position = pages.count
     *
     * @param string|null $className OPTIONAL
     *
     * @return int
     */
    public function CurrentRecordPositionInSequence($className = null) : int
    {
        $position = 0;
        $currentRcurrentRecordBeingEdited = $this->getCurrentRecordBeingEdited();
        $allPages = $this->AllPages($className);
        foreach ($allPages as $count => $page) {
            if ($page->FrontEndUID() === $currentRcurrentRecordBeingEdited->FrontEndUID()) {
                $position = $count;
                break;
            }
        }

        return $position;
    }

    public function HasPreviousPage() : bool
    {
        return $this->CurrentRecordPositionInSequence() > 1;
    }


    /**
     * @return FrontEndEditable|null
     */
    public function PreviousPageObject()
    {
        if ($this->HasPreviousPage()) {
            $pos = $this->CurrentRecordPositionInSequence() - 1;

            return $this->getPageItem($pos);
        }
    }

    public function HasNextPage() : bool
    {
        return $this->CurrentRecordPositionInSequence() < $this->TotalNumberOfPages();
    }

    /**
     * @return FrontEndEditable|null
     */
    public function NextPageObject()
    {
        if ($this->HasNextPage()) {
            $pos = $this->CurrentRecordPositionInSequence() + 1;

            return $this->getPageItem($pos);
        }
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
        if ($currentRecordBeingEdited && $currentRecordBeingEdited->exists()) {
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
        if ($this->currentRecordBeingEdited && $this->currentRecordBeingEdited->exists()) {
            //do nothing
        } else {
            $this->currentRecordBeingEdited = FrontEndEditorSessionManager::get_record_being_edited();
        }

        return $this->currentRecordBeingEdited;
        ;
    }

    public function TotalNumberOfPages() : int
    {
        return $this->AllPages()->count();
    }


    /**
     * do we have to add another child for this Class?
     *
     * @param  string $className
     * @return bool
     */
    public function MustAddAnotherOfThisClass($className) : bool
    {
        $existingChildren = $this->FrontEndFindChildObjects();
        $config = $this->ArrayOfClassesToSequence($className);
        $count = $existingChildren->count();

        return $count < $config['Min'];
    }

    /**
     * can we add another child for this Class?
     *
     * @param  string $className
     * @return bool
     */
    public function CanAddAnotherOfThisClass($className) : bool
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
        if ($parent && $parent->exists()) {
            return $parent->FrontEndFindChildObjects($className);
        }
        return ArrayList::create();
    }

    protected $_rootParent_cache = null;

    /**
     * @return FrontEndEditable
     */
    protected function FrontEndParentObject()
    {
        if (self::$_rootParent_cache === null) {
            $currentObject = $this->getCurrentRecordBeingEdited();
            if ($currentObject) {
                self::$_rootParent_cache = $this->FrontEndParentObject();
            }
        }

        return self::$_rootParent_cache;
    }



    /**
     * @param int|string|null
     *
     * @return FrontEndEditable
     */
    public function getPageItem($pageNumberOrFrontEndUID) : FrontEndEditable
    {
        if ($pageNumberOrFrontEndUID === null) {
            $pageNumberOrFrontEndUID = $this->FrontEndUID();
        }
        foreach ($this->AllPages() as $count => $item) {
            if (
                (int) $count === (int) $pageNumberOrFrontEndUID ||
                (string) $item->FrontEndUID() === (string) $pageNumberOrFrontEndUID
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
        foreach ($this->AllPages() as $count => $item) {
            if ($item->FrontEndUID() === $string) {
                return $count + 1;
            }
        }

        return 0;
    }


    protected function FrontEndUID(): string
    {
        $obj = $this->getCurrentRecordBeingEdited();

        return FrontEndEditorSessionManager::object_to_string($obj);
    }
}
