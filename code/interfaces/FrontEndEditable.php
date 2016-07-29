<?php



interface FrontEndEditable {

	/**
	 * required fields for front end...
	 * @return null | RequiredFields
	 */
	public function getFrontEndValidator();
	
	/**
	 * the short title for the object
	 * @return string
	 */
	public function FrontEndShortTitle();

	/**
	 * more detailed title to explain the specific record you are dealing with
	 * make it return an empty string if no extended title is required...
	 * @return string
	 */
	public function FrontEndExtendedTitle();


	/**
	 * inserts Fields before FieldName
	 * so that you can create headers
	 * FieldName => Field
	 *
	 * @return array
	 */
	public function FrontEndHeaders();

	/**
	 * format:
	 * FieldName => class
	 *
	 * @return array
	 */
	public function ExtraClassesForFrontEnd();

	/**
	 * format:
	 * FieldName => HTML Help Message
	 *
	 * @return array
	 */
	public function RightTitlesForFrontEnd();

	/**
	 * format:
	 * FieldName => placeholder value
	 *
	 * @return array
	 */
	public function PlaceHoldersForFrontEnd();


	/**
	 * format:
	 *  - FieldNameA,
	 *  - FieldNameB, etc..
	 *
	 * @return array
	 */
	public function FieldsToRemoveFromFrontEnd();


	/**
	 * list of relations that
	 * should not be created automatically...
	 * @return array
	 */
	public function FrontEndCustomRelationFields();

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
	 * @return array
	 */
	public function FrontEndCustomRelationsOptionProvider();

	/**
	 * list of fields that should be made read-only ...
	 * e.g. the preset Title
	 * @return array
	 */
	public function FrontEndMakeReadOnlyFields();


	/**
	 * list of fields foreign relations that are not deleted
	 * but where a field is set to false or something similar
	 * @return array
	 * e.g.
	 * MyChildren = array("ShowInMenus" => 0, "ShowInSearch" => 0)
	 * MySpecialDataObject = array("Archive" => 1)
	 */
	public function FrontEndDeleteAlternatives();

	/**
	 * returns the parent dataobject
	 * for the formation of breadcrumbs in the front-end editor
	 *
	 * @return null | DataObject
	 */
	public function FrontEndParentObject();

	/**
	 * returns the parent dataobject
	 * for the formation of breadcrumbs in the front-end editor
	 * the most common usage is like this:
	 * ```php
	 * 			public function FrontEndSiblings($rootParent = null, $includeMe = true) {
	 * 				return $this->FrontEndDefaultSiblings($rootParent, $includeMe);
	 * 			}
	 * ```
	 * @param null | DataObject $rootParent
	 * @param null | boolean $includeMe
	 * 
	 * @return null | DataList
	 */
	public function FrontEndSiblings($rootParent = null, $includeMe = true);

	/**
	 * fields that can be edited right across siblings.
	 * returns as field list
	 * Any field that is set to read-only will also be excluded.
	 * 
	 * e.g.
	 * - MyField1
	 * - MyField2
	 * @array
	 */ 
	public function FrontEndNoSiblingEdits();



}
