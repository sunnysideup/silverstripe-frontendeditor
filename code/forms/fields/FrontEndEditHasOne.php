<?php

/**
 * creates an edit or add
 * field for an has one relation.
 *
 *
 */

class FrontEndEditHasOne extends FormField
{
    private $hasOneRelationRecord = null;

    private $originatingRecord = null;

    public function __construct($fieldName, $title, $originatingRecord, $hasOneRelationRecord)
    {
        $this->hasOneRelationRecord = $hasOneRelationRecord;
        $this->originatingRecord = $originatingRecord;
        return parent::__construct($fieldName, $title);
    }

    public function Field($properties = array())
    {
        $Link = "";
        $title = "";
        if ($this->hasOneRelationRecord && $this->hasOneRelationRecord->exists()) {
            if ($this->hasOneRelationRecord->canEdit()) {
                $link = $this->hasOneRelationRecord->FrontEndEditLink();
                if ($this->hasOneRelationRecord instanceof FrontEndEditable) {
                    $title = $this->hasOneRelationRecord->FrontEndShortTitle();
                } else {
                    $title = "edit ".$this->hasOneRelationRecord->getTitle();
                }
            }
        } else {
            $hasOneFields = $this->originatingRecord->has_one();
            $model = $hasOneFields[$this->getName()];
            $this->hasOneRelationRecord = Injector::inst()->create($model);
            if ($this->hasOneRelationRecord->canCreate()) {
                $link = $this->hasOneRelationRecord->FrontEndEditLink();
                $title = "add ";
            }
        }
        if ($link && $title) {
            return "<p><a href=\"".$link."\">".$title."</a></p>";
        } else {
            return "";
        }
    }
}
