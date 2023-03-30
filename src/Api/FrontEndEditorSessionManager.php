<?php

namespace SunnySideUp\FrontendEditor\Api;

use SilverStripe\Control\Controller;
use SilverStripe\View\ViewableData;

/**
 * holds info about the current edit session
 *
 */


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD:  extends Object (ignore case)
  * NEW:  extends ViewableData (COMPLEX)
  * EXP: This used to extend Object, but object does not exist anymore. You can also manually add use Extensible, use Injectable, and use Configurable
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
class FrontEndEditorSessionManager extends ViewableData
{
    /**
    * the root object relating to the current editing session.
    * @var null | FrontEndEditable
    */
    private static $_can_edit_object = null;



    ###
    # current_record_being_edited
    ###

    public static function set_current_record_being_edited($record)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        Controller::curr()->getRequest()->getSession()->set('FEE_CurrentRecordClassName', $record->ClassName);

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        Controller::curr()->getRequest()->getSession()->set('FEE_CurrentRecordID', $record->ID);
    }

    public static function get_current_record_being_edited($record)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */


        $className = Controller::curr()->getRequest()->getSession()->get('FEE_CurrentRecordClassName');

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        $id = Controller::curr()->getRequest()->getSession()->get('FEE_CurrentRecordID');



        return $className::get()->byID(intval($id));
    }

    public static function clear_current_record_being_edited()
    {
        self::clear_variable('FEE_CurrentRecordClassName');
        self::clear_variable('FEE_CurrentRecordID');
    }




    ###
    # form_data
    ###


    public static function set_form_data($data)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        Controller::curr()->getRequest()->getSession()->set('FormInfo.FrontEndEditForm.data', $data);
    }

    public static function get_form_data()
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        return Controller::curr()->getRequest()->getSession()->get('FormInfo.FrontEndEditForm.data');
    }

    public static function clear_form_data()
    {
        self::clear_variable('FormInfo.FrontEndEditForm.data');
    }



    ###
    # FrontEndGoBackInBrowserSteps
    # FrontEndGoBackObjectDetails
    ###

    public static function add_go_back_link($object)
    {
        $backObjectClassName = '';

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        $sequenceNumber = Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserSteps');
        if (!$sequenceNumber) {
            $sequenceNumber = 1;
        }

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        if (count(Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserSteps'))) {
            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            $data = explode(",", Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber));
            list($backObjectClassName, $backObjectID) = $data;
        }
        if ($backObjectClassName != $object->ClassName) {
            $sequenceNumber++;

            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            Controller::curr()->getRequest()->getSession()->set('FEE_GoBackInBrowserSteps', $sequenceNumber);

            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            Controller::curr()->getRequest()->getSession()->set('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber, $object->ClassName.",".$object->ID);

            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            Controller::curr()->getRequest()->getSession()->save();
        }
    }

    public static function get_sequence_number()
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        return Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserSteps');
    }

    public static function get_sequence_number_details($number)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        return Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserStepsDetails'.$number);
    }


    /**
     * the record that was edited before this one ...
     * @param FrontEndEditable|null $currentRecord
     *
     * @return null|DataObject
     */
    public static function previous_object_based_on_browsing($currentRecord = null)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        $sequenceNumber = Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserSteps')-1;

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        $data = explode(",", Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber));
        if (count($data) != 2) {
            $data = array("", "");
        }
        list($backObjectClassName, $backObjectID) = $data;
        if ($backObjectClassName && $backObjectID) {
            if (
                $currentRecord &&
                $currentRecord->ClassName == $backObjectClassName &&
                $currentRecord->ID == $backObjectID
            ) {
                //do nothing
            } else {
                $obj = $backObjectClassName::get()->byID($backObjectID);
                if ($obj && $obj->hasExtension('FEDataExtension')) {
                    return $obj;
                }
            }
        }
    }



    public static function clear_previous_object_based_on_browsing()
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        $sequenceNumber = Controller::curr()->getRequest()->getSession()->get('FEE_GoBackInBrowserSteps');

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        Controller::curr()->getRequest()->getSession()->set('FEE_GoBackInBrowserSteps', $sequenceNumber - 1);

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        Controller::curr()->getRequest()->getSession()->clear('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber);

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        Controller::curr()->getRequest()->getSession()->save();
    }

    public static function clear_all_previous_objects()
    {
        self::clear_variable('FEE_GoBackInBrowserSteps');
        for ($i = 0; $i < 30; $i++) {
            self::clear_variable('FEE_GoBackInBrowserStepsDetails'.$i);
        }
    }





    ###
    # FEE_canEditObject
    ###


    /**
     * returns ClassName,ID
     * e.g. Page,123 or MySpeciaPage,222
     *
     * @return string
     */
    public static function get_root_can_edit_object_string()
    {
        $obj = self::get_can_edit_object();

        return self::object_to_string($obj);
    }

    /**
     *
     * @return FrontEndEditable
     */
    public static function get_can_edit_object()
    {
        if (self::$_can_edit_object === null) {
            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            $objectString = Controller::curr()->getRequest()->getSession()->get('FEE_canEditObject');
            if ($objectString) {
                self::$_can_edit_object = self::string_to_object($objectString);
            }
        }

        return self::$_can_edit_object;
    }

    /**
     * This is where you set the root object
     * @param FrontEndEditable $object
     *
     * @return string
     */
    public static function set_can_edit_object($object)
    {
        $string = self::object_to_string($object);

        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        $objectString = Controller::curr()->getRequest()->getSession()->set('FEE_canEditObject', $string);
        self::$_can_edit_object = $object;

        return $objectString;
    }

    /**
     * a list of items, for one className, that can be edited based on the root object
     * @param string $className
     * @return DataList
     */


    public static function editable_lists_based_on_can_edit($className)
    {
        return $className::get()->filter(array('FrontEndRootCanEditObject' => self::get_root_can_edit_object_string()));
    }

    public static function clear_can_edit_object()
    {
        self::clear_variable('FEE_canEditObject', null);
    }




    ########################
    # Previous and Next Provider
    ########################

    public static function clear_sequencer()
    {
        self::clear_variable('FEE_SequencerClassName');
        self::clear_variable('FEE_SequencerNoteCurrentRecord');
        self::clear_variable('FEE_SequencerCurrentRecordBeingEdited');
        self::clear_can_edit_object();
        self::clear_current_record_being_edited();
        self::clear_form_data();
    }




    ###
    # FrontEndEditorSequencerClassName
    ###



    public static function set_sequencer($className)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */


        Controller::curr()->getRequest()->getSession()->set('FEE_SequencerClassName', $className);
    }

    /**
     * returns the sequencer that has been set
     *
     * @return string
     */
    public static function get_sequencer()
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        return Controller::curr()->getRequest()->getSession()->get('FEE_SequencerClassName');
    }

    /**
     * set if the should the current record be recorded YES or NO?
     * @param bool $bool [description]
     */
    public static function set_note_current_record(bool $bool)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        return Controller::curr()->getRequest()->getSession()->set('FEE_SequencerNoteCurrentRecord', $bool);
    }





    ###
    # FrontEndEditorSequencerNoteCurrentRecord
    ###


    /**
     * Should the current record be recorded YES or NO??????
     * @return bool [description]
     */
    public static function get_note_current_record(): bool
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        return Controller::curr()->getRequest()->getSession()->get('FEE_SequencerNoteCurrentRecord') ? true : false;
    }


    public static function set_record_being_edited_in_sequence($object)
    {
        if (self::get_note_current_record() && $object) {
            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            Controller::curr()->getRequest()->getSession()->set(
                'FEE_SequencerCurrentRecordBeingEdited',
                self::object_to_string($object)
            );
            //dont allow it to be set again
            self::set_note_current_record(false);
        }
    }

    public static function get_record_being_edited_in_sequence($asString = false)
    {
        /**
          * ### @@@@ START REPLACEMENT @@@@ ###
          * WHY: automated upgrade
          * OLD: Session:: (case sensitive)
          * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
          * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
          * ### @@@@ STOP REPLACEMENT @@@@ ###
          */
        $string = Controller::curr()->getRequest()->getSession()->get(
            'FEE_SequencerCurrentRecordBeingEdited'
        );
        if ($string) {
            if ($asString) {
                return $string;
            } else {
                return self::string_to_object($string);
            }
        } else {
            return '';
        }
    }



    #############
    # helper methods....
    #############

    /**
     * this method is used by FrontEndUID
     * @param  DataObject $object
     * @return string
     */
    public static function object_to_string($object): string
    {
        if ($object) {
            return $object->ClassName.','.$object->ID;
        }

        return 'DataObject,0';
    }

    public static function string_to_object($string)
    {
        if ($string) {
            list($className, $id) = explode(',', $string);



            return $className::get()->byID(intval($id));
        }
    }

    protected static function clear_variable($variable)
    {
        for ($i = 0; $i < 3; $i++) {
            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            Controller::curr()->getRequest()->getSession()->set($variable, '');

            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            Controller::curr()->getRequest()->getSession()->set($variable, null);

            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
            Controller::curr()->getRequest()->getSession()->clear($variable);

            /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: Session:: (case sensitive)
              * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
              * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
        }
    }
}
