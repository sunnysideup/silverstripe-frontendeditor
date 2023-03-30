<?php

namespace SunnySideUp\FrontendEditor\Control;






use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Security;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\CMS\Controllers\ContentController;





class FrontEndFieldsWithAjaxValidation extends ContentController
{

    /**
     * Defines methods that can be called directly
     * @var array
     */
    private static $allowed_actions = [
        'check' => true
    ];

    /**
     * returns false on OK - or a message on error
     * @return bool
     */
    public function check($request)
    {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $className = Convert::raw2sql($this->request->param('ClassName'));
        //this is a bit of a hack...
        $id = intval($this->request->param('OtherID'));
        $field = Convert::raw2sql($this->request->param('FieldName'));
        $value = Convert::raw2sql($this->request->getVar('val'));
        $returnValue = 'ok';

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->class (case sensitive)
  * NEW: $this->class (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        if ($this->classAndFieldExist($className, $field)) {
            if ($id) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                $obj = $className::get()->byID($id);
            } else {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                $obj = DataObject::get_one($className);
            }
            if ($obj->canEdit()) {
                $validation = $obj->FrontEndFieldsWithAjaxValidation();
                $method = $validation[$field];
                $classAndMethod = explode('.', $method);
                if (count($classAndMethod) === 2) {
                    $method = $classAndMethod[1];
                    $objectForMethod = $obj;
                } else {
                    $method = $classAndMethod[0];
                    $objectForMethod = $this;
                }

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                $returnValue = $objectForMethod->$method($className, $id, $field, $value);
            } else {
                return Security::permissionFailure(
                    $this,
                    'You do not have privilege to edit this record.'
                );
            }
        } else {
            return Security::permissionFailure(
                $this,
                'Bad data'
            );
        }

        die($returnValue);
    }


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    protected function checkForDuplicates($className, $id, $field, $value)
    {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $others = $className::get()->filter([$field => $value]);
        if ($id) {
            $others = $others->exclude(['ID' => $id]);
        }
        if ($others->count() > 0) {
            $list = [];
            foreach ($others as $other) {
                $list[] = '<a href="'.$other->FrontEndEditLink().'">'.$other->FrontEndShortTitle().'</a>';
            }
            return '
                There is another entry with the same value. '.implode(', ', $list);
        }

        return 'ok';
    }


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    protected function checkForDuplicatesAsNumbers($className, $id, $field, $value)
    {
        $value = intval(preg_replace("/[^0-9]/", "", $value));


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return $this->checkForDuplicates($className, $id, $field, $value);
    }



/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    protected function classAndFieldExist($className, $field) : bool
    {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        if (class_exists($className)) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            $obj = Injector::inst()->get($className);
            if ($obj) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: ->db() (case sensitive)
  * NEW: ->Config()->get('db') (COMPLEX)
  * EXP: Check implementation
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                $db = $obj->Config()->get('db');

                return isset($db[$field]);
            }
        }

        return false;
    }
}

