<?php

class FrontEndEditorSequencerExplanation extends FrontEndEditorExplanationsBaseClass
{

    private static $field_labels = array(
        "ObjectClassName" => "Sequence Code",
        "ClassNameNice" => "Sequence Name",
        "LongDescription" => "Introduction to Sequence"
    );

    private static $summary_fields = array(
        "ClassNameNice" => "Sequence",
        "ShortDescription" => "Short Description",
        "HasLongDescriptionNice" => "Has Long description"
    );

    private static $singular_name = 'Sequence Data-Entry Information';

    private static $plural_name = 'Data-Entry Information for Sequences';

    /**
     *
     * @param string $className class to describe (e.g. MyBusinessOwner)
     * @param string $type to describe (e.g. FrontEndEditorSequencerExplanation)
     *
     * @return FrontEndEditorSequencerExplanation
     */
    public static function add_or_find_item($className, $type = 'FrontEndEditorSequencerExplanation'): FrontEndEditorExplanationsBaseClass
    {
        return parent::add_or_find_item($className, $type);
    }



    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }


}
