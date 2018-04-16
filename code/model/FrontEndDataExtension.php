<?php


class FrontEndDataExtension extends DataExtension
{
    public static $db = array(
        "FrontEndRootCanEditObject" => "Varchar(150)"
    );

    public static $has_one = array(
        "FrontEndEditor" => "Member"
    );

    public static $indexes = array(
        "FrontEndRootCanEditObject" => true
    );

    public function FieldsToRemoveFromFrontEndDefaults()
    {
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

    public function updateCMSFields(FieldList $fields)
    {
        $link = $this->owner->FrontEndEditLink();
        if ($link) {
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
        if ($rootParentObject && (! $rootParentObject->FrontEndIsRoot())) {
            $fields->addFieldToTab(
                'Root.FrontEnd',
                $field2 = ReadonlyField::create(
                    "ParentRootCanEdit",
                    "Root",
                    "<a target=\"_blank\" href=\"".$rootParentObject->CMSEditLink()."\">Open <em>".$rootParentObject->getTitle()."</em></a>"
                )
            );
            $field2->dontEscape = true;
        }
        $fields->removeFieldFromTab("Root.Main", "FrontEndRootCanEditObject");
    }

    public function updateSettingsFields($fields)
    {
        $fields->addFieldsToTab(
            'Root.FrontEnd',
            array(
                GridField::create(
                    "ClassExplanation",
                    _t("FrontEndEditor.CLASS_EXPLANATIONS", "Class Explanations"),
                    ArrayList::create()->push($this->FrontEndEditorClassExplanation()),
                    $config = GridFieldConfig_RecordEditor::create()
                ),
                GridField::create(
                    "RightTitleEditor",
                    _t("FrontEndEditor.FIELD_EXPLANATIONS", "Field Explanations"),
                    $this->owner->FrontEndRightTitleObjects(),
                    $config = GridFieldConfig_RecordEditor::create()
                ),
                LiteralField::create(
                    "FrontEndRootCanEditObjectLink",
                    "
                    <h2>".
                        _t("FrontEndDataExtension.RELATES_TO", "Relates to")."
                        <a href=\"".$this->FrontEndRootParentObject()->FrontEndEditLink()."\">".$this->FrontEndRootParentObject()->FrontEndShortTitle()."</a>
                    </h2>
                    <p>".$this->FrontEndRootParentObject()->FrontEndExtendedTitle()."</p>"
                )
            )
        );
    }

    public function FrontEndEditorClassExplanation()
    {
        return FrontEndEditorClassExplanation::get()
            ->filter(
              ['ObjectClassName' => ($this->ClassName ? $this->ClassName : get_class($this->owner))]
            )
            ->first();
    }
    /**
     *
     * @return string | null
     */
    public function FrontEndEditLink()
    {
        $page = DataObject::get_one('FrontEndEditorPage');
        if ($page) {
            return $page->Link("edit/".$this->owner->ClassName."/".$this->owner->ID."/");
        } elseif ($this->owner->hasMethod("CMSEditLink")) {
            return $this->owner->CMSEditLink();
        }
    }

    /**
     * @return string (HTML)
     */
    public function FrontEndEditIcon($textOnly = false, $short = false)
    {
        $code = '';
        if ($short) {
            $code = '✎';
        } elseif ($this->owner->hasMethod('FrontEndEditIconCode')) {
            $code = '✎'.$this->owner->FrontEndEditIconCode();
        }
        $html = '<span class="frontend-edit-icon" style="color: '.$this->owner->FrontEndEditColour().'; border-color: '.$this->owner->FrontEndEditColour().'">'.$code.'</span>';
        if ($textOnly) {
            return strip_tags($html);
        } else {
            return $html;
        }
    }

    public function FrontEndRightTitleObjects()
    {
        return FrontEndEditorRightTitle::get()->filter(array("ObjectClassName" => $this->owner->ClassName));
    }

    public function onBeforeWrite()
    {
        //debug::log("---".$frontEndRootParentObjectAsString);
        $this->owner->FrontEndRootCanEditObject = $this->FrontEndRootParentObjectAsString();
        //to complete
        // if ($this->owner->ClassName == Config::inst()->get("FrontEndEditorPage_Controller", "default_model")) {
        // }
    }

    public function canCreate($member)
    {
        if ($this->owner->ClassName == Config::inst()->get("FrontEndEditorPage_Controller", "default_model")) {
            return true;
        }
    }

    public function canView($member)
    {
        if ($this->owner->FrontEndRootCanEditObject == FrontEndEditorSessionManager::get_root_can_edit_object_string()) {
            return true;
        }
    }

    public function canEdit($member)
    {
        if ($this->owner->FrontEndRootCanEditObject == FrontEndEditorSessionManager::get_root_can_edit_object_string()) {
            return true;
        }
    }

    /**
     * uses MyModel::$required_fields
     * @param ValidationResult
     */
    public function validate(ValidationResult $validationResult)
    {
        $requiredFields = Config::inst()->get($this->owner->class, 'required_fields', Config::INHERITED);
        if ($requiredFields) {
            foreach ($requiredFields as $name) {
                $error = false;
                if ($this->owner->hasMethod($name)) {
                    $object = $this->owner->$name();
                    if (! $object->exists()) {
                        $error = true;
                    }
                } elseif (! $this->owner->$name) {
                    $error = true;
                }
                if ($error) {
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
    public function FrontEndEditorBreadCrumbs()
    {
        $array = [];
        $al = ArrayList::create();
        $array[$this->owner->ClassName."-".$this->owner->ID] = $this->owner;
        if ($this->owner->hasMethod("FrontEndParentObject")) {
            $parent = $this->owner->FrontEndParentObject();
            while ($parent && $parent->exists()) {
                $array[$parent->ClassName."-".$parent->ID] = $parent;
                if ($parent->FrontEndIsRoot()) {
                    $parent = null;
                    break;
                } else {
                    $parent = $parent->FrontEndParentObject();
                }
            }
        }
        $array = array_reverse($array);
        foreach ($array as $object) {
            if ($object->getTitle()) {
                $al->push($object);
            }
        }
        return $al;
    }

    public function FrontEndRemoveRelationLink($relationField, $foreignID)
    {
        return DataObject::get_one('FrontEndEditorPage')->FrontEndRemoveRelationLink($this->owner, $relationField, $foreignID);
    }

    public function FrontEndAddRelationLink($relationField)
    {
        return DataObject::get_one('FrontEndEditorPage')->FrontEndAddRelationLink($this->owner, $relationField);
    }

    private static $_front_end_root_parent_object = [];

    /**
     *
     * @return DataObject
     */
    public function FrontEndRootParentObject()
    {
        $uid = $this->owner->FrontEndUID();
        if (!isset(self::$_front_end_root_parent_object[$uid])) {
            $returnObject = $this->owner;
            if ($this->owner->hasMethod("FrontEndParentObject")) {
                $parent = $this->owner->FrontEndParentObject();
                $x = 0;
                while (
                    $parent &&
                    $x < 99 &&
                    $parent->hasMethod("FrontEndParentObject")

                ) {
                    $x++;
                    if ($uid === $parent->FrontEndUID()) {
                        $parent = null;
                    } else {
                        $returnObject = $parent;
                        $parent = $parent->FrontEndParentObject();
                    }
                    //debug::log($parent->ClassName.$parent->ID."-------asdf-----");
                }
            }
            self::$_front_end_root_parent_object[$uid] = $returnObject;
        }
        return self::$_front_end_root_parent_object[$uid];
    }

    public function FrontEndIsRoot()
    {
        $parent = $this->owner->FrontEndRootParentObject();
        if ($parent) {
            return $parent->FrontEndUID() === $this->owner->FrontEndUID();
        } else {
            return true;
        }
    }

    /**
     *
     * @return string
     */
    public function FrontEndRootParentObjectAsString() : string
    {
        $obj = $this->owner->FrontEndRootParentObject();
        if ($obj && $obj->ID) {
            return FrontEndEditorSessionManager::object_to_string($obj);
        }

        return "";
    }

    /**
     * Adds a root object
     * @param FrontEndEditable $rootObject any dataobject implementing FrontEndEditable
     * @param bool             $write      write me?
     */
    public function FrontEndAddRootParentObject($rootObject, $write = false)
    {
        $this->owner->FrontEndRootCanEditObject = FrontEndEditorSessionManager::object_to_string($rootObject);
        if ($write) {
            if ($this->owner instanceof SiteTree) {
                $this->owner->writeToStage('Stage');
                $this->owner->publish('Stage', 'Live');
            } else {
                $this->owner->write();
            }
        }
        return $this->owner;
    }

    public function FrontEndUID()
    {
        return FrontEndEditorSessionManager::object_to_string($this->owner);
    }

    public function FrontEndShortAndExtendedTitle()
    {
        return $this->owner->FrontEndShortTitle()." - ".$this->owner->FrontEndExtendedTitle();
    }

    /**
     * records within the same "edit group"
     * in or excluding the current record.
     * @return Datalist
     */
    public function FrontEndDefaultSiblings($rootParent = null, $includeMe = false)
    {
        $className = $this->owner->ClassName;
        if ($rootParent) {
            $myObj = $rootParent;
        } else {
            $myObj = $this->owner;
        }
        $rootObjectAsString = $myObj->FrontEndRootParentObjectAsString();
        $list = $className::get()->filter(array("FrontEndRootCanEditObject" => $rootObjectAsString));
        if (! $includeMe) {
            $list = $list->exclude(array("ID" => $this->owner->ID));
        }
        if (! $rootObjectAsString) {
            $list = $list->filter(array("ID" => 0));
        }
        return $list;
    }

    /**
     * returns a datalist of objects of a particular class
     * (e.g. Page will include HomePage)
     * that share a particular root parent.
     * @param string $className [description]
     *
     * @return DataList
     */
    public function FrontEndFindChildObjects($className)
    {
        return $className::get()->filter(array('FrontEndRootCanEditObject' => $this->owner->FrontEndRootCanEditObject));
    }

    public function requireDefaultRecords()
    {
        if (isset($_GET['righttitles'])) {
            $fieldLabels = $this->owner->FieldLabels();
            $rightTitles = Config::inst()->get($this->owner->ClassName, "field_labels_right");
            if (!is_array($rightTitles)) {
                $rightTitles = [];
            }
            $rightTitles = array_merge($rightTitles, $this->owner->RightTitlesForFrontEnd());
            $fieldsToRemoveFromFrontEnd = array_merge(
                (array) $this->owner->FieldsToRemoveFromFrontEnd(),
                (array) $this->owner->FieldsToRemoveFromFrontEndDefaults()
            );
            foreach ($fieldLabels as $fieldName => $fieldLabel) {
                if (!in_array($fieldName, $fieldsToRemoveFromFrontEnd)) {
                    DB::alteration_message("Adding right title for ".$this->owner->ClassName.".".$fieldName);
                    $obj = FrontEndEditorRightTitle::add_or_find_field(
                        $this->owner->ClassName,
                        $fieldName,
                        isset($rightTitles[$fieldName]) ? $rightTitles[$fieldName] : ""
                    );
                }
            }
        }
    }
}
