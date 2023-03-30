<?php

namespace SunnySideUp\FrontendEditor\Forms\Fields;



use SilverStripe\Forms\CheckboxSetField;
use SunnySideUp\FrontendEditor\Model\FrontEndDataExtension;
use SilverStripe\ORM\SS_List;
use SilverStripe\Forms\LiteralField;



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
    protected $selectExistingFieldClassName = CheckboxSetField::class;


    public function __construct($name, $title)
    {
        $this->hasManyField = $name;
        return parent::__construct($name, $title);
    }

    /**
     *
     * @param string $hasManyClassName
     *
     * @return FrontEndExtendedHasManyField
     */
    public function setHasManyClassName($hasManyClassName)
    {
        $this->hasManyClassName = $hasManyClassName;

        return $this;
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
     * @return bool
     */
    protected function hasEmptyStringForSelection()
    {
        return false;
    }


    public function FieldHolder($properties = [])
    {
        if (!$this->fieldHolderIsDone) {
            $this->fieldHolderIsDone = true;
            $hasManyField = $this->getCalculatedFieldName();
            $hasManyFieldWithID = $this->getCalculatedFieldName(true);
            $hasManyClassName = $this->getForeignClassName();
            //if object exists:
            if ($this->recordBeingEdited) {
                $hasManyObjectSingleton = $this->getForeignSingleton();
                if ($hasManyObjectSingleton->hasExtension(FrontEndDataExtension::class)) {
                    $customRelationFields = $this->recordBeingEdited->FrontEndCustomRelationsOptionProvider();
                    if (
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
                                $deleteAlternatives = $this->recordBeingEdited->FrontEndDeleteAlternatives();
                                //note the difference between NULL and FALSE
                                $deleteAlternative = isset($deleteAlternatives[$hasManyField]) ? $deleteAlternatives[$hasManyField] : null;
                                if ($deleteAlternative !== false) {
                                    if ($hasManyObject->canDelete()) {
                                        $deleteLink = "<a class=\"frontEndRemoveLink\" href=\"".$this->recordBeingEdited->FrontEndRemoveRelationLink($hasManyField, $hasManyObject->ID)."\">✗</a>";
                                    }
                                }
                                $this->push(
                                    LiteralField::create(
                                        $hasManyField."_EDIT_".$hasManyObject->ID,
                                        "<h5 class=\"frontEndEditAndRemoveLinks\" id=\"EDIT_AND_REMOVE_LINK_HEADING_".$hasManyObject->ClassName."_".$hasManyObject->ID."\">
                                            ".$deleteLink."
                                            <a class=\"frontEndEditLink\" href=\"".$hasManyObject->FrontEndEditLink()."\">".$hasManyObject->FrontEndEditIcon()." ".$hasManyObject->FrontEndShortTitle()."</a>
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
        return parent::FieldHolder($properties = []);
    }
}

