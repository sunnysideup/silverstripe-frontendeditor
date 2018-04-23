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
     * returns true on OK - false on error ...
     * @return bool
     */
    public function check() : bool
    {
        $className = Convert::raw2sql($this->request->param('ClassName'));
        $id = intval($this->request->param('ID'));
        $field = intval($this->request->param('ID'));
        $value = Convert::raw2sql($this->request->getVar('val'));
        if($this->classAndFieldExist($className, $field)) {
            if($id) {
                $obj = $className::get()->byID($id);
            } else {
                $obj = DataObject::get_one($className);
            }
            if($obj->canEdit()) {
                $validation = $obj->Frontxxx();
                $method = $validation[$field];
                if($method === true) {
                    $obj = $this;
                    $method = 'checkForDuplicates';
                }
            }
        }

        return true;
    }

    protected function checkForDuplicates($className, $ID, $field, $value)
    {
        $others = $className::get()->filter([$field => $value])
        if($id) {
            $others->exclude(['ID' => $id]);
        }
        if($others->count()) {

            return false;
        }
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
