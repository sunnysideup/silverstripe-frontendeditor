<?php

/**
 * holds info about the current edit session
 *
 */

class FrontEndEditorSessionManager extends Object
{

    /**
     *
     * @var null | DataObject
     */
    private static $_can_edit_object = null;

    /**
     * returns ClassName,ID
     * e.g. Page,123 or MySpeciaPage,222
     *
     * @return string
     */
    public static function get_root_can_edit_object_string()
    {
        $obj = self::get_can_edit_object();
        if ($obj) {
            return $obj->ClassName.",".$obj->ID;
        }
        return "DataObject,0";
    }

    /**
     * @return DataObject
     */
    public static function get_can_edit_object()
    {
        if (!self::$_can_edit_object) {
            $objectString = Session::get("FrontEndEditorSessionManagerObjectString");
            if ($objectString) {
                list($className, $id)  = explode(",", $objectString);
                self::$_can_edit_object = $className::get()->byID($id);
            }
        }
        return self::$_can_edit_object;
    }

    /**
     * @param DataObject $object
     * @return string
     */
    public static function set_can_edit_object($object)
    {
        $objectString = Session::set("FrontEndEditorSessionManagerObjectString", $object->ClassName.",".$object->ID);
        self::$_can_edit_object = $object;
        return $objectString;
    }

    /**
     *
     * @param string $className
     * @return DataList
     */
    public static function editable_lists_based_on_can_edit($className)
    {
        return $className::get()->filter(array("FrontEndRootCanEditObject" => self::get_root_can_edit_object_string()));
    }

    /**
     *
     * @return null | DataObject
     */
    public static function previous_object($currentRecord = null)
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

    public static function clear_previous_object()
    {
        $sequenceNumber = Session::get("FrontEndGoBackSequenceNumber");
        Session::set("FrontEndGoBackSequenceNumber", $sequenceNumber - 1);
        Session::clear("FrontEndGoBackObjectDetails".$sequenceNumber);
        Session::save();
    }
}
