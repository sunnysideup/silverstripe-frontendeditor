<?php


class FrontEndEditForm extends Form
{
    private static $allowed_actions = array(
        "createnew",
        "save",
        "saveandgoback",
        "saveandgonext",
        "saveandaddanother",
        "deleterecord"
    );

    protected $recordBeingEdited = null;

    protected $relationsBeingSaved = [];

    protected $isGoBack = false;

    protected $isGoNext = false;

    protected $isAddAnother = false;

    public function __construct($controller, $name, $recordBeingEdited)
    {
        $this->controller = $controller;
        //get the right record
        $this->recordBeingEdited = $recordBeingEdited;
        $this->addExtraClass($recordBeingEdited);
        if (!$this->recordBeingEdited) {
            $this->recordBeingEdited = FrontEndEditorSessionManager::get_current_record_being_edited();
        } elseif ($this->recordBeingEdited->getTitle()) {
            FrontEndEditorSessionManager::set_current_record_being_edited($this->recordBeingEdited);
        }

        $fieldLabels = $this->recordBeingEdited->fieldLabels(true);

        //starting point
        $fields = $this->recordBeingEdited->getFrontEndFields();
        $fields->unshift(
            LiteralField::create(
                'FrontEndEditIcon',
                '<div class="edit-tab">'.$this->recordBeingEdited->FrontEndEditIcon().'</div>'
            )
        );

        //dont add what we are later going to remove again...
        $removeFields = array_merge(
            (array) $this->recordBeingEdited->FieldsToRemoveFromFrontEnd(),
            (array) $this->recordBeingEdited->FieldsToRemoveFromFrontEndDefaults()
        );

        //add relations: we do this here so that we can tap into Controller!
        if ($this->recordBeingEdited->exists()) {
            $customRelationFields = $this->recordBeingEdited->FrontEndCustomRelationFields();
            $existingSelectors = $this->recordBeingEdited->FrontEndCustomRelationsOptionProvider();
            foreach ($this->recordBeingEdited->hasOne() as $hasOneField => $hasOneClassName) {
                $hasOneFieldWithID = $hasOneField."ID";
                $myExistingSelectors = null;
                $addExistingCustomKey = $hasOneFieldWithID.'_CAN_BE_ADDED';
                if (isset($existingSelectors[$addExistingCustomKey])) {
                    $myExistingSelectors = $existingSelectors[$addExistingCustomKey];
                }
                if (!in_array($hasOneField, $customRelationFields)) {
                    $fields->removeByName($hasOneFieldWithID);
                }
                if (in_array($hasOneField, $customRelationFields)  || in_array($hasOneField, $removeFields)) {
                    //do nothing
                } else {
                    $hasOneFieldObject = FrontEndExtendedHasOneField::create($hasOneField, $fieldLabels[$hasOneField]);
                    $hasOneFieldObject->setHasOneClassName($hasOneClassName);
                    $hasOneFieldObject->setRecordBeingEdited($this->recordBeingEdited);
                    $hasOneFieldObject->setExistingSelectors($myExistingSelectors);
                    $fields->push($hasOneFieldObject);
                }
            }

            //we do has_many last ...
            $otherRelations =
                (array) $this->recordBeingEdited->manyMany() +
                (array) Config::inst()->get($this->recordBeingEdited->ClassName, "belongs_many_many") +
                (array) $this->recordBeingEdited->hasMany();
            foreach ($otherRelations as $hasManyField => $hasManyClassName) {
                $myExistingSelectors = null;
                $addExistingCustomKey = $hasManyField.'_CAN_BE_ADDED';
                if (isset($existingSelectors[$addExistingCustomKey])) {
                    $myExistingSelectors = $existingSelectors[$addExistingCustomKey];
                }
                if (in_array($hasManyField, $customRelationFields) || in_array($hasManyField, $removeFields)) {
                    if (in_array($hasManyField, $customRelationFields)) {
                        $this->relationsBeingSaved[$hasManyField] = $hasManyField;
                    }
                    //do nothing
                } else {
                    $hasManyFieldObject = FrontEndExtendedHasManyField::create($hasManyField, $fieldLabels[$hasManyField]);
                    $hasManyFieldObject->setHasManyClassName($hasManyClassName);
                    $hasManyFieldObject->setRecordBeingEdited($this->recordBeingEdited);
                    $hasManyFieldObject->setExistingSelectors($myExistingSelectors);
                    $this->relationsBeingSaved[$hasManyField] = $hasManyField;
                    $fields->push($hasManyFieldObject);
                }
            }
        } else {
            $db = $this->recordBeingEdited->hasOne();
            foreach ($db as $field => $class) {
                unset($db[$field]);
                $db[$field."ID"] = $class;
            }
            $db += $this->recordBeingEdited->hasMany();
            $db += $this->recordBeingEdited->manyMany();
            $db += (array)$this->recordBeingEdited->stat("belongs_many_many");
            foreach ($fields as $field) {
                $fieldName = $field->getName();
                if (isset($db[$fieldName])) {
                    $fields->removeByName($fieldName);
                }
            }
        }

        //record description
        $recordDescription = FrontEndEditorClassExplanation::add_or_find_item(
            $this->recordBeingEdited->ClassName
        );
        if ($recordDescription->HasDescription()) {
            $fields->unshift(
            LiteralField::create(
                'Introduction',
                '<div class="form-intro">'.$recordDescription->BestDescription().'</div>'
                )
            );
        }

        //right titles ...
        $rightTitles = Config::inst()->get($this->recordBeingEdited->ClassName, "field_labels_right");
        if (! is_array($rightTitles)) {
            $rightTitles = [];
        }
        $rightTitles = array_merge($rightTitles, $this->recordBeingEdited->RightTitlesForFrontEnd());

        //add defaults for right titles
        foreach ($fields as $field) {
            if ($field->hasData()) {
                $fieldName = $field->ID();
                $obj = FrontEndEditorRightTitle::add_or_find_field(
                    $this->recordBeingEdited->ClassName,
                    $fieldName,
                    isset($rightTitles[$fieldName]) ? $rightTitles[$fieldName] : ''
                );
            }
        }

        //add data back in ...
        $rightTitles = array_merge($rightTitles, FrontEndEditorRightTitle::get_entered_ones($this->recordBeingEdited->ClassName));
        foreach ($rightTitles as $fieldName => $rightTitle) {
            $obj = FrontEndEditorRightTitle::add_or_find_field(
                $this->recordBeingEdited->ClassName,
                $fieldName
            );
            $field = $fields->fieldByName($fieldName);
            if ($field) {
                if ($field instanceof CheckboxField ||$field instanceof GridField) {
                    $field->setDescription($obj->BestDescription());
                } else {
                    $field->setRightTitle($obj->BestDescription());
                }
            }
        }

        //place holders
        $placeHolders = $this->recordBeingEdited->PlaceHoldersForFrontEnd();
        foreach ($placeHolders as $fieldName => $placeHolder) {
            $field = $fields->fieldByName($fieldName);
            if ($field) {
                $field->setAttribute("placeholder", $placeHolder);
            }
        }

        //sibling edits
        if (class_exists("DataObjectOneFieldUpdateController")) {
            $noSiblingEdits = $this->recordBeingEdited->FrontEndNoSiblingEdits();
            $readOnlyFields = $this->recordBeingEdited->FrontEndMakeReadOnlyFields();
            $siblingWhere = $this->recordBeingEdited->FrontEndSiblings(null, false);
            $siblingClassName = $siblingWhere->dataClass();
            $dbFields = $this->recordBeingEdited->db();
            if (count($dbFields) && class_exists($siblingClassName)) {
                $siblingArray = $siblingWhere->map("ID", "ID")->toArray();
                if (count($siblingArray)) {
                    foreach ($dbFields as $fieldName => $type) {
                        if (!in_array($fieldName, $noSiblingEdits) && !in_array($fieldName, $readOnlyFields)) {
                            $field = $fields->fieldByName($fieldName);
                            $tableName = $this->recordBeingEdited->baseTable();
                            $tableNameVersioned = $tableName;
                            if ($field && $tableName) {
                                //the below does not seem to be required ...
                                // if (is_a($this->recordBeingEdited, Object::getCustomClass("SiteTree"))) {
                                //     if (Versioned::current_stage() == "Live") {
                                //         //$tableNameVersioned .= "_Live";
                                //     }
                                // }
                                $siblingEditLink = DataObjectOneFieldUpdateController::popup_link(
                                    $siblingClassName,
                                    $fieldName,
                                    $where = "\"".$tableNameVersioned."\".\"ID\" IN (".implode(",", $siblingArray).")",
                                    $sort = '',
                                    $linkText = $field->Title(),
                                    "FrontEndShortAndExtendedTitle"
                                );
                                $field->setTitle($siblingEditLink);
                            }
                        }
                    }
                }
            }
        }

        //extra classes
        $ajaxClasses = $this->recordBeingEdited->FrontEndFieldsWithAjaxValidation();
        $extraClass = 'ajax-validation';
        foreach ($ajaxClasses as $fieldName => $method) {
            $field = $fields->dataFieldByName($fieldName);
            if ($field) {
                $field->addExtraClass($extraClass);
            }
        }

        //extra classes
        $extraClasses = $this->recordBeingEdited->ExtraClassesForFrontEnd();
        foreach ($extraClasses as $fieldName => $extraClass) {
            $field = $fields->dataFieldByName($fieldName);
            if ($field) {
                $field->addExtraClass($extraClass);
            }
        }


        //remove fields

        foreach ($removeFields as $removeField) {
            $fields->removeByName($removeField);
        }


        //add classname + ID
        $fields->push(new HiddenField("IDToUse", $this->recordBeingEdited->ID, $this->recordBeingEdited->ID));
        $fields->push(new HiddenField("ClassNameToUse", $this->recordBeingEdited->ClassName, $this->recordBeingEdited->ClassName));
        $fields->push(new HiddenField("RelationsBeingSaved", implode(",", $this->relationsBeingSaved), implode(",", $this->relationsBeingSaved)));


        //make readonly
        $readOnlyFieldNames = $this->recordBeingEdited->FrontEndMakeReadOnlyFields();
        if (is_array($readOnlyFieldNames) && count($readOnlyFieldNames)) {
            foreach ($readOnlyFieldNames as $readOnlyFieldName) {
                $readOnlyField = $fields->dataFieldByName($readOnlyFieldName);
                if ($readOnlyField) {
                    $fields->replaceField($readOnlyFieldName, $readOnlyField->performReadonlyTransformation());
                }
            }
        }


        //headers and special cases...
        $headerArray = [];
        $currentlyAddingTo = "";
        foreach ($fields as $field) {
            $fieldName = $field->ID();
            $headerStartsHere = isset($headers[$fieldName]) ? true : false;
            if ($headerStartsHere) {
                $headerIsStandard = (is_string($headers[$fieldName])) ? true : false;
            } else {
                $headerIsStandard = false;
            }
            if ($headerStartsHere && $headerIsStandard) {
                //save last one ...
                if (!isset($headerArray[$fieldName])) {
                    $headerArray[$fieldName] = [];
                    $currentlyAddingTo = $fieldName;
                }
            }
            if ($currentlyAddingTo) {
                $headerArray[$currentlyAddingTo][$fieldName] = $field;
            }

            if ($field instanceof CheckboxSetField) {
                //'adds ability to select none in checkboxsetfield'
                $fields->push(
                    HiddenField::create(
                        $field->getName().'[0]',
                        0,
                        0
                    )
                );
            }
        }
        foreach ($headerArray as $fieldName => $fieldArray) {
            $toggleField = ToggleCompositeField($fieldName."_HEADING", $headers[$fieldName]);
            unset($headers[$fieldName]);
            $fields->insertBefore($toggleField, $fieldName);
            foreach ($fieldArray as $fieldNameToRemove => $fieldToAdd) {
                $fields->removeByName($fieldNameToRemove);
                $toggleField->push($fieldToAdd);
            }
        }
        $headers = $this->recordBeingEdited->FrontEndHeaders();
        foreach ($headers as $insertBefore => $formField) {
            $fields->insertBefore($formField, $insertBefore);
        }


        //required fields
        $validator = $this->recordBeingEdited->getFrontEndValidator();
        if (!$validator) {
            $requiredFields = Config::inst()->get($this->recordBeingEdited->ClassName, 'required_fields');
            if ($requiredFields && is_array($requiredFields)) {
                $validator = RequiredFields::create($requiredFields);
            } else {
                $validator = [];
            }
        }
        //set colour to forms border ...
        $colour = $this->recordBeingEdited->FrontEndEditColour();
        $this->setAttribute(
            'style',
            'border-color: '.$colour.';'
        );
        //build!
        $actions = $this->selectActions();
        parent::__construct($controller, $name, $fields, $actions, $validator);
        Requirements::javascript(THIRDPARTY_DIR."/jquery-form/jquery.form.js");
        Requirements::javascript("frontendeditor/javascript/FrontEndEditForm.js");
        Requirements::customScript(
            "var FrontEndEditFormFormSelector = '#".$this->FormName()."'; ",
            "FrontEndEditFormFormSelector"
        );
        if (($this->recordBeingEdited && $this->recordBeingEdited->ID) || (isset($_GET["reusedata"]) && $_GET["reusedata"])) {
            $this->loadDataFrom($this->recordBeingEdited);
            $oldData = FrontEndEditorSessionManager::get_form_data();
            if ($oldData && (is_array($oldData) || is_object($oldData))) {
                $this->loadDataFrom($oldData);
            }
        }
        if ($recordBeingEdited && $recordBeingEdited->hasMethod('FinalUpdateFrontEndForm')) {
            $recordBeingEdited->FinalUpdateFrontEndForm($this);
        }
    }

