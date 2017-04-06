<?php

class FrontEndExtendedHasManyField extends FrontEndExtendedHasOneOrManyField
{

    /**
     * e.g. MyObjects
     * @var string
     */
    protected $hasManyField = "";

    /**
     * e.g. MyForeignObjectClass
     * @var string
     */
    protected $hasManyClassName = "";

    /**
     *
     * @var boolean
     */
    protected $relationIsBeingSaved = false;

    /**
     * @var string
     */
    protected $selectExistingFieldClassName = "CheckboxSetField";


    public function __construct($name, $title)
    {
        $this->hasManyField = $name;
        return parent::__construct($name, $title);
    }

    public function setHasManyClassName($hasManyClassName)
    {
        $this->hasManyClassName = $hasManyClassName;
    }


    public function getForeignClassName()
    {
        return $this->hasManyClassName;
    }


    protected function getCalculatedFieldName($withID = false)
    {
        return $this->hasManyField;
    }

    public function getRelationIsBeingSaved()
    {
        return $this->relationIsBeingSaved;
    }

    /**
     * @return boolean
     */
    protected function hasEmptyStringForSelection()
    {
        return false;
    }


    public function FieldHolder($properties = array())
    {
        if (!$this->fieldHolderIsDone) {
            $this->fieldHolderIsDone = true;
            $hasManyField = $this->getCalculatedFieldName();
            $hasManyFieldWithID = $this->getCalculatedFieldName(true);
            $hasManyClassName = $this->getForeignClassName();
            //if object exists:
            if($this->recordBeingEdited) {
                $hasManyObjectSingleton = $this->getForeignSingleton();
                if ($hasManyObjectSingleton->hasExtension('FrontEndDataExtension')) {
                    $customRelationFields = $this->recordBeingEdited->FrontEndCustomRelationsOptionProvider();
                    if(
                        isset($customRelationFields[$hasManyField]) &&
                        $customRelationFields[$hasManyField] instanceof SS_List
                    ) {
                        $hasManyObjects = $customRelationFields[$hasManyField];
                    } else {
                        $hasManyObjects = $this->recordBeingEdited->$hasManyField();
                    }
                    if ($hasManyObjects && $hasManyObjects->count()) {
                        foreach ($hasManyObjects as $hasManyObject) {
                            if ($hasManyObject->canEdit()) {
                                $deleteLink = "";
                                if ($hasManyObject->canDelete()) {
                                    $deleteLink = "<a class=\"frontEndRemoveLink\" href=\"".$this->recordBeingEdited->FrontEndRemoveRelationLink($hasManyField, $hasManyObject->ID)."\">✗</a>";
                                }
                                $this->push(
                                    LiteralField::create(
                                        $hasManyField."_EDIT_".$hasManyObject->ID,
                                        "<h5 class=\"frontEndEditAndRemoveLinks\" id=\"EDIT_AND_REMOVE_LINK_HEADING_".$hasManyObject->ClassName."_".$hasManyObject->ID."\">
                                            ".$deleteLink."
                                            <a class=\"frontEndEditLink\" href=\"".$hasManyObject->FrontEndEditLink()."\"><span>&#9998;</span> ".$hasManyObject->FrontEndShortTitle()."</a>
                                            <div class=\"extendedDescriptionForRelation\">".$hasManyObject->FrontEndExtendedTitle()."</div>
                                        </h5>"
                                    )
                                );
                            }
                        }
                    }
                    if ($hasManyObjectSingleton->canCreate()) {
                        $this->push(
                            LiteralField::create(
                                $hasManyField."_ADD",
                                "<h5 class=\"frontEndAddLink\" id=\"ADD_LINK_HEADING".$hasManyObjectSingleton->ClassName."\">
                                    <a href=\"".$this->recordBeingEdited->FrontEndAddRelationLink($hasManyField)."\"><span>[+]</span> "._t("FrontEndEdtior.ADD", "add")." ".$this->Title()."</a>
                                </h5>"
                            )
                        );
                    }
                }
                //temporary hack to stop read-only ones from being added.
                if ($hasManyObjectSingleton->canCreate()) {
                    if ($existingSelectorField = $this->existingSelectorField()) {
                        $this->push($existingSelectorField);
                        $this->relationIsBeingSaved = true;
                    }
                }
            }
        }
        return parent::FieldHolder($properties = array());
    }
}
