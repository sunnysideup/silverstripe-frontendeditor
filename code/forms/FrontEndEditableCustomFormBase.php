<?php



class FrontEndEditableCustomFormBase extends DataObject implements FrontEndEditable
{

    /**
     *
     * @param  FrontEndEditable            $parentObject DataObject
     * @param  string                      $className    ClassName to create
     * @param  array                      $allParams    [description]
     * @return FrontEndEditable               [description]
     */
    public static function add_child_to_parent($parentObject, $className, $allParams) {
        return null;
    }


    public function getFrontEndFields($params = NULL)
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
     * required fields for front end...
     * @return null | RequiredFields
     */
    public function getFrontEndValidator()
    {
        return null;
    }

    /**
     * the short title for the object
     * @return string
     */
    public function FrontEndShortTitle()
    {
        return 'Sequence Editor';
    }

    /**
     * more detailed title to explain the specific record you are dealing with
     * make it return an empty string if no extended title is required...
     * @return string
     */
    public function FrontEndExtendedTitle()
    {
        return 'Please review details below';
    }


    /**
     * inserts Fields before FieldName
     * so that you can create headers
     * FieldName => Field
     *
     * @return array
     */
    public function FrontEndHeaders()
    {
        return [];
    }

    /**
     * format:
     * FieldName => class
     *
     * @return array
     */
    public function ExtraClassesForFrontEnd()
    {
        return [];
    }

    /**
     *
     * @return string
     */
    public function ExtraClassesForFrontEndForm()
    {
        return [];
    }

    /**
     * format:
     * FieldName => HTML Help Message
     *
     * @return array
     */
    public function RightTitlesForFrontEnd()
    {
        return [];
    }

    /**
     * format:
     * FieldName => placeholder value
     *
     * @return array
     */
    public function PlaceHoldersForFrontEnd()
    {
        return [];
    }


    /**
     * format:
     *  - FieldNameA,
     *  - FieldNameB, etc..
     *
     * @return array
     */
    public function FieldsToRemoveFromFrontEnd()
    {
        return [];
    }


    /**
     * list of relations that
     * should not be created automatically...
     * @return array
     */
    public function FrontEndCustomRelationFields()
    {
        return [];
    }

    /**
     * list of options for each relation that can be selected instead of adding a new one ...
     *
     * e.g.
     * MyHasOneRelation1ID => True ... uses FrontEndEditorSessionManager::editable_lists_based_on_can_edit
     * MyHasOneRelation1ID => DataList
     * MyHasOneRelation2ID => new DropdownField(.....)
     * MyHasManyRelation => new CheckboxSetField(...)
     * MyManyManyRelation => new DropdownField(.....)
     * MyBelongsManyManyRelation1 => SS_Map
     * MyBelongsManyManyRelation2 => SS_List
     *
     * add _CAN_BE_ADDED to the end of the relationship field name
     * to separately define the ones that can be added ...
     * e.g.
     *    MyHasOneRelation1ID_CAN_BE_ADDED
     *    MyHasManyRelation_CAN_BE_ADDED
     *
     *
     * @return array
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
    public function FrontEndMakeReadOnlyFields()
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
