<?php


class FrontEndDataExtension extends DataExtension {

    public static $db = array(
        "FrontEndRootCanEditObject" => "Varchar(150)"
    );

    public static $has_one = array(
        "FrontEndEditor" => "Member"
    );

    public static $indexes = array(
        "FrontEndRootCanEditObject" => true
    );

    public function FieldsToRemoveFromFrontEndDefaults(){
        return array(
            "FrontEndEditor",
            "FrontEndRootCanEditObject",
            "URLSegment",
            "ExtraMeta",
            "ShowInMenus",
            "ShowInSearch",
            "HasBrokenFile",
            "HasBrokenLink",
            "Sort",
            "ReportClass",
            "CanViewType",
            "CanEditType",
            "Version",
            "AlsoShowProducts",
            "BackLinkTracking",
            "LinkTracking",
            "ImageTracking",
            "ViewerGroups",
            "EditorGroups"
        );
    }

    function updateCMSFields(FieldList $fields){

        $link = $this->owner->FrontEndEditLink();
        if($link) {
            $fields->addFieldToTab(
                'Root.FrontEnd',
                $field1 = ReadonlyField::create(
                    "EditOnTheFrontEnd",
                    "Front-End",
                    "<a target=\"_blank\" href=\"".$link."\">Open <em>".$this->owner->getTitle()."</em></a>"
                )
            );
            $field1->dontEscape = true;
        }
        //$fields->removeByName("FrontEndRootCanEditObject");
        $rootParentObject = $this->FrontEndRootParentObject();
        if($rootParentObject && ($rootParentObject->ID != $this->owner->ID ||  $rootParentObject->ClassName != $this->owner->ClassName)) {
            $fields->addFieldToTab(
                'Root.FrontEnd',
                $field2 = ReadonlyField::create(
                    "ParentRootCanEdit",
                    "Root",
                    "<a target=\"_blank\" href=\"".$rootParentObject->CMSEditLink()."\">Open <em>".$rootParentObject->getTitle()."</em></a>")
            );
            $field2->dontEscape = true;
        }
        $fields->removeFieldFromTab("Root.Main", "FrontEndRootCanEditObject");
    }


    public function updateSettingsFields($fields) {
        $fields->addFieldsToTab('Root.FrontEnd', array(
            new GridField(
                "RightTitleEditor",
                _t("FrontEndEditor.FIELD_EXPLANATIONS", "Field Explanations"),
                $this->owner->FrontEndRightTitleObjects(),
                $config = GridFieldConfig_RecordEditor::create()
            ),
            new LiteralField("FrontEndRootCanEditObjectLink", "
                <h2>".
                    _t("FrontEndDataExtension.RELATES_TO", "Relates to")."
                    <a href=\"".$this->FrontEndRootParentObject()->FrontEndEditLink()."\">".$this->FrontEndRootParentObject()->FrontEndShortTitle()."</a>
                </h2>
                <p>".$this->FrontEndRootParentObject()->FrontEndExtendedTitle()."</p>"
            )
        ));
    }

    /**
     *
     * @return string | null
     */
    public function FrontEndEditLink() {
        $page = FrontEndEditorPage::get()->first();
        if($page) {
            return $page->Link("edit/".$this->owner->ClassName."/".$this->owner->ID."/");
        }
        elseif($this->owner->hasMethod("CMSEditLink")) {
            return $this->owner->CMSEditLink();
        }
    }

    function FrontEndRightTitleObjects(){
        return FrontEndEditorRightTitle::get()->filter(array("ObjectClassName" => $this->owner->ClassName));
    }

    public function onBeforeWrite(){
        $frontEndRootParentObjectAsString = $this->owner->FrontEndRootParentObjectAsString();
        //debug::log("---".$frontEndRootParentObjectAsString);
        if($this->owner->FrontEndRootCanEditObject != $frontEndRootParentObjectAsString) {
            $this->owner->FrontEndRootCanEditObject = $frontEndRootParentObjectAsString;
        }
        //to complete
        if($this->owner->ClassName == Config::inst()->get("FrontEndEditorPage_Controller", "default_model")) {

        }
        $this->FrontEndEditorID = Member::currentUserID();
    }

    function canCreate($member) {
        if($this->owner->ClassName == Config::inst()->get("FrontEndEditorPage_Controller", "default_model")) {
            return true;
        }
    }

    function canView($member) {
        if($this->owner->FrontEndRootCanEditObject == FrontEndEditorSessionManager::get_root_can_edit_object_string()) {
            return true;
        }
    }

    function canEdit($member) {
        if($this->owner->FrontEndRootCanEditObject == FrontEndEditorSessionManager::get_root_can_edit_object_string()) {
            return true;
        }
    }

