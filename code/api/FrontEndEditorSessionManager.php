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

    public static function add_go_back_link($object)
    {
        $backObjectClassName = "";
        $sequenceNumber = Session::get("FrontEndGoBackSequenceNumber");
        if (!$sequenceNumber) {
            $sequenceNumber = 1;
        }
        if (count(Session::get("FrontEndGoBackSequenceNumber"))) {
            $data = explode(",", Session::get("FrontEndGoBackObjectDetails".$sequenceNumber));
            list($backObjectClassName, $backObjectID) = $data;
        }
        if ($backObjectClassName != $object->ClassName) {
            $sequenceNumber++;
            Session::set("FrontEndGoBackSequenceNumber", $sequenceNumber);
            Session::set("FrontEndGoBackObjectDetails".$sequenceNumber, $object->ClassName.",".$object->ID);
            Session::save();
        }
    }

    public static function get_sequence_number()
    {
        return Session::get("FrontEndGoBackSequenceNumber");
    }

    public static function get_sequence_number_details($number)
    {
        return Session::get("FrontEndGoBackObjectDetails".$number);
    }

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
            $objectString = Session::get("FrontEndEditorSessionManager_can_edit_object");
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
        $objectString = Session::set("FrontEndEditorSessionManager_can_edit_object", $string);
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
        return $className::get()->filter(array("FrontEndRootCanEditObject" => self::get_root_can_edit_object_string()));
    }

    /**
     * the record that was edited before this one ...
     * @param FrontEndEditable|null $currentRecord
     *
     * @return null|DataObject
     */
    public static function previous_object_based_on_browsing($currentRecord = null)
    {
        $sequenceNumber = Session::get("FrontEndGoBackSequenceNumber")-1;
        $data = explode(",", Session::get("FrontEndGoBackObjectDetails".$sequenceNumber));
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
                if ($obj && $obj->hasExtension('FrontEndDataExtension')) {
                    return $obj;
                }
            }
        }
    }

    public static function clear_previous_object_based_on_browsing()
    {
        $sequenceNumber = Session::get("FrontEndGoBackSequenceNumber");
        Session::set("FrontEndGoBackSequenceNumber", $sequenceNumber - 1);
        Session::clear("FrontEndGoBackObjectDetails".$sequenceNumber);
        Session::save();
    }

    ########################
    # Previous and Next Provider
    ########################

    public static function clear_sequencer()
    {
        Session::set(
            'FrontEndEditorPreviousAndNextSequencerClassName',
            ''
        );
        Session::clear(
            'FrontEndEditorPreviousAndNextSequencerClassName'
        );
        Session::save();
    }

    public static function set_sequencer($className)
    {
        Session::set(
            'FrontEndEditorPreviousAndNextSequencerClassName',
            $className
        );
    }


    /**
     * returns the sequencer that has been set
     *
     * @return string
     */
    public static function get_sequencer()
    {
        return Session::get(
            'FrontEndEditorPreviousAndNextSequencerClassName'
        );
    }

    /**
     * set if the should the current record be recorded YES or NO?
     * @param bool $bool [description]
     */
    public static function set_note_current_record(bool $bool)
    {
        return Session::set(
            'FrontEndEditorPreviousAndNextSequencerNoteCurrentRecord',
            $bool
        );
    }



    /**
     * Should the current record be recorded YES or NO??????
     * @return bool [description]
     */
    public static function get_note_current_record() : bool
    {
        return Session::get(
            'FrontEndEditorPreviousAndNextSequencerNoteCurrentRecord'
        ) ? true : false;
    }


    public static function clear_record_being_edited()
    {
        Session::set(
            'FrontEndEditorPreviousAndNextSequencerCurrentRecordBeingEdited',
            ''
        );
        Session::clear(
            'FrontEndEditorPreviousAndNextSequencerCurrentRecordBeingEdited'
        );
        Session::save();
    }

    public static function set_record_being_edited_in_sequence($object)
    {
        if (self::get_note_current_record() && $object) {
            Session::set(
                'FrontEndEditorPreviousAndNextSequencerCurrentRecordBeingEdited',
                self::object_to_string($object)
            );
            //dont allow it to be set again
            self::set_note_current_record(false);
        }
    }

    public static function get_record_being_edited_in_sequence($asString = false)
    {
        $string = Session::get(
            'FrontEndEditorPreviousAndNextSequencerCurrentRecordBeingEdited'
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

            return $className::get()->byID($id);
        }
    }
}
