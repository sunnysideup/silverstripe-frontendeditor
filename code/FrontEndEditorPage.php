<?php


class FrontEndEditorPage extends Page
{
    private static $icon = "frontendeditor/images/treeicons/FrontEndEditorPage";

    private static $description = "";

    public function canCreate($member = null)
    {
        return FrontEndEditorPage::get()->count() ? false : true;
    }


    /**
     * @param DataObject $recordBeingEdited
     * @param string $relationName
     * @param int $foreignID
     *
     * @return string
     *
     */
    public function FrontEndRemoveRelationLink($recordBeingEdited, $relationName, $foreignID)
    {
        return $this->Link(
            "frontendremoverelation/".
            $recordBeingEdited->ClassName."/".
            $recordBeingEdited->ID."/".
            "?goingto=".$relationName.",".$foreignID
        );
    }

    /**
     * @param DataObject $recordBeingEdited
     * @param string $relationName
     * @return string
     */
    public function FrontEndAddRelationLink($recordBeingEdited, $relationName)
    {
        return $this->Link("frontendaddrelation/".
            $recordBeingEdited->ClassName."/".
            $recordBeingEdited->ID."/".
            "?goingto=".$relationName);
    }
}

class FrontEndEditorPage_Controller extends Page_Controller
{


    /**
     * the main parent classname / model
     * for editing front-end data.
     * @var string
     */
    private static $default_model = "Provider";

    protected $recordBeingEdited = null;


    /**
     * An array of actions that can be accessed via a request. Each array element should be an action name, and the
     * permissions or conditions required to allow the user to access it.
     *
     * <code>
     * array (
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
     * );
     * </code>
     *
     * @var array $allowed_actions
     */
    private static $allowed_actions = array(
        "Form" => "->canEditCurrentRecord",
        "edit" => "->canEditCurrentRecord",
        "frontendaddrelation" => "->canEditCurrentRecord",
        "frontendremoverelation" => "->canEditCurrentRecord"
    );

    public function init()
    {
        parent::init();
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        Requirements::themedCSS("FrontEndEditForm", "frontendeditor");
        $model = $this->request->param("ID");
        if (!$model) {
            $model = $this->Config()->get("default_model");
        }
        if ($model && class_exists($model)) {
            $id = $this->request->param("OtherID");
            if ($id) {
                $this->recordBeingEdited = $model::get()->byID($id);
            }
            if (!$this->recordBeingEdited) {
                $this->recordBeingEdited = $model::create();
            }
        }
        Requirements::javascript("mysite/javascript/RecentlyEdited.js");
    }

    public function ViewLink()
    {
        if ($this->recordBeingEdited->canView()) {
            if ($this->recordBeingEdited->hasMethod("Link")) {
                return $this->recordBeingEdited->Link();
            }
        }
    }

    public function RecordBeingEdited()
    {
        return $this->recordBeingEdited;
    }

    public function Form()
    {
        return FrontEndEditForm::create($this, "Form", $this->recordBeingEdited);
    }

    /**
     * @return null | ArrayList
     */
    public function AlternativeViewLinks()
    {
        $record = $this->RecordBeingEdited();
        if($record) {
            $array = array();
            if($this->ViewLink()) {
                $array['VIEW'] = array(
                    'Title' => 'View',
                    'Description' => 'Read-only',
                    'Link' => $this->ViewLink()
                );
            }
            if($record->hasMethod('CMSEditLink')) {
                $array['EDIT'] = array(
                    'Title' => 'CMS',
                    'Description' => 'Edit this record in the CMS (back-end)',
                    'Link' => $record->CMSEditLink()
                );
            }
            $array = $record->FrontEndAlternativeViewLinks($array);
            if(count($array)) {
                $al = ArrayList::create();
                foreach($array as $item) {
                    $al->push(ArrayData::create($item));
                }
                return $al;
            }
        }
    }


    public function edit()
    {
        if (isset($_GET["ajax"]) && $_GET["ajax"]) {
            return $this->renderWith("FrontEndEditorPageAjaxVersion");
        }
        if (!$this->recordBeingEdited) {
            return $this->httpError(404);
        }
        if ($this->recordBeingEdited->ClassName == $this->Config()->get("default_model")) {
            FrontEndEditorSessionManager::set_can_edit_object($this->recordBeingEdited);
        }
        $this->addGoBackLink();
        if ($this->recordBeingEdited instanceof FrontEndEditable) {
            $title = $this->recordBeingEdited->FrontEndShortTitle();
        } else {
            $title = $this->recordBeingEdited->getTitle();
        }
        if (!$title) {
            $title = "[NEW ".$this->recordBeingEdited->singular_name()."]";
        }
        $this->Title = "✎ ".$title;
        return array();
    }


