<?php

/**
 * holds info about the current edit session
 *
 */

class FrontEndEditorSessionManager extends Object
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
        Session::set('FEE_CurrentRecordClassName', $record->ClassName);
        Session::set('FEE_CurrentRecordID', $record->ID);
    }

    public static function get_current_record_being_edited($record)
    {
        $className = Session::get('FEE_CurrentRecordClassName');
        $id = Session::get('FEE_CurrentRecordID');

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
        Session::set('FormInfo.FrontEndEditForm.data', $data);
    }

    public static function get_form_data()
    {
        return Session::get('FormInfo.FrontEndEditForm.data');
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
        $sequenceNumber = Session::get('FEE_GoBackInBrowserSteps');
        if (!$sequenceNumber) {
            $sequenceNumber = 1;
        }
        if (count(Session::get('FEE_GoBackInBrowserSteps'))) {
            $data = explode(",", Session::get('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber));
            list($backObjectClassName, $backObjectID) = $data;
        }
        if ($backObjectClassName != $object->ClassName) {
            $sequenceNumber++;
            Session::set('FEE_GoBackInBrowserSteps', $sequenceNumber);
            Session::set('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber, $object->ClassName.",".$object->ID);
            Session::save();
        }
    }

    public static function get_sequence_number()
    {
        return Session::get('FEE_GoBackInBrowserSteps');
    }

    public static function get_sequence_number_details($number)
    {
        return Session::get('FEE_GoBackInBrowserStepsDetails'.$number);
    }


    /**
     * the record that was edited before this one ...
     * @param FrontEndEditable|null $currentRecord
     *
     * @return null|DataObject
     */
    public static function previous_object_based_on_browsing($currentRecord = null)
    {
        $sequenceNumber = Session::get('FEE_GoBackInBrowserSteps')-1;
        $data = explode(",", Session::get('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber));
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
        $sequenceNumber = Session::get('FEE_GoBackInBrowserSteps');
        Session::set('FEE_GoBackInBrowserSteps', $sequenceNumber - 1);
        Session::clear('FEE_GoBackInBrowserStepsDetails'.$sequenceNumber);
        Session::save();
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
            $objectString = Session::get('FEE_canEditObject');
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
        $objectString = Session::set('FEE_canEditObject', $string);
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
        Session::set('FEE_SequencerClassName', $className);
    }

    /**
     * returns the sequencer that has been set
     *
     * @return string
     */
    public static function get_sequencer()
    {
        return Session::get('FEE_SequencerClassName');
    }

    /**
     * set if the should the current record be recorded YES or NO?
     * @param bool $bool [description]
     */
    public static function set_note_current_record(bool $bool)
    {
        return Session::set('FEE_SequencerNoteCurrentRecord', $bool);
    }





    ###
    # FrontEndEditorSequencerNoteCurrentRecord
    ###


    /**
     * Should the current record be recorded YES or NO??????
     * @return bool [description]
     */
    public static function get_note_current_record() : bool
    {
        return Session::get('FEE_SequencerNoteCurrentRecord') ? true : false;
    }


    public static function set_record_being_edited_in_sequence($object)
    {
        if (self::get_note_current_record() && $object) {
            Session::set(
                'FEE_SequencerCurrentRecordBeingEdited',
                self::object_to_string($object)
            );
            //dont allow it to be set again
            self::set_note_current_record(false);
        }
    }

    public static function get_record_being_edited_in_sequence($asString = false)
    {
        $string = Session::get(
            'FEE_SequencerCurrentRecordBeingEdited'
        );
        if ($string) {
            if ($asString) {
                return $tring;
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
    public static function object_to_string($object) : string
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
            Session::set($variable, '');
            Session::set($variable, null);
            Session::clear($variable);
            Session::save();
        }
    }
}

