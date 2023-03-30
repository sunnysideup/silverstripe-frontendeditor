<?php

namespace SunnySideUp\FrontendEditor\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Core\Injector\Injector;

class FrontEndEditorExplanationsBaseClass extends DataObject
{


    private static $table_name = 'FrontEndEditorExplanationsBaseClass';

    private static $db = array(
        "ObjectClassName" => "Varchar(100)",
        "ObjectFieldName" => "Varchar(100)",
        "ShortDescription" => "Varchar(255)",
        "LongDescription" => "HTMLText",
        "DefaultValue" => "Varchar(255)"
    );

    private static $indexes = array(
        "ObjectClassName" => true,
        "ObjectFieldName" => true,
        "Unique" => array(
            "type" => "unique",
            "value" => "\"ObjectClassName\", \"ObjectFieldName\"",
        )
    );

    private static $casting = array(
        "Title" => "Varchar",
        "ClassNameNice" => "Varchar",
        "FieldNameNice" => "Varchar",
        "HasLongDescription" => "Boolean",
        "HasLongDescriptionNice" => "Varchar"
    );


    private static $searchable_fields = array(
        "ObjectClassName" => "PartialMatchFilter",
        "ShortDescription" => "PartialMatchFilter"
    );

    private static $default_sort = "ObjectClassName ASC";

    protected static $_cache_for_explanation_objects = [];

    /**
     *
     * @param string $className
     * @param string $type

     *
     * @return FrontEndEditorExplanationsBaseClass
     */


    public static function add_or_find_item($className, $type = ''): FrontEndEditorExplanationsBaseClass
    {
        if (! $type || $type === FrontEndEditorExplanationsBaseClass::class) {
            user_error('A type must be provided!');
        }


        $key = $type.'_'.$className;
        if (isset(self::$_cache_for_explanation_objects[$key])) {
            //do nothing
        } else {
            $filter = array(


                "ObjectClassName" => $className
            );
            $obj = DataObject::get_one(
                $type,
                $filter,
                $cacheDataObjectGetOne = false
            );
            if (! $obj) {
                $obj = $type::create($filter);
            }
            $id = $obj->write();

            self::$_cache_for_explanation_objects[$key] = DataObject::get_one($type, ['ID' => $id]);
        }

        return self::$_cache_for_explanation_objects[$key];
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
        $fieldLabels = $this->FieldLabels();

        $objectNames = array_unique(FrontEndEditorExplanationsBaseClass::get()->column('ObjectClassName'));
        $newList = ['' => '-- ANY --'];
        foreach ($objectNames as $key => $value) {
            $newList[$value] = $this->getClassNameNice($value);
        }
        asort($newList);
        $fieldList->replaceField(
            'ObjectClassName',
            DropdownField::create(
                'ObjectClassName',
                $fieldLabels['ObjectClassName'],
                $newList
            )
        );

        $fieldList->push(
            TextField::create('ShortDescription', 'Short Description')
        );


        //allow changes
        $this->extend('UpdateSearchFields', $fieldList, $_params);

        return $fieldList;
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fieldLabels = $this->FieldLabels();
        $fields->removeByName("ObjectClassName");
        $fields->removeByName("ObjectFieldName");
        $fields->removeByName("DefaultValue");
        $fields->addFieldToTab("Root.Main", ReadonlyField::create("ObjectClassName", $fieldLabels['ObjectClassName']), "ShortDescription");
        $fields->addFieldToTab("Root.Main", ReadonlyField::create("ClassNameNice", $fieldLabels['ClassNameNice']), "ShortDescription");

        $fields->dataFieldByName('LongDescription')->setRows(3);

        return $fields;
    }

    /**
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getClassNameNice()." (".$this->ObjectClassName.")";
    }

    /**
     * caching variable for objects`
     * @var array
     */
    private static $_cache_for_class_objects = [];

    /**
     *
     * @param  string|null $className
     *
     * @return FrontEndEditble (DataObject)
     */


    protected function getClassNameObjectFromCache($className = null)
    {

        if ($className === null) {

            $className = $this->ObjectClassName;
        }


        if (! isset(self::$_cache_for_class_objects[$className])) {

            if (! class_exists($className)) {

                $className = DataObject::class;
                $this->ObjectClassName = DataObject::class;
            }


            self::$_cache_for_class_objects[$className] = Injector::inst()->get($className);
        }



        return self::$_cache_for_class_objects[$className];
    }

    /**
     *
     * @param  [type] $className [description]
     * @return [type]            [description]
     */


    public function getClassNameNice($className = null): string
    {

        if ($className === null) {

            $className = $this->ObjectClassName;
        }


        if ($obj = $this->getClassNameObjectFromCache($className)) {
            return $obj->i18n_singular_name();
        }


        return "ERROR IN FINDING NAME FOR CLASS: ".$className;
    }

    /**
     * @casted variable
     *
     * @return bool
     */
    public function HasLongDescription(): bool
    {
        return $this->getHasLongDescription();
    }

    /**
     * @casted variable
     * @return bool
     */
    public function getHasLongDescription(): bool
    {
        return strlen($this->LongDescription) > 10 ? true : false;
    }

    /**
     * @casted variable
     * @return string
     */
    public function HasLongDescriptionNice(): string
    {
        return $this->getHasLongDescriptionNice();
    }
    public function getHasLongDescriptionNice(): string
    {
        return $this->HasLongDescription() ? "yes" : "no";
    }

    /**
     *
     * @return string|null
     */
    public function BestDescription(): string
    {
        if ($this->getHasLongDescription()) {
            return $this->LongDescription;
        } else {
            return $this->ShortDescription;
        }
        return '';
    }

    /**
     *
     * @return bool
     */
    public function HasDescription(): bool
    {
        if ($this->getHasLongDescription()) {
            return true;
        } else {
            if ($this->ShortDescription) {
                return true;
            }
        }
        return false;
    }


    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canDelete($member = null)
    {
        if (! $this->getClassNameObjectFromCache()) {
            return parent::canDelete($member = null);
        }

        return false;
    }

    protected static function get_my_static_class()
    {
        return 'ddd';
    }
}