    public function canEditCurrentRecord()
    {
        if ($this->recordBeingEdited && $this->recordBeingEdited->exists()) {
            return $this->recordBeingEdited->canEdit();
        } elseif ($this->recordBeingEdited && $this->recordBeingEdited->canCreate()) {
            return true;
        }
        return Permission::check("ADMIN");
    }


    public function FrontEndEditorBreadCrumbs()
    {
        return $this->recordBeingEdited->FrontEndEditorBreadCrumbs();
    }

    /**
     *
     */
    public function frontendremoverelation()
    {
        $foreignObject = explode(",", $this->request->getVar("goingto"));

        $relationName = $foreignObject[0];
        $foreignID = $foreignObject[1];
        $type = $this->frontEndDetermineRelationType($relationName);
        $foreignClassName = $this->frontEndDetermineRelationClassName($relationName);
        $foreignObject = $foreignClassName::get()->byID($foreignID);
        $deleteAlternatives = $this->recordBeingEdited->frontEndDeleteAlternatives();
        if (isset($deleteAlternatives[$relationName])) {
            if ($foreignObject) {
                foreach ($deleteAlternatives[$relationName] as $fieldOrMethod => $value) {
                    if ($foreignObject->hasField($fieldOrMethod)) {
                        $foreignObject->$fieldOrMethod = $value;
                        $foreignObject->write();
                    } elseif ($foreignObject->hasMethod($fieldOrMethod)) {
                        if ($value === true) {
                            $foreignObject->$fieldOrMethod();
                        } else {
                            $foreignObject->$fieldOrMethod($value);
                        }
                    }
                }
                $foreignObject->write();
            }
        //the else is important so that
        } else {
            switch ($type) {
                case "belongs_to":
                    die("to be completed");
                    break;
                case "has_one":
                    $field = $relationName."ID";
                    $this->recordBeingEdited->$field = 0;
                    break;
                case "has_many":
                case "many_many":
                case "belongs_many_many":
                    $this->recordBeingEdited->$relationName()->remove($foreignObject);
                    break;
            }
            if ($foreignObject && $foreignObject->canDelete()) {
                $foreignObject->delete();
            }
        }
        return $this->redirectBack();
    }


    /**
     *
     */
    public function frontendaddrelation()
    {
        Config::inst()->update('DataObject', 'validation_enabled', false);
        $foreignObject = explode(",", $this->request->getVar("goingto"));
        $relationName = $foreignObject[0];
        $type = $this->frontEndDetermineRelationType($relationName);
        $foreignClassName = $this->frontEndDetermineRelationClassName($relationName);
        $obj = $foreignClassName::create();
        if ($obj instanceof SiteTree) {
            $obj->writeToStage("Stage");
            $obj->publish("Stage", "Live");
        } else {
            $obj->write();
        }
        switch ($type) {
            case "belongs_to":
                die("to be completed");
                $field = $relationName."ID";
                $this->recordBeingEdited->$field = $obj->ID;
                if ($this->recordBeingEdited instanceof SiteTree) {
                    $this->recordBeingEdited->writeToStage("Stage");
                    $this->recordBeingEdited->Publish("Stage", "Live");
                } else {
                    $this->recordBeingEdited->write();
                }
                //we write the object again ...
                if ($obj instanceof SiteTree) {
                    $obj->writeToStage("Stage");
                    $obj->Publish("Stage", "Live");
                } else {
                    $obj->write();
                }
                //print_r($field);
                //print_r($obj->ClassName."-".$obj->ID."-".$obj->ProviderID);
                //print_r($this->recordBeingEdited->ClassName."-".$this->recordBeingEdited->ID."-".$this->recordBeingEdited->OpeningHoursID);
                //die("DD");
                break;
            case "has_one":
                $field = $relationName."ID";
                $this->recordBeingEdited->$field = $obj->ID;
                if ($this->recordBeingEdited instanceof SiteTree) {
                    $this->recordBeingEdited->writeToStage("Stage");
                    $this->recordBeingEdited->Publish("Stage", "Live");
                } else {
                    $this->recordBeingEdited->write();
                }
                //we write the object again ...
                if ($obj instanceof SiteTree) {
                    $obj->writeToStage("Stage");
                    $obj->Publish("Stage", "Live");
                } else {
                    $obj->write();
                }
                //print_r($field);
                //print_r($obj->ClassName."-".$obj->ID."-".$obj->ProviderID);
                //print_r($this->recordBeingEdited->ClassName."-".$this->recordBeingEdited->ID."-".$this->recordBeingEdited->OpeningHoursID);
                //die("DD");
                break;
            case "has_many":
            case "many_many":
            case "belongs_many_many":
                $this->recordBeingEdited->$relationName()->add($obj);
                break;
        }

        return $this->redirectToRelation($obj);
    }

