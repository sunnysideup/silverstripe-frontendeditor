<?php

class FrontEndEditorClassExplanation extends DataObject
{
    private static $db = array(
        "ObjectClassName" => "Varchar(100)",
        "LongDescription" => "HTMLText"
    );

    private static $indexes = array(
        "Unique" => array(
            "type" => "unique",
            "value" => "\"ObjectClassName\""
        )
    );

    private static $casting = array(
        "Title" => "Varchar",
        "ClassNameNice" => "Varchar",
        "HasLongDescription" => "Boolean",
        "HasLongDescriptionNice" => "Varchar"
    );

    private static $field_labels = array(
        "ObjectClassName" => "Class Name Code",
        "ClassNameNice" => "Class",
        "LongDescription" => "Introduction to class"
    );

    private static $summary_fields = array(
        "ClassNameNice" => "Class",
        "HasLongDescriptionNice" => "Long description"
    );

    private static $searchable_fields = array(
        "ObjectClassName" => "PartialMatchFilter"
    );

    private static $default_sort = "ObjectClassName ASC";

    private static $singular_name = 'Data-Entry Explanation for Class';
    public function i18n_singular_name()
    {
        return self::$singular_name;
    }

    private static $plural_name = 'Data-Entry Explanations for Classes';
    public function i18n_plural_name()
    {
        return self::$plural_name;
    }

    private static $_cache_for_class_objects = [];

    /**
     *
     * @param string $className
     * @param string $fieldName

     *
     * @return FrontEndEditorClassExplanation
     */
    public static function add_or_find_item($className)
    {
        $filter = array(
            "ObjectClassName" => $className
        );
        $obj = DataObject::get_one(
            'FrontEndEditorClassExplanation',
            $filter,
            $cacheDataObjectGetOne = false
        );
        if (! $obj) {
            $obj = FrontEndEditorClassExplanation::create($filter);
        }
        $id = $obj->write();

        $obj = DataObject::get_one('FrontEndEditorClassExplanation', ['ID' => $id]);

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

        $objectNames = array_unique(FrontEndEditorClassExplanation::get()->column('ObjectClassName'));
        $newList = ['' => '-- ANY --'];
        foreach($objectNames as $key => $value) {
            $newList[$value] = $this->getClassNameNice($value);
        }
        asort($newList);
        $fieldList->replaceField(
            'ObjectClassName',
            DropdownField::create(
                'ObjectClassName',
                'Class',
                $newList
            )
        );


        //allow changes
        $this->extend('scaffoldSearchFields', $fieldList, $_params);

        return $fieldList;
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName("ObjectClassName");
        $fields->removeByName("ObjectFieldName");
        $fields->addFieldToTab("Root.Main", new ReadonlyField("ObjectClassName", "Class"), "LongDescription");
        $fields->addFieldToTab("Root.Main", new ReadonlyField("ClassNameNice", "Class Proper"), "LongDescription");
        $list = FrontEndEditorRightTitle::get()->filter(['ObjectClassName' => $this->ObjectClassName]);
        if($list->count()) {
            $fields->addFieldToTab(
                "Root.Fields",
                GridField::create(
                    'Fields',
                    'Fields',
                    $list,
                    GridFieldConfig_RecordEditor::create()
                )
            );
        }
        $fields->dataFieldByName('LongDescription')->setRows(3);

        return $fields;
    }

    public function getTitle()
    {
        return $this->getClassNameNice()." (".$this->ObjectClassName.")";
    }

    private static $_cache_for_class_names = [];

    protected function getClassNameObjectFromCache($className = null)
    {
        if($className === null) {
            $className = $this->ObjectClassName;
        }
        if (!isset(self::$_cache_for_class_names[$className])) {
            if (!class_exists($className)) {
                $className = 'DataObject';
                $this->ObjectClassName = "DataObject";
            }
            self::$_cache_for_class_names[$className] = Injector::inst()->get($className);
        }

        return self::$_cache_for_class_names[$className];
    }

    public function getClassNameNice($className = null)
    {
        if($className === null) {
            $className = $this->ObjectClassName;
        }
        if ($obj = $this->getClassNameObjectFromCache($className)) {
            return $obj->i18n_singular_name();
        }
        return "ERROR IN FINDING NAME FOR CLASS: ".$className;
    }

    /**
     * @casted variable
     * @return boolean
     */
    public function HasLongDescription()
    {
        return $this->getHasLongDescription();
    }
    public function getHasLongDescription()
    {
        return strlen($this->LongDescription) > 10 ? true : false;
    }

    /**
     * @casted variable
     * @return string
     */
    public function HasLongDescriptionNice()
    {
        return $this->getHasLongDescriptionNice();
    }
    public function getHasLongDescriptionNice()
    {
        return $this->HasLongDescription() ? "yes" : "no";
    }

    /**
     *
     * @return string|null
     */
    public function BestDescription()
    {
        if ($this->getHasLongDescription()) {
            return $this->LongDescription;
        }
    }

    /**
     *
     * @return boolean
     */
    public function HasDescription()
    {
        if ($this->getHasLongDescription()) {
            return true;
        }

        return false;
    }

    public function canCreate($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        if (! $this->getClassNameObjectFromCache()) {
            return parent::canDelete($member);
        }

        return false;
    }
}