    protected function selectActions()
    {

        //actions
        $actions = FieldList::create();

        if ($this->recordBeingEdited->exists()) {
            $actions->push(
                FormAction::create('save', _t("FrontEndEditForm.SAVE", "save"))
                    ->addExtraClass('save-button')
            );
        } else {
            $actions->push(
                FormAction::create('createnew', _t("FrontEndEditForm.CREATE", "create"))
                    ->addExtraClass('create-button add-button')
            );
        }

        if ($this->recordBeingEdited->canDelete()) {
            $actions->push(
                FormAction::create("deleterecord", _t("FrontEndEditForm.DELETE", "delete"))
                    ->addExtraClass('delete-button')
            );
        }

        //broken
        if ($this->HasPreviousPage()) {
            $actions->push(
                FormAction::create('saveandgoback', _t("FrontEndEditForm.SAVE_AND_GO_BACK", "save and go back"))
                    ->addExtraClass('save-and-go-prev-button prev-and-next')
            );
        }
        if ($this->HasNextPage()) {
            $actions->push(
                FormAction::create('saveandgonext', _t("FrontEndEditForm.SAVE_AND_NEXT", "save and go next"))
                    ->addExtraClass('save-and-go-next-button prev-and-next')
            );
        }
        if ($this->CanAddAnotherOfThisClass()) {
            $actions->push(
                FormAction::create('saveandaddanother', _t("FrontEndEditForm.SAVE_AND_NEXT", "save and add another"))
                    ->addExtraClass('save-and-add-another-button add-button')
            );
        }

        return $actions;
    }

