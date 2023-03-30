<?php

namespace SunnySideUp\FrontendEditor\Api;









use SunnySideUp\FrontendEditor\FrontEndEditorPage;
use SilverStripe\ORM\DataObject;
use SunnySideUp\FrontendEditor\Interfaces\FrontEndEditable;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ViewableData;



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
     * e.g. Enter Rates
     * @return string
     */
    abstract public function Title() : string;

    private static $singular_name = 'Please override';
    abstract public function i18n_singular_name();

    private static $plural_name = 'Please override';
    abstract public function i18n_plural_name();

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
     * This method must set the first record being edited...
     */
    abstract public function StartSequence();

    /**
     * Get ready to edit the next one
     * Returns true if it is all go.
     * @return bool
     */
    public function PrepareForNextPage() : bool
    {
        /** @var FrontEndEditable|DataObject $nextObject */
        $nextObject = $this->NextPageObject();
        if (! $nextObject->exists()) {
            //create one
        }

        return true;
    }

    /**
     *
     * Returns true if it is all go.
     * @return bool
     */
    public function prepareAddAnother() : bool
    {
        /** @var FrontEndEditable|DataObject $currentRecord */
        $currentRecord = $this->getCurrentRecordBeingEdited();
        if ($currentRecord && $currentRecord->exists()) {
            $this->AddAnotherOfThisClass($currentRecord);
            return true;
        }

        return false;
    }

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
        /** @var FrontEndEditorPage $page */
        $page = DataObject::get_one(FrontEndEditorPage::class);
        if ($page) {
            return $page->Link().'startsequence/'.strtolower(get_class($this)).'/';
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
        /** @var FrontEndEditable|DataObject $obj */
        $obj = $this->getCurrentRecordBeingEdited();
        if ($obj && $obj instanceof FrontEndEditable && $obj->canEdit($member = null)) {
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
            $page = DataObject::get_one(FrontEndEditorPage::class);
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
                $rootParent = $this->FrontEndRootParentObject();
                if ($rootParent) {
                    $this->_allPages = ArrayList::create();
                    $array = $this->ArrayOfClassesToSequence();
                    foreach ($array as $myClassName => $configs) {


                        if ($className === null || $className === $myClassName) {
                            $linkingMode = 'link';
                            $items =  $this->FrontEndFindChildObjects($myClassName)->sort(['ID' => 'ASC'])->limit(50);
                            $count = $items->count();
                            if ($count === 0) {
                                if ($currentObject->ClassName === $myClassName) {
                                    $linkingMode = 'current';
                                }
                                $obj = Injector::inst()->get($myClassName);
                                $obj->SequenceTitle = $this->createStatement($obj, $configs);
                                if ($obj->SequenceTitle) {
                                    $obj->SequenceLinkingMode = $linkingMode;
                                    $this->_allPages->push($obj);
                                }
                                // $this->_allPages->push($className::create());
                            } else {
                                foreach ($items as $count => $obj) {
                                    if ($currentObject->ClassName == $obj->ClassName && $currentObject->ID == $obj->ID) {
                                        $linkingMode = 'current';
                                    } elseif ($currentObject->ClassName === $obj->ClassName) {
                                        $linkingMode = 'section';
                                    }
                                    $obj->SequenceTitle = $this->editStatement($obj, $configs);
                                    $obj->SequenceLinkingMode = $linkingMode;
                                    $this->_allPages->push($obj);
                                }
                            }
                        }
                    }
                }
            }
        }
        if (! $this->_allPages) {
            $this->_allPages = false;
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
        $currentRecordBeingEdited = $this->getCurrentRecordBeingEdited();


        $allPages = $this->AllPages($className);
        foreach ($allPages as $count => $page) {
            if ($page->FrontEndUID() === $currentRecordBeingEdited->FrontEndUID()) {
                $position = $count;
                break;
            }
        }

        return $position + 1;
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
            FrontEndEditorSessionManager::set_record_being_edited_in_sequence($currentRecordBeingEdited);
        }

        return $this;
    }

    private static $_count_get_current_record = 0;
    /**
     *
     * @return FrontEndEditable|null
     */
    public function getCurrentRecordBeingEdited()
    {
        self::$_count_get_current_record++;
        if (self::$_count_get_current_record > 100) {
            user_error("STOP");
        }
        if ($this->currentRecordBeingEdited && $this->currentRecordBeingEdited->exists()) {
            //do nothing
        } else {
            $this->currentRecordBeingEdited = FrontEndEditorSessionManager::get_record_being_edited_in_sequence();

            if (! $this->currentRecordBeingEdited) {
                $this->currentRecordBeingEdited = FrontEndEditorSessionManager::get_can_edit_object();
            }
        }

        return $this->currentRecordBeingEdited;
    }

    /**
     *
     * @return int
     */
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


        $existingChildren = $this->FrontEndFindChildObjects($className);


        $config = $this->ArrayOfClassesToSequence($className);
        $count = $existingChildren->count();

        return $count < $config['Min'];
    }

    /**
     * can we add another child for this Class?
     *
     * @param  string $className
     * @param  bool $$currentRecordIsNew
     *
     * @return bool
     */


    public function CanAddAnotherOfThisClass($className, $currentRecordIsNew = false) : bool
    {


        $existingChildren = $this->FrontEndFindChildObjects($className);


        $config = $this->ArrayOfClassesToSequence($className);
        $count = $existingChildren->count();
        if ($currentRecordIsNew) {
            $count++;
        }
        return $count < $config['Max'];
    }

    private static $_child_object_cache = [];

    /**
     * returns a datalist of objects of a particular class
     * (e.g. Page will include HomePage)
     * that share a particular root parent.
     * @param string $className [description]
     *
     * @return ArrayList
     */


    public function FrontEndFindChildObjects($className) : SS_List
    {


        if (! isset(self::$_child_object_cache[$className])) {


            self::$_child_object_cache[$className] = ArrayList::create();
            $rootParent = $this->FrontEndRootParentObject();
            if ($rootParent && $rootParent->exists()) {

                //exception for the root parent itself ...


                if ($rootParent->ClassName === $className) {
                    $al = ArrayList::create();
                    $al->push($rootParent);


                    self::$_child_object_cache[$className] = $al;
                } else {


                    $list = $rootParent->FrontEndFindChildObjects($className);
                    if ($list instanceof SS_List) {


                        self::$_child_object_cache[$className] = $list;
                    }
                }
            }
        }



        return self::$_child_object_cache[$className];
    }

    protected static $_root_parent_cache = null;

    /**
     * @return FrontEndEditable|false
     */
    protected function FrontEndRootParentObject()
    {
        if (self::$_root_parent_cache === null) {
            self::$_root_parent_cache = false;
            $currentObject = $this->getCurrentRecordBeingEdited();
            if ($currentObject) {
                self::$_root_parent_cache = $currentObject->FrontEndRootParentObject();
            }
        }

        return self::$_root_parent_cache;
    }



    /**
     * @param int|string|null     $pageNumberOrFrontEndUID
     * @param bool                $returnPageNumber
     *
     * @return FrontEndEditable|int
     */
    public function getPageItem($pageNumberOrFrontEndUID, $returnPageNumber = false) : FrontEndEditable
    {
        if ($pageNumberOrFrontEndUID === null) {
            $pageNumberOrFrontEndUID = $this->FrontEndUID();
        }
        foreach ($this->AllPages() as $count => $item) {
            if (
                ((int) $count === (int) $pageNumberOrFrontEndUID)
                ||
                ((string) $item->FrontEndUID() === (string) $pageNumberOrFrontEndUID)
            ) {
                if ($returnPageNumber) {
                    return $count;
                } else {
                    return $item;
                }
            }
        }

        return user_error('Can not find page item for '.$pageNumberOrFrontEndUID);
    }

    /**
     *
     * @return int
     */
    public function getPageNumber() : int
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

    /**
     *
     * @param  DataObject $obj
     * @param  array $configs
     * @return string
     */
    protected function createStatement($obj, $configs)
    {
        if (! isset($configs['Min'])) {
            $configs['Min'] = 0;
        }
        if (! isset($configs['Max'])) {
            $configs['Max'] = 0;
        }
        if ($configs['Min'] > $configs['Max']) {
            $configs['Min'] = $configs['Max'];
        }
        if ((int) $configs['Max'] === 0) {
            return '';
        }
        if ((int) $configs['Max'] === 1) {
            $name = $obj->i18n_singular_name();
        } else {
            $name = $obj->i18n_plural_name();
        }
        if ((int) $configs['Min'] === 0) {
            return 'create up to '.$configs['Max'] .' new '.$name;
        } elseif ((int) $configs['Min'] === (int) $configs['Max']) {
            return 'create '.($configs['Max'] > 1 ? $configs['Max'] : '') .' new '.$name;
        } else {
            return 'create between '.$configs['Min'] .' - '.$configs['Max'] .' new '.$name;
        }
    }

    protected function editStatement($obj, $configs)
    {
        return $obj->i18n_singular_name().': '.$obj->Title;
    }
}

