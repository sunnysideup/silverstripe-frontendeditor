<?php

namespace SunnySideUp\FrontendEditor\Model\Explanations;

use SilverStripe\ORM\DataObject;
use SunnySideUp\FrontendEditor\Model\FrontEndEditorExplanationsBaseClass;

class FrontEndEditorClassExplanation extends FrontEndEditorExplanationsBaseClass
{
    private static $field_labels = array(
        "ObjectClassName" => "DataObject Code",
        "ClassNameNice" => DataObject::class,
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


    public static function add_or_find_item($className, $type = FrontEndEditorClassExplanation::class): FrontEndEditorExplanationsBaseClass
    {
        return parent::add_or_find_item($className, $type);
    }
}
