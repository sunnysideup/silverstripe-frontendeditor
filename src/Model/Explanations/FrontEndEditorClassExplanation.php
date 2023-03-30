<?php

namespace SunnySideUp\FrontendEditor\Model\Explanations;

use FrontEndEditorExplanationsBaseClass;


class FrontEndEditorClassExplanation extends FrontEndEditorExplanationsBaseClass
{
    private static $field_labels = array(
        "ObjectClassName" => "DataObject Code",
        "ClassNameNice" => "DataObject",
        "LongDescription" => "Details of DataObject"
    );

    private static $summary_fields = array(
        "ClassNameNice" => "DataObject",
        "ShortDescription" => "Short Description",
        "HasLongDescriptionNice" => "Has Long Description"
    );

    private static $singular_name = 'Data-Entry Explanation for DataObject';

    private static $plural_name = 'Data-Entry Explanations for DataObjects';

    /**
     *
     * @param string $className class to describe (e.g. MyBusinessOwner)
     * @param string $type to describe (e.g. FrontEndEditorClassExplanation)
     *
     * @return FrontEndEditorClassExplanation
     */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    public static function add_or_find_item($className, $type = 'FrontEndEditorClassExplanation'): FrontEndEditorExplanationsBaseClass
    {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return parent::add_or_find_item($className, $type);
    }
}