    public function createnew($data, $form)
    {
        return $this->save($data, $form);
    }

    public function save($data, $form)
    {
        $controller = $this->controller;
        $this->retrieveRecordBeingEdited($data);
        if ($this->recordBeingEdited && $this->recordBeingEdited->canEdit()) {

            //start hack
            foreach ($this->recordBeingEdited->db() as $name => $type) {
                if (stripos($type, "oolean")) {
                    if (!isset($data[$name])) {
                        $this->recordBeingEdited->$name = false;
                    } else {
                        $this->recordBeingEdited->$name = true;
                    }
                } elseif ($type === 'Date') {
                    $value = DateField::create('MyDate')->setValue($data[$name])->dataValue();
                    $this->recordBeingEdited->$name = $value;
                } elseif (isset($data[$name])) {
                    if ($name != "ID" && $name != "ClassName") {
                        $this->recordBeingEdited->$name = $data[$name];
                    }
                }
            }
            $this->relationsBeingSaved = explode(",", $data["RelationsBeingSaved"]);
            foreach ($this->recordBeingEdited->hasOne() as $name => $type) {
                $name = $name."ID";
                if (isset($data[$name])) {
                    $this->recordBeingEdited->$name = (int)preg_replace("/[^0-9]/", "", $data[$name]);
                }
            }

            //save old data for future use
            FrontEndEditorSessionManager::set_form_data($data);

            // validate now ...
            // has to be doValidate
            $validationResult = $this->recordBeingEdited->doValidate();
            if ($validationResult && !$validationResult->valid()) {
                $form->sessionMessage("ERROR - Could not save data: ".$validationResult->message(), "bad");
                if (! $this->recordBeingEdited->ID) {
                    return $controller->redirect($controller->Link()."?reusedata=1");
                } else {
                    return $controller->redirectBack();
                }
            }

            FrontEndEditorSessionManager::clear_form_data();

            //more hack!
            //we can only add here, not remove...
            $manyMany = $this->recordBeingEdited->manyMany() + $this->recordBeingEdited->hasMany();
            foreach ($this->relationsBeingSaved as $relationName) {
                if ($relationName) {
                    if (isset($data[$relationName])) {
                        //special case ???
                        if (isset($data[$relationName]["GridState"])) {
                            //do nothing ..
                        } else {
                            if (isset($manyMany[$relationName])) {
                                $this->recordBeingEdited->$relationName()->removeAll();
                                // debug::log(print_r($data[$relationName], 1));
                            }
                            //remove any useless ones ...
                            unset($data[$relationName][0]);
                            $this->recordBeingEdited->$relationName()->addMany($data[$relationName]);
                        }
                    }
                }
            }
            //end hack
            if ($this->recordBeingEdited instanceof SiteTree) {
                $this->recordBeingEdited->writeToStage("Stage");
                $this->recordBeingEdited->Publish("Stage", "Live");
            } else {
                $this->recordBeingEdited->write();
            }
            $form->sessionMessage(_t("FrontEndEditor.SAVED", "Details have been saved."), "good");


            $ajaxGetVariable = "";
            if (Director::is_ajax()) {
                $ajaxGetVariable = "?ajax=".rand(0, 9999999999999999999);
            }

            if ($this->isGoBack) {
                if ($controller->HasSequence()) {
                    return $controller->redirect($controller->PreviousSequenceLink().$ajaxGetVariable);
                } else {
                    if ($previousObject = $this->previousObject()) {
                        $this->clearPreviousObject();
                        return $controller->redirect($previousObject->FrontEndEditLink().$ajaxGetVariable);
                    }
                }
            }

            if ($this->isGoNext) {
                if ($controller->HasSequence()) {
                    return $controller->redirect($controller->NextSequenceLink().$ajaxGetVariable);
                } else {
                    if ($nextObject = $this->nextObject()) {
                        return $controller->redirect($nextObject->FrontEndEditLink().$ajaxGetVariable);
                    }
                }
            } elseif ($this->isAddAnother) {
                if ($controller->HasSequence()) {
                    return $controller->redirect($controller->AddAnotherSequenceLink().$ajaxGetVariable);
                    if ($obj) {
                        return $controller->redirect($obj->FrontEndEditLink().$ajaxGetVariable);
                    }
                }
            }

            if ($this->recordBeingEdited->hasMethod('FrontEndEditLink')) {
                $controller->redirect($this->recordBeingEdited->FrontEndEditLink().$ajaxGetVariable);
            } else {
                $controller->redirectBack();
            }
        } else {
            $form->sessionMessage("Sorry, you do not have enough permissions to edit this record.", "good");
            $controller->redirectBack();
        }
    }