    /**
     * uses MyModel::$required_fields
     * @param ValidationResult
     */
    public function validate(ValidationResult $validationResult) {
        $requiredFields = Config::inst()->get($this->owner->class, 'required_fields', Config::INHERITED);
        if($requiredFields) {
            foreach($requiredFields as $name) {
                $error = false;
                if($this->owner->hasMethod($name)) {
                    $object = $this->owner->$name();
                    if(! $object->exists()) {
                        $error = true;
                    }
                }
                else if(! $this->owner->$name) {
                    $error = true;
                }
                if($error) {
                    $label = $this->owner->fieldLabel($name);
                    $errorMessage = _t(
                        'Form.FIELDISREQUIRED',
                        '{name} is required',
                        array(
                            'name' => strip_tags(
                                '"' .$label . '"'
                            )
                        )
                    );
                    $validationResult->error($errorMessage, $name);
                }
            }
        }

    }

    /**
     *
     * @return ArrayList
     */
    public function FrontEndEditorBreadCrumbs(){
        $array = array();
        $al = ArrayList::create();
        $array[] = $this->owner;
        if($this->owner->hasMethod("FrontEndParentObject")) {
            $parent = $this->owner->FrontEndParentObject();
            while($parent) {
                $array[$parent->ClassName."-".$parent->ID] = $parent;
                $parent = $parent->FrontEndParentObject();
            }
        }
        $array = array_reverse($array);
        foreach($array as $object) {
            if($object->getTitle()) {
                $al->push($object);
            }
        }
        return $al;
    }

    function FrontEndRemoveRelationLink($relationField, $foreignID) {
        return FrontEndEditorPage::get()->first()->FrontEndRemoveRelationLink($this->owner, $relationField, $foreignID);
    }

    function FrontEndAddRelationLink($relationField) {
        return FrontEndEditorPage::get()->first()->FrontEndAddRelationLink($this->owner, $relationField);
    }

    private static $_front_end_root_parent_object = array();

    /**
     *
     * @return DataObject
     */
    function FrontEndRootParentObject(){
        $uid = $this->owner->ClassName."-".$this->owner->ID;
        if(!isset(self::$_front_end_root_parent_object[$uid])) {
            $returnObject = $this->owner;
            if($this->owner->hasMethod("FrontEndParentObject")) {
                $parent = $this->owner->FrontEndParentObject();
                $x = 0;
                while($parent && $x < 99 && $parent->hasMethod("FrontEndParentObject")) {
                    $x++;
                    $returnObject = $parent;
                    //debug::log($parent->ClassName.$parent->ID."-------asdf-----");
                    $parent = $parent->FrontEndParentObject();
                }
            }
            self::$_front_end_root_parent_object[$uid] = $returnObject;
        }
        return self::$_front_end_root_parent_object[$uid];
    }

    /**
     *
     * @return DataObject
     */
    function FrontEndRootParentObjectAsString(){
        $obj = $this->owner->FrontEndRootParentObject();
        if($obj && $obj->ID) {
            return $obj->ClassName.",".$obj->ID;
        }
    }

    function FrontEndShortAndExtendedTitle(){
        return $this->owner->FrontEndShortTitle()." - ".$this->owner->FrontEndExtendedTitle();
    }

    /**
     * records within the same "edit group"
     * in or excluding the current record.
     * @return Datalist
     */
    function FrontEndDefaultSiblings($rootParent = null, $includeMe = false){
        $className = $this->owner->ClassName;
        if($rootParent) {
            $rootObjectAsString = $rootParent->FrontEndRootParentObjectAsString();
        }
        else {
            $rootObjectAsString = $this->owner->FrontEndRootParentObjectAsString();
        }
        $list = $className::get()->filter(array("FrontEndRootCanEditObject" => $rootObjectAsString));
        if(!$includeMe) {
            $list = $list->exclude(array("ID" => $this->owner->ID));
        }
        if(!$rootObjectAsString) {
            $list = $list->filter(array("ID" => 0));
        }
        return $list;
    }

    function requireDefaultRecords(){
        $fieldLabels = $this->owner->FieldLabels();
        $rightTitles = Config::inst()->get($this->owner->ClassName, "field_labels_right");
        if(!is_array($rightTitles)) {
            $rightTitles = array();
        }
        $rightTitles = array_merge($rightTitles, $this->owner->RightTitlesForFrontEnd());
        $fieldsToRemoveFromFrontEnd = array_merge(
            (array) $this->owner->FieldsToRemoveFromFrontEnd(),
            (array) $this->owner->FieldsToRemoveFromFrontEndDefaults()
        );
        foreach($fieldLabels as $fieldName => $fieldLabel) {
            if(!in_array($fieldName, $fieldsToRemoveFromFrontEnd)) {
                DB::alteration_message("Adding right title for ".$this->owner->ClassName.".".$fieldName);
                $obj = FrontEndEditorRightTitle::add_or_find_item(
                    $this->owner->ClassName,
                    $fieldName,
                    isset($rightTitles[$fieldName]) ? $rightTitles[$fieldName] : ""
                );
            }
        }

    }


}
