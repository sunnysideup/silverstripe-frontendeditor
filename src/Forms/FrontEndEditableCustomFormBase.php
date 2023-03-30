<?php

namespace SunnySideUp\FrontendEditor\Forms;

use DataObject;
use FrontEndEditable;
use FieldList;
use LiteralField;




class FrontEndEditableCustomFormBase extends DataObject implements FrontEndEditable
{

    /**
     *
     * @param  FrontEndEditable            $parentObject DataObject
     * @param  string                      $className    ClassName to create
     * @param  array                       $allParams
     * @return FrontEndEditable
     */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    public static function add_another($rootParentObject, $className, $sibling = null, $params)
    {
        return null;
    }


    public function getFrontEndFields($params = null)
    {
        $fields = FieldList::create();
        $fields->push(
            LiteralField::create(
                'Intro',
                'tba'
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFrontEndValidator()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function FrontEndFieldsWithAjaxValidation()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function FrontEndShortTitle()
    {
        return 'Sequence Editor';
    }

    /**
     * {@inheritDoc}
     */
    public function FrontEndExtendedTitle()
    {
        return 'Please review details below';
    }


    /**
     * {@inheritDoc}
     */
    public function FrontEndHeaders()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function ExtraClassesForFrontEnd()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function ExtraClassesForFrontEndForm()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function RightTitlesForFrontEnd()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function PlaceHoldersForFrontEnd()
    {
        return [];
    }


    /**
     * {@inheritDoc}
     */
    public function FieldsToRemoveFromFrontEnd()
    {
        return [];
    }


    /**
     * {@inheritDoc}
     */
    public function FrontEndCustomRelationFields()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function FrontEndCustomRelationsOptionProvider()
    {
        return [];
    }

    /**
     * list of fields that should be made read-only ...
     * e.g. the preset Title
     * @return array
     */
    public function FrontEndMakeReadonlyFields()
    {
        return [];
    }


    /**
     * list of fields foreign relations that are not deleted
     * but where a field is set to false or something similar
     * @return array
     * e.g.
     * MyChildren = array("ShowInMenus" => 0, "ShowInSearch" => 0)
     * MySpecialDataObject = array("Archive" => 1)
     *
     * where `MyChildren` and `MySpecialDataObject` are relations
     *
     * Instead of a field, you can also add a method (e.g. doDeletionDifferently)
     * the value is passed to this method, unless it is TRUE (strict check). e.g.
     *     MyChildren(
     *         'doDeletionDifferently' => true,
     *     )
     *
     * The method overrides the field.
     *
     * If you do not want to show the delete function then you can return:
     *    return array(
     *        MyChildren = false
     *    );
     *
     *
     * @return array
     */
    public function FrontEndDeleteAlternatives()
    {
        return [];
    }

    /**
     * returns the parent dataobject
     * for the formation of breadcrumbs in the front-end editor
     *
     * @return null | DataObject
     */
    public function FrontEndParentObject()
    {
        return null;
    }

    /**
     * returns the parent dataobject
     * for the formation of breadcrumbs in the front-end editor
     * the most common usage is like this:
     * <code>
     * ```php
     * 			public function FrontEndSiblings($rootParent = null, $includeMe = true) {
     * 				return $this->FrontEndDefaultSiblings($rootParent, $includeMe);
     * 			}
     * ```
     * </code>
     * @param null | DataObject $rootParent
     * @param null | boolean $includeMe
     *
     * @return null | DataList
     */
    public function FrontEndSiblings($rootParent = null, $includeMe = true)
    {
        return null;
    }

    /**
     * fields that can be edited right across siblings.
     * returns as field list
     * Any field that is set to read-only will also be excluded.
     *
     * e.g.
     * - MyField1
     * - MyField2
     * @return array
     */
    public function FrontEndNoSiblingEdits()
    {
        return [];
    }

    /**
     * Additional Views can be added here.  Some are added by default, but you can add
     * other ones, such as preview, etc...
     * It is provided the default array, which it can change OR add to
     * the array should look like this (standard PHP Array)
     *     [
     *         NameOfLink: [
     *             Title
     *             Link
     * -           Description
     *         ]
     *     ]
     *
     * @param array
     *
     * @return array
     */
    public function FrontEndAlternativeViewLinks($array)
    {
        return [];
    }

    /**
     * @param Form
     */
    // public function FinalUpdateFrontEndForm($form)


    /**
     * return a code or HTML snippet
     *
     * @return string (html)
     */
    public function FrontEndEditIconCode()
    {
        return '';
    }

    /**
     * return an HTML colour
     * e.g. "GREEN" or "#223223"
     *
     * @return string
     */
    public function FrontEndEditColour()
    {
        return '';
    }
}

