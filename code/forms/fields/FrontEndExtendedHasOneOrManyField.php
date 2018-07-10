<?php

abstract class FrontEndExtendedHasOneOrManyField extends CompositeField
{


    /**
     * @var null | DataObject
     */
    protected $recordBeingEdited = null;

    /**
     * @var mixed
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
     *
     * @return FrontEndExtendedHasOneOrManyField
     */
    public function setRecordBeingEdited($recordBeingEdited)
    {
        $this->recordBeingEdited = $recordBeingEdited;
        return $this;
    }

    /**
     *
     * @param array $existingSelectors
     *
     * @return FrontEndExtendedHasOneOrManyField
     */
    public function setExistingSelectors($existingSelectors)
    {
        $this->existingSelectors = $existingSelectors;
        return $this;
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
     * @param bool $withID
     * @return string
     */
    protected function getCalculatedFieldName($withID = false)
    {
        return "ERROR IN getCalculatedFieldName, please make sure this method has been extended in your class.";
    }

    /**
     * @return bool
     */
    protected function hasEmptyStringForSelection()
    {
        return "ERROR IN hasEmptyStringForSelection, please make sure this method has been extended in your class.";
    }


    public function FieldHolder($properties = [])
    {
        $children = $this->getChildren();
        if ($children->count()) {
            if ($rightTitleField = $this->getRightTitleField()) {
                $this->insertBefore($rightTitleField, $children->First()->id());
            }
            $this->insertBefore($this->getHeadingField(), $children->First()->id());

            return parent::FieldHolder($properties = []);
        }
        return HiddenField::create("no field for ".$this->getCalculatedFieldName());
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
        if ($this->existingSelectors) {
            $source = $this->existingSelectors;
        } else {
            $foreignSingleton = $this->getForeignSingleton();
            if ($foreignSingleton->hasExtension('FrontEndDataExtension')) {
                $source = $foreignSingleton->FrontEndSiblings($this->recordBeingEdited->FrontEndRootParentObject(), true);
                $newSource = [];
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
                    if ($source->first() instanceof FrontEndEditable) {
                        $dropdownSource = $source->map('ID', 'FrontEndShortTitle');
                        if ($dropdownSource instanceof SS_Map) {
                            $dropdownSource = $dropdownSource->toArray();
                        }
                    }
                }
            } elseif ($source && is_array($source)) {
                $dropdownSource = $source;
            }
            if ($dropdownSource && count($dropdownSource)) {
                foreach ($dropdownSource as $id => $sourceItem) {
                    if ($id && ! $sourceItem) {
                        $dropdownSource[$id] = "# ".$id;
                    }
                }
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
                    if (is_array($dropdownSource) && is_array($defaultValue)) {
                        $dropdownSource = array_diff_key($dropdownSource, $defaultValue);
                    }
                    $title = _t("FrontEndEditor.ADD_EXISTING", "add existing");
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

    /**
     *
     * @param string $title
     * @return FrontEndExtendedHasOneOrManyField
     */
    public function setRightTitle($title)
    {
        parent::setRightTitle($title);
        $this->fieldHolderIsDone = false;
        return $this;
    }
}
