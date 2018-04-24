<?php



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
    public function check($request) : bool
    {
        $className = Convert::raw2sql($this->request->param('ClassName'));
        $id = intval($this->request->param('ID'));
        $field = Convert::raw2sql($this->request->param('FieldName'));
        $value = Convert::raw2sql($this->request->getVar('val'));
        if($this->classAndFieldExist($className, $field)) {
            if($id) {
                $obj = $className::get()->byID($id);
            } else {
                $obj = DataObject::get_one($className);
            }
            if($obj->canEdit()) {
                $validation = $obj->FrontEndFieldsWithAjaxValidation();
                $method = $validation[$field];
                $classAndMethod = explode('.', $method);
                if(count($classAndMethod) === 2) {
                    $method = $classAndMethod[1];
                    $objectForMethod = $obj;
                } else {
                    $method = $classAndMethod[0];
                    $objectForMethod = $this;
                }

                return $obj->$method();
            }
        } else {
            die('you can not access this page.');
        }

        return false;
    }

    protected function checkForDuplicates($className, $ID, $field, $value)
    {
        $others = $className::get()->filter([$field => $value]);
        if($id) {
            $others->exclude(['ID' => $id]);
        }
        if($others->count() > 0) {

            return 'There is another entry with the same value. Please enter a unique value';
        }

        return 'false';
    }


    protected function classAndFieldExist($className, $field) : bool
    {
        if(class_exists($className)) {
            $obj = Injector::inst()->get($className);
            if($obj) {
                $db = $obj->db();

                return isset($db[$field]);
            }
        }

        return false;
    }

}
