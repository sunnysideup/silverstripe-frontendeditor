<?php

namespace SunnySideUp\FrontendEditor;

use Page;



class FrontEndEditorPage extends Page
{
    private static $icon = "frontendeditor/images/treeicons/FrontEndEditorPage";

    private static $description = "";

    public function canCreate($member = null, $context = [])
    {
        return FrontEndEditorPage::get()->count() ? false : true;
    }


    /**
     * @param DataObject $recordBeingEdited
     * @param string $relationName
     * @param int $foreignID
     *
     * @return string
     *
     */
    public function FrontEndRemoveRelationLink($recordBeingEdited, $relationName, $foreignID)
    {
        return $this->Link(
            "frontendremoverelation/".
            $recordBeingEdited->ClassName."/".
            $recordBeingEdited->ID."/".
            "?goingto=".$relationName.",".$foreignID
        );
    }

    /**
     * @param DataObject $recordBeingEdited
     * @param string $relationName
     *
     * @return string
     */
    public function FrontEndAddRelationLink($recordBeingEdited, $relationName)
    {
        return $this->Link(
            "frontendaddrelation/".
            $recordBeingEdited->ClassName."/".
            $recordBeingEdited->ID."/".
            "?goingto=".$relationName
        );
    }

    /**
     * @param DataObject $recordBeingEdited
     *
     * @return string
     */
    public function FrontEndEditLink($recordBeingEdited)
    {
        if ($recordBeingEdited->ID) {
            return $this->FrontEndEditLinkFast(
                $recordBeingEdited->ClassName,
                $recordBeingEdited->ID
            );
        }
    }

    /**
     * Link to edit item.
     * @param string $className
     * @param int $id
     *
     * @return string
     */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    public function FrontEndEditLinkFast($className, $id)
    {
        return $this->Link(
            'edit/'.

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            $className.'/'.
            $id
        );
    }
}