    protected function redirectToRelation($obj)
    {
        Session::save();
        return $this->redirect($obj->FrontEndEditLink());
    }

    protected function addGoBackLink()
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
        if ($backObjectClassName != $this->recordBeingEdited->ClassName) {
            $sequenceNumber++;
            Session::set("FrontEndGoBackSequenceNumber", $sequenceNumber);
            Session::set("FrontEndGoBackObjectDetails".$sequenceNumber, $this->recordBeingEdited->ClassName.",".$this->recordBeingEdited->ID);
            Session::save();
        }
    }

    /**
     *
     * @return null | arraylist
     */
    public function GoBackLinks()
    {
        $al = null;
        $alDone = array($this->recordBeingEdited->ClassName.",".$this->recordBeingEdited->ID => true);
        $sequenceNumber = Session::get("FrontEndGoBackSequenceNumber");
        if ($sequenceNumber) {
            for ($i = $sequenceNumber; $i >=0; $i--) {
                $value = Session::get("FrontEndGoBackObjectDetails".$i);
                if (!isset($alDone[$value])) {
                    $array = explode(",", $value);
                    if (count($array) == 2) {
                        list($className, $id) = $array;
                        $obj = $className::get()->byID($id);
                        if ($obj && $obj->hasExtension('FrontEndDataExtension')) {
                            if (!$al) {
                                $al = ArrayList::create();
                            }
                            $al->push($obj);
                            $alDone[$value] = true;
                        }
                    }
                }
            }
        }
        return $al;
    }

    /**
     *
     * @var array
     */
    private static $_front_end_determine_relation_type = array();

    /**
     * Works out the type of relations for the record being edited.
     * @param string $relationName
     * @return string
     */
    protected function frontEndDetermineRelationType($relationName)
    {
        if (!isset(self::$_front_end_determine_relation_type[$relationName])) {
            $hasOne = Config::inst()->get($this->recordBeingEdited->ClassName, "has_one");
            if (isset($hasOne[$relationName])) {
                self::$_front_end_determine_relation_type[$relationName] =  "has_one";
            } else {
                $hasMany = Config::inst()->get($this->recordBeingEdited->ClassName, "has_many");
                if (isset($hasMany[$relationName])) {
                    self::$_front_end_determine_relation_type[$relationName] =  "has_many";
                } else {
                    $manyMany = Config::inst()->get($this->recordBeingEdited->ClassName, "many_many");
                    if (isset($manyMany[$relationName])) {
                        self::$_front_end_determine_relation_type[$relationName] =  "many_many";
                    } else {
                        $belongsManyMany = Config::inst()->get($this->recordBeingEdited->ClassName, "belongs_many_many");
                        if (isset($belongsManyMany[$relationName])) {
                            self::$_front_end_determine_relation_type[$relationName] =  "belongs_many_many";
                        } else {
                            user_error("type could not be found", E_USER_NOTICE);
                        }
                    }
                }
            }
        }
        return self::$_front_end_determine_relation_type[$relationName];
    }

    /**
     *
     * @var array
     */
    private static $_front_end_determine_relation_classname = array();

    /**
     * Works out class name of the relation
     * @param string $relationName
     * @return string
     */
    protected function frontEndDetermineRelationClassName($relationName)
    {
        if (!isset(self::$_front_end_determine_relation_classname[$relationName])) {
            $hasOne = Config::inst()->get($this->recordBeingEdited->ClassName, "has_one");
            if (isset($hasOne[$relationName])) {
                self::$_front_end_determine_relation_classname = $hasOne[$relationName];
            } else {
                $hasMany = Config::inst()->get($this->recordBeingEdited->ClassName, "has_many");
                if (isset($hasMany[$relationName])) {
                    self::$_front_end_determine_relation_classname = $hasMany[$relationName];
                } else {
                    $manyMany = Config::inst()->get($this->recordBeingEdited->ClassName, "many_many");
                    if (isset($manyMany[$relationName])) {
                        self::$_front_end_determine_relation_classname = $manyMany[$relationName];
                    } else {
                        $belongsManyMany = Config::inst()->get($this->recordBeingEdited->ClassName, "belongs_many_many");
                        if (isset($belongsManyMany[$relationName])) {
                            self::$_front_end_determine_relation_classname =  $belongsManyMany[$relationName];
                        } else {
                            user_error("type could not be found", E_USER_NOTICE);
                        }
                    }
                }
            }
        }
        return self::$_front_end_determine_relation_classname;
    }
}