    public function deleterecord($data, $form)
    {
        $this->retrieveRecordBeingEdited($data);
        //die(" TO BE COMPLETED ");
        $rootParentObject = $this->recordBeingEdited->FrontEndRootParentObject();
        if ($this->recordBeingEdited->exists() || $this->recordBeingEdited->ID > 0) {
            if ($this->recordBeingEdited->canDelete()) {
                if ($this->recordBeingEdited instanceof SiteTree) {
                    $this->recordBeingEdited->deleteFromStage('Live');
                    $this->recordBeingEdited->deleteFromStage('Stage');
                } else {
                    $this->recordBeingEdited->delete();
                }
            }
            if ($rootParentObject) {
                return $this->controller->redirect($rootParentObject->FrontEndEditLink());
            }
        }
        die("Object can not be found ... ");
    }

    public function saveandaddanother($data, $form)
    {
        $this->isAddAnother = true;
        return $this->save($data, $form);
    }


    public function saveandgoback($data, $form)
    {
        $this->isGoBack = true;
        return $this->save($data, $form);
    }

    public function saveandgonext($data, $form)
    {
        $this->isGoNext = true;
        return $this->save($data, $form);
    }


    /**
     * is there another page to work through?
     *
     * @param  string|null $classNMame
     * @return bool
     */
    public function HasNextPage($className = null) : bool
    {
        if ($this->controller->HasSequence()) {
            return $this
                ->PreviousAndNextSequencer()
                ->HasNextPage($className);
        } else {
            return false;
            // return FrontEndEditorSessionManager::previous_object_based_on_browsing($this->recordBeingEdited);
        }
    }

