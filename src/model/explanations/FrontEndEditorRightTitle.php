<?php

class FrontEndEditorRightTitle extends FrontEndEditorExplanationsBaseClass
{
    private static $field_labels = array(
        "ObjectClassName" => "DataObject Code",
        "ObjectFieldName" => "Field Name Code",
        "ClassNameNice" => "Class",
        "FieldNameNice" => "Field",
        "LongDescription" => "Extended Description - use with care if necessary"
    );

    private static $summary_fields = array(
        "ClassNameNice" => "DataObject",
        "ObjectFieldName" => "Field Code",
        "FieldNameNice" => "Field Name",
        "ShortDescription" => "Description",
        "HasLongDescriptionNice" => "Has Long description"
    );

    private static $searchable_fields = array(
        "ObjectClassName" => "PartialMatchFilter",
        "ObjectFieldName" => "PartialMatchFilter",
        "ShortDescription" => "PartialMatchFilter"
    );


    private static $singular_name = 'Individual Field Explanation';

    private static $plural_name = 'Individual Field Explanations';

    /**
     *
     * @param string $className
     *
     * @return array
     */
    public static function get_entered_ones($className)
    {
        $array = [];
        $objects = FrontEndEditorRightTitle::get()
            ->filter(array("ObjectClassName" => $className));
        foreach ($objects as $object) {
            if ($object->HasDescription()) {
                $array[$object->ObjectFieldName] = $object->BestDescription();
            }
        }
        return $array;
    }

    /**
     *
     * @param string $className
     * @param string $fieldName
     * @param string $defaultValue
     *
     * @return FrontEndEditorRightTitle
     */
    public static function add_or_find_field($className, $fieldName, $defaultValue = "")
    {
        $filter = array(
            "ObjectClassName" => $className,
            "ObjectFieldName" => $fieldName
        );
        $obj = DataObject::get_one(
            'FrontEndEditorRightTitle',
            $filter,
            $cacheDataObjectGetOne = false
        );
        if (!$obj) {
            $obj = FrontEndEditorRightTitle::create($filter);
            $obj->DefaultValue = $defaultValue;
            $obj->ShortDescription = $defaultValue;
        }
        if ($defaultValue) {
            if ($obj->DefaultValue !== $defaultValue) {
                $obj->DefaultValue = $defaultValue;
            }
        }
        $obj->write();

        return $obj;
    }




    /**
     * Determine which properties on the DataObject are
     * searchable, and map them to their default {@link FormField}
     * representations. Used for scaffolding a searchform for {@link ModelAdmin}.
     *
     * Some additional logic is included for switching field labels, based on
     * how generic or specific the field type is.
     *
     * Used by {@link SearchContext}.
     *
     * @param array $_params
     *                       'fieldClasses': Associative array of field names as keys and FormField classes as values
     *                       'restrictFields': Numeric array of a field name whitelist
     *
     * @return FieldList
     */
    public function scaffoldSearchFields($_params = null)
    {
        $fieldList = parent::scaffoldSearchFields($_params);

        $rows = DB::query('
            SELECT DISTINCT CONCAT("ObjectClassName", \',\', "ObjectFieldName") AS COMBO
            FROM "FrontEndEditorExplanationsBaseClass"
            WHERE "ClassName" = \'FrontEndEditorRightTitle\'
        ');
        $newList = ['' => '-- ANY --'];
        foreach ($rows as $row) {
            $combo = $row['COMBO'];
            list($className, $fieldName) = explode(',', $combo);
            if ($className && $fieldName) {
                $newList[$fieldName] = $this->getFieldNameNice($className, $fieldName).
                ' ('.$this->getClassNameNice($className).')';
            }
        }
        asort($newList);
        $fieldList->replaceField(
            'ObjectFieldName',
            DropdownField::create(
                'ObjectFieldName',
                'Field',
                $newList
            )
        );

        //allow changes
        $this->extend('UpdateSearchFields', $fieldList, $_params);

        return $fieldList;
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab("Root.Main", ReadonlyField::create("FieldNameNice", "Field"), "ShortDescription");
        $fields->addFieldToTab("Root.Main", ReadonlyField::create("ObjectFieldName", "Code"), "ShortDescription");
        $fields->addFieldToTab("Root.Main", ReadonlyField::create("DefaultValue", "Default"), "ShortDescription");

        $fields->dataFieldByName('LongDescription')->setRows(3);

        return $fields;
    }

    public function getTitle() : string
    {
        return $this->getClassNameNice().", ".$this->getFieldNameNice()." (".$this->ObjectFieldName.")";
    }

    private static $_cache_for_field_labels = [];

    public function getFieldNameNice($className = null, $fieldName = null)
    {
        if ($className === null) {
            $className = $this->ObjectClassName;
        }
        if ($fieldName === null) {
            $fieldName = $this->ObjectFieldName;
        }
        if (!isset(self::$_cache_for_field_labels[$className])) {
            if ($obj = $this->getClassNameObjectFromCache($className)) {
                self::$_cache_for_field_labels[$className] = $obj->FieldLabels();
            }
        }
        if (isset(self::$_cache_for_field_labels[$className])) {
            if ($array = self::$_cache_for_field_labels[$className]) {
                if (isset($array[$fieldName])) {
                    return $array[$fieldName];
                }
            }
        }

        return "ERROR";
    }


    /**
     *
     * @return string
     */
    public function BestDescription() : string
    {
        if ($this->getHasLongDescription()) {
            return $this->LongDescription;
        } else {
            if ($this->ShortDescription) {
                return $this->ShortDescription;
            }
            if ($this->DefaultValue) {
                return $this->DefaultValue;
            }
        }
        return '';
    }

    public function canCreate($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        if (!$this->getClassNameObjectFromCache()) {
            return DataObject::canDelete($member);
        }
        if ($this->getFieldNameNice() == "ERROR") {
            return DataObject::canDelete($member);
        }

        return false;
    }
}

