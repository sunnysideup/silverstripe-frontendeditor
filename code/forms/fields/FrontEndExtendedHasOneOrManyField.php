<?php

abstract class FrontEndExtendedHasOneOrManyField extends CompositeField
{


    /**
     * @var null | DataObject
     */
    protected $recordBeingEdited = null;

    /**
     * @var array
     */
    protected $existingSelectors = null;

    /**
     * @var string
     */
    protected $selectExistingFieldClassName = "FormField";

    /**
     * @var boolean
     */
    protected $fieldHolderIsDone = false;


    public function __construct($name, $title)
    {
        $this->name = $name;
        $this->id = $name;
        $this->title = $title;
        return parent::__construct();
    }

    /**
     *
     * @return LiteralField
     */
    protected function getHeadingField()
    {
        $id = $this->getCalculatedFieldName(true)."_HEADING";
        return LiteralField::create($id, "<h3 class=\"frontEndHasOneRelationHeader\" id=\"".$id."\">".$this->Title()."</h3>");
    }

    /**
     *
     * @return LiteralField | null
     */
    protected function getRightTitleField()
    {
        if ($rightTitle = $this->RightTitle()) {
            $id = $this->getCalculatedFieldName(true)."_RIGHT_TITLE";
            return LiteralField::create($id, "<label class=\"frontEndRelationField right\" for=\"".$this->getCalculatedFieldName(true)."\">".$rightTitle."</label>");
        }
    }


    /**
     *
     * @param DataObject $recordBeingEdited
     */
    public function setRecordBeingEdited($recordBeingEdited)
    {
        $this->recordBeingEdited = $recordBeingEdited;
    }

    /**
     *
     * @param array $existingSelectors
     */
    public function setExistingSelectors($existingSelectors)
    {
        $this->existingSelectors = $existingSelectors;
    }

    /**
     * e.g. CheckboxSetField or DropdownField
     * @return string
     */
    protected function getSelectExistingFieldClassName()
    {
        return $this->selectExistingFieldClassName;
    }

    /**
     *
     * @return string
     */
    protected function getForeignClassName()
    {
        return "ERROR IN getForeignClassName, please make sure this method has been extended in your class.";
    }

    /**
     * @return DataObject (singleton)
     */
    protected function getForeignSingleton()
    {
        return Injector::inst()->get($this->getForeignClassName());
    }

    /**
     * @param boolean $withID
     * @return string
     */
    protected function getCalculatedFieldName($withID = false)
    {
        return "ERROR IN getCalculatedFieldName, please make sure this method has been extended in your class.";
    }

    /**
     * @return boolean
     */
    protected function hasEmptyStringForSelection()
    {
        return "ERROR IN hasEmptyStringForSelection, please make sure this method has been extended in your class.";
    }


    public function FieldHolder($properties = array())
    {
        $children = $this->getChildren();
        if ($children->count()) {
            if ($rightTitleField = $this->getRightTitleField()) {
                $this->insertBefore($rightTitleField, $children->First()->id());
            }
            $this->insertBefore($this->getHeadingField(), $children->First()->id());

            return parent::FieldHolder($properties = array());
        }
        return new HiddenField("no field for ".$this->getCalculatedFieldName());
    }

    /**
     *
     * @return FormField
     */
    protected function existingSelectorField()
    {
        $fieldTypeClassName = $this->getSelectExistingFieldClassName();
        $source = null;
        $existingSelectorField = null;
        if (isset($this->existingSelectors[$this->getCalculatedFieldName(true)])) {
            $source = $this->existingSelectors[$this->getCalculatedFieldName(true)];
        } else {
            $foreignSingleton = $this->getForeignSingleton();
            if ($foreignSingleton->hasExtension('FrontEndDataExtension')) {
                $source = $foreignSingleton->FrontEndSiblings($this->recordBeingEdited->FrontEndRootParentObject(), true);
                $newSource = array();
                foreach ($source as $obj) {
                    $newSource[$obj->ID] = $obj->FrontEndShortTitle();
                }
                $source = $newSource;
                //print_r($source->sql());
                //echo "<hr />";
            }
        }
        if ($source) {
            $dropdownSource = null;
            if ($source && $source instanceof FormField) {
                $existingSelectorField = $source;
                //do nothing
            } elseif ($source && $source instanceof SS_Map) {
                if ($source->count()) {
                    $dropdownSource = $source->toArray();
                }
            } elseif ($source && $source instanceof SS_List) {
                if ($source->count()) {
                    $dropdownSource = $source->map()->toArray();
                }
            } elseif ($source && is_array($source)) {
                $dropdownSource = $source;
            }
            if ($dropdownSource && count($dropdownSource)) {
                $fieldName = $this->getCalculatedFieldName(true);
                $fieldNameWithoutID = $this->getCalculatedFieldName(false);
                $currentValues = $this->recordBeingEdited->$fieldNameWithoutID();
                if ($currentValues instanceof DataObject) {
                    $defaultValue = $currentValues->ID;
                    $title = _t("FrontEndEditor.CHANGE_EXISTING", "");
                } else {
                    if ($currentValues instanceof SS_List) {
                        $defaultValue = $currentValues->map("ID", "ID")->toArray();
                    } elseif ($currentValues instanceof SS_Map) {
                        $defaultValue = $currentValues->map("ID", "ID")->toArray();
                    }
                    if (is_array($defaultValue) && is_array($defaultValue)) {
                        $dropdownSource = array_diff_key($dropdownSource, $defaultValue);
                    }
                    $title = _t("FrontEndEditor.ADD_NEW", "add existing");
                }
                if (count($dropdownSource)) {
                    if ($fieldTypeClassName != "CheckboxOptionSetField") {
                        $className = $this->getForeignClassName();
                        if ($fieldTypeClassName != "DropdownField") {
                            foreach ($dropdownSource as $id => $value) {
                                $object = $className::get()->byID($id);
                                if ($object) {
                                    $dropdownSource[$id] = DBField::create_field('HTMLText', "<a href=\"".$object->FrontEndEditLink()."\">".$value."</a>");
                                }
                            }
                        }
                    }
                    $existingSelectorField = $fieldTypeClassName::create(
                        $fieldName,
                        $title,
                        $dropdownSource
                    );
                    if ($this->hasEmptyStringForSelection()) {
                        $existingSelectorField->setEmptyString("-- SELECT --");
                    }
                    if ($defaultValue) {
                        $existingSelectorField->setValue($defaultValue);
                    }
                }
            }
        }
        return $existingSelectorField;
    }

    public function setRightTitle($title)
    {
        parent::setRightTitle($title);
        $this->fieldHolderIsDone = false;
    }
}