    /**
     * is there a previous page to work through?
     *
     * @param  string|null $classNMame
     * @return bool
     */
    public function HasPreviousPage($className = null) : bool
    {
        if ($this->controller->HasSequence()) {
            return $this
                ->PreviousAndNextSequencer()
                ->HasPreviousPage($className);
        } else {
            return FrontEndEditorSessionManager::previous_object_based_on_browsing($this->recordBeingEdited) ? true : false;
        }
    }

    /**
     *
     * @return bool
     */
    public function CanAddAnotherOfThisClass()
    {
        if ($this->controller->HasSequence()) {
            $currentRecordIsNew = $this->recordBeingEdited->exists() ? false : true;
            return $this
                ->PreviousAndNextSequencer()
                ->CanAddAnotherOfThisClass($this->recordBeingEdited->ClassName, $currentRecordIsNew);
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function clearPreviousObject()
    {
        FrontEndEditorSessionManager::clear_previous_object_based_on_browsing();
    }


    // /**
    //  *
    //  * @return bool
    //  */
    // public function MustAddAnotherOfThisClass()
    // {
    //     if($this->controller->HasSequence()) {
    //         return $this
    //             ->controller
    //             ->PreviousAndNextSequencer()
    //             ->runOnSequencer(
    //                 'MustAddAnotherOfThisClass',
    //                 false,
    //                 $params = [$this->recordBeingEdited->ClassName]
    //             );
    //     }
    // }


    /**
     * @return FrontEndEditorPreviousAndNextProvider|null
     */
    public function PreviousAndNextProvider()
    {
        return $this->controller->PreviousAndNextProvider();
    }

    /**
     * @return FrontEndEditorPreviousAndNextSequencer|null
     */
    public function PreviousAndNextSequencer()
    {
        $provider = $this->controller->PreviousAndNextProvider();
        if ($provider) {
            return $provider->getSequencer();
        }
    }

    /**
     * retrieve the object being edited from the data
     * submitted in the form (or otherwise)
     * if expects two parameters to be set in the $data:
     * - IDToUse
     * - ClassNameToUse
     * if the ID is not set then it will create a new object
     *
     * @param array $data
     *
     * @return DataObject
     */
    protected function retrieveRecordBeingEdited($data)
    {
        //has it already been retrieved?
        if ($this->recordBeingEdited && $this->recordBeingEdited->exists() && $this->recordBeingEdited instanceof FrontEndEditable) {
            return $this->recordBeingEdited;
        }
        $id = 0;
        $className = "";
        if (isset($data["IDToUse"])) {
            $id = $data["IDToUse"];
        }
        if (isset($data["ClassNameToUse"])) {
            $className = $data["ClassNameToUse"];
        }
        if ($className) {
            if (class_exists($className)) {
                //find existing ...
                if ($id) {
                    $this->recordBeingEdited = $className::get()->byID($id);
                    if ($this->recordBeingEdited->hasExtension('FrontEndDataExtension')) {
                        return $this->recordBeingEdited;
                    }
                }
                //create new ...
                else {
                    $className = $data["ClassNameToUse"];
                    if ($className && class_exists($className)) {
                        $obj = Injector::inst()->get("Provider");
                        if ($obj->hasExtension('FrontEndDataExtension')) {
                            if ($obj->canCreate()) {
                                $this->recordBeingEdited = $className::create();
                                return $this->recordBeingEdited;
                            }
                        }
                    }
                }
            }
        }
        user_error("Form can not find object being edited...<pre>".print_r($data, 1)."</pre>");
    }
}
