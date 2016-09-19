<?php

class FrontEndExtendedHasOneField extends FrontEndExtendedHasOneOrManyField
{

    /**
     *
     * @var string
     */
    protected $hasOneField = "";

    /**
     *
     * @var string
     */
    protected $hasOneClassName = "";

    /**
     * is there the ability to set the selection back to "NONE"
     * @var boolean
     */
    protected $hasEmptyStringForSelection = true;

    /**
     * @var string
     */
    protected $selectExistingFieldClassName = "DropdownField";



    public function __construct($name, $title)
    {
        $this->hasOneField = $name;
        return parent::__construct($name, $title);
    }

    public function setHasOneClassName($hasOneClassName)
    {
        $this->hasOneClassName = $hasOneClassName;
    }

    public function getForeignClassName()
    {
        return $this->hasOneClassName;
    }

    protected function getCalculatedFieldName($withID = false)
    {
        $postFix = "";
        if ($withID) {
            $postFix = "ID";
        }
        return $this->hasOneField.$postFix;
    }

    /**
     * @return boolean
     */
    protected function hasEmptyStringForSelection()
    {
        return $this->hasEmptyStringForSelection;
    }



    public function FieldHolder($properties = array())
    {
        if (!$this->fieldHolderIsDone) {
            $this->fieldHolderIsDone = true;
            $fields = new CompositeField();
            $hasOneField = $this->getCalculatedFieldName();
            $hasOneFieldWithID = $this->getCalculatedFieldName(true);
            $hasOneClassName = $this->getForeignClassName();
            //if object exists:
            $hasOneObject = $this->recordBeingEdited->$hasOneField();
            $existingSelectorField = $this->existingSelectorField();
            if ($existingSelectorField) {
                $this->push($existingSelectorField);
            }
            if ($hasOneObject && $hasOneObject->exists()) {
                if ($hasOneObject->hasExtension('FrontEndDataExtension')) {
                    if ($hasOneObject->canEdit()) {
                        $this->push(
                            LiteralField::create(
                                $hasOneFieldWithID."_EDIT",
                                "<h5 class=\"frontEndEditAndRemoveLinks\"  id=\"EDIT_AND_REMOVE_LINK_HEADING_".$hasOneObject->ClassName."_".$hasOneObject->ID."\">
									<a class=\"frontEndRemoveLink\" href=\"".$this->recordBeingEdited->FrontEndRemoveRelationLink($hasOneField, $hasOneObject->ID)."\">✗</a>
									<a class=\"frontEndEditLink\" href=\"".$hasOneObject->FrontEndEditLink()."\"><span>&#9998;</span> ".$hasOneObject->FrontEndShortTitle()."</a>
									<div class=\"extendedDescriptionForRelation\">".$hasOneObject->FrontEndExtendedTitle()."</div>
								</h5>"
                            )
                        );
                    }
                }
            } else {
                $hasOneObject = $this->getForeignSingleton();
                if ($hasOneObject->canCreate()) {
                    if ($hasOneObject->hasExtension('FrontEndDataExtension')) {
                        $this->push(
                            LiteralField::create(
                                $hasOneFieldWithID."_ADD",
                                "<h5 class=\"frontEndAddLink\" id=\"ADD_LINK_HEADING_".$hasOneObject->ClassName."\">
									<a href=\"".$this->recordBeingEdited->FrontEndAddRelationLink($hasOneField)."\"><span>[+]</span> "._t("FrontEndEdtior.ADD", "add")." ".$this->Title()."</a>
								</h5>"
                            )
                        );
                    }
                }
            }
        }
        return parent::FieldHolder($properties = array());
    }
}
