<?php

class FrontEndEditorRightTitle extends DataObject {

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

	private static $field_labels = array(
		"ObjectClassName" => "Class Name Code",
		"ObjectFieldName" => "FIeld Name Code",
		"ClassNameNice" => "Class",
		"FieldNameNice" => "Field",
		"LongDescription" => "Extended Description - use with care if necessary"
	);

	private static $summary_fields = array(
		"ClassNameNice" => "Class",
		"FieldNameNice" => "Field",
		"ObjectFieldName" => "Code",
		"ShortDescription" => "Description",
		"HasLongDescriptionNice" => "Long description"
	);

	private static $searchable_fields = array(
		"ObjectClassName" => "PartialMatchFilter",
		"ObjectFieldName" => "PartialMatchFilter",
		"ShortDescription" => "PartialMatchFilter"
	);

	private static $default_sort = "ObjectClassName ASC, ObjectFieldName ASC";

	private static $_cache_for_class_objects = array();


	/**
	 *
	 * @param string $className
	 *
	 * @return FrontEndEditorRightTitle
	 */
	public static function get_entered_ones($className) {
		$array = array();
		$objects = FrontEndEditorRightTitle::get()
			->filter(array("ObjectClassName" => $className));
		foreach($objects as $object) {
			if($object->HasDescription()) {
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
	public static function add_or_find_item($className, $fieldName, $defaultValue = "") {
		$filter = array(
			"ObjectClassName" => $className,
			"ObjectFieldName" => $fieldName
		);
		$obj = FrontEndEditorRightTitle::get()->filter($filter)->first();
		if(!$obj) {
			$obj = FrontEndEditorRightTitle::create($filter);
			$obj->DefaultValue = $defaultValue;
			$obj->ShortDescription = $defaultValue;
		}
		if($obj->DefaultValue != $defaultValue){
			$obj->DefaultValue = $defaultValue;
		}
		$obj->write();
		return $obj;
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName("ObjectClassName");
		$fields->removeByName("ObjectFieldName");
		$fields->addFieldToTab("Root.Main", new ReadonlyField("ClassNameNice", "Class"), "ShortDescription");
		$fields->addFieldToTab("Root.Main", new ReadonlyField("FieldNameNice", "Field"), "ShortDescription");
		$fields->addFieldToTab("Root.Main", new ReadonlyField("ObjectFieldName", "Code"), "ShortDescription");
		$fields->addFieldToTab("Root.Main", new ReadonlyField("DefaultValue", "Default"), "ShortDescription");
		$fields->addFieldToTab("Root.Main", new TextareaField("ShortDescription", "Short Description"), "LongDescription");
		return $fields;
	}

	function getTitle(){
		return $this->getClassNameNice().", ".$this->getFieldNameNice()." (".$this->ObjectFieldName.")";
	}

	private static $_cache_for_class_names = array();

	protected function getClassNameObjectFromCache(){
		if(!isset(self::$_cache_for_class_names[$this->ObjectClassName])) {
			if(!class_exists($this->ObjectClassName)) {
				$this->ObjectClassName = "DataObject";
			}
			self::$_cache_for_class_names[$this->ObjectClassName] = Injector::inst()->get($this->ObjectClassName);
		}
		return self::$_cache_for_class_names[$this->ObjectClassName];
	}

	function getClassNameNice(){
		if($obj = $this->getClassNameObjectFromCache()) {
			return $obj->singular_name();
		}
		return "ERROR IN FINDING NAME FOR CLASS: ".$this->ObjectClassName;
	}

	private static $_cache_for_field_labels = array();

	function getFieldNameNice(){
		if(!isset(self::$_cache_for_field_labels[$this->ObjectClassName])) {
			if($obj = $this->getClassNameObjectFromCache()) {
				self::$_cache_for_field_labels[$this->ObjectClassName] = $obj->FieldLabels();
			}
		}
		if(isset(self::$_cache_for_field_labels[$this->ObjectClassName])) {
			if($array = self::$_cache_for_field_labels[$this->ObjectClassName]) {
				if(isset($array[$this->ObjectFieldName])) {
					return $array[$this->ObjectFieldName];
				}
			}
		}
		return "ERROR";
	}

	/**
	 * @casted variable
	 * @return boolean
	 */
	function HasLongDescription(){ return $this->getHasLongDescription(); }
	function getHasLongDescription(){
		return strlen($this->LongDescription) > 10 ? true : false;
	}

	/**
	 * @casted variable
	 * @return string
	 */
	function HasLongDescriptionNice(){ return $this->getHasLongDescriptionNice(); }
	function getHasLongDescriptionNice(){
		return $this->HasLongDescription() ? "yes" : "no";
	}

	/**
	 *
	 * @return string
	 */
	function BestDescription() {
		if($this->getHasLongDescription()) {
			return $this->LongDescription;
		}
		else {
			if($this->ShortDescription) {
				return $this->ShortDescription;
			}
			return $this->DefaultValue;
		}
	}

	/**
	 *
	 * @return boolean
	 */
	function HasDescription() {
		if($this->getHasLongDescription()) {
			return true;
		}
		else {
			if($this->ShortDescription) {
				return true;
			}
		}
		return false;
	}

	function canCreate($member = null) {
		return false;
	}

	function canDelete($member = null) {
		if(!$this->getClassNameObjectFromCache()) {
			return parent::canDelete($member);
		}
		if($this->getFieldNameNice() == "ERROR") {
			return parent::canDelete($member);
		}
		return false;
	}

}
