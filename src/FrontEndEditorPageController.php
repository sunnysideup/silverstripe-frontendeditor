<?php

namespace SunnySideUp\FrontendEditor;

use PageController;













use SilverStripe\View\Requirements;
use SilverStripe\ORM\DataObject;
use SunnySideUp\FrontendEditor\Api\FrontEndEditorSessionManager;
use SilverStripe\Forms\Form;
use SunnySideUp\FrontendEditor\Forms\FrontEndEditForm;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SunnySideUp\FrontendEditor\Interfaces\FrontEndEditable;
use SilverStripe\Security\Permission;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SunnySideUp\FrontendEditor\Model\FrontEndDataExtension;
use SunnySideUp\FrontendEditor\Api\FrontEndEditorPreviousAndNextProvider;



class FrontEndEditorPageController extends PageController
{


    /**
     * the main parent classname / model
     * for editing front-end data.
     * @var string
     */
    private static $default_model = "Provider";

    /**
     *
     * @var DataObject|null
     */
    protected $recordBeingEdited = null;

    /**
     *
     * @var DataObject|null
     */
    protected $rootParentObject = null;

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
        "frontendremoverelation" => "->canEditCurrentRecord",
        "showsequences" => true,
        "startsequence" => true,
        "stopsequence" => true,
        "gotopreviouspageinsequence" => true,
        "gotonextpageinsequence" => true,
        "gotoaddanother" => true,
        "debugsequencer" => 'ADMIN'
    );


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD:     public function init() (ignore case)
  * NEW:     protected function init() (COMPLEX)
  * EXP: Controller init functions are now protected  please check that is a controller.
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    protected function init()
    {
        parent::init();
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        Requirements::themedCSS("sunnysideup/frontendeditor: FrontEndEditForm", "frontendeditor");
        $model = $this->request->param("ID");
        if (!$model) {
            $model = $this->Config()->get("default_model");
        }
        if ($model && is_subclass_of($model, DataObject::class, true)) {
            $id = $this->request->param("OtherID");
            if ($id) {
                $this->recordBeingEdited = $model::get()->byID($id);
                if ($this->recordBeingEdited) {
                    $this->rootParentObject = $this->recordBeingEdited->FrontEndRootParentObject();
                    FrontEndEditorSessionManager::set_can_edit_object($this->rootParentObject);
                }
            }
            if (! $this->recordBeingEdited) {
                $this->recordBeingEdited = $model::create();
            }
        }
        Requirements::javascript("sunnysideup/frontendeditor: app/javascript/RecentlyEdited.js");
    }

    public function index()
    {
        return [];
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
        $form = FrontEndEditForm::create($this, Form::class, $this->recordBeingEdited);
        if ($this->recordBeingEdited) {
            if ($this->recordBeingEdited->hasMethod("ExtraClassesForFrontEndForm")) {
                $form->addExtraClass($this->recordBeingEdited->ExtraClassesForFrontEndForm());
            }
        }
        return $form;
    }

    /**
     * @return null | ArrayList
     */
    public function AlternativeViewLinks()
    {
        $record = $this->RecordBeingEdited();
        if ($record) {
            $array = [];
            if ($this->ViewLink()) {
                $array['VIEW'] = array(
                    'Title' => 'Read Only',
                    'Description' => 'Non Editable version of the data you are entering',
                    'Link' => $this->ViewLink()
                );
            }
            if ($record->hasMethod('CMSEditLink')) {
                $array['EDIT'] = array(
                    'Title' => 'CMS',
                    'Description' => 'Edit this record in the CMS (back-end)',
                    'Link' => $record->CMSEditLink()
                );
            }
            if (
                $this->rootParentObject &&
                $this->rootParentObject->exists() &&
                ! $record->FrontEndIsRoot() &&
                $this->rootParentObject->hasMethod('FrontEndEditLink')
            ) {
                $array['ROOT'] = array(
                    'Title' => $this->rootParentObject->getTitle(),
                    'Description' => 'The root parent of this object',
                    'Link' => $this->rootParentObject->FrontEndEditLink()
                );
            }
            $array = $record->FrontEndAlternativeViewLinks($array);
            if (count($array)) {
                $al = ArrayList::create();
                foreach ($array as $item) {
                    $al->push(ArrayData::create($item));
                }
                return $al;
            }
        }
    }


    public function edit()
    {
        if (isset($_GET["ajax"]) && $_GET["ajax"]) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: ->RenderWith( (ignore case)
  * NEW: ->RenderWith( (COMPLEX)
  * EXP: Check that the template location is still valid!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            return $this->RenderWith("FrontEndEditorPageAjaxVersion");
        }
        if (! $this->recordBeingEdited) {
            return $this->httpError(404);
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
        $this->Title = $title;

        return [];
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
                    if ($foreignObject->hasMethod($fieldOrMethod)) {
                        if ($value === true) {
                            $foreignObject->$fieldOrMethod();
                        } else {
                            $foreignObject->$fieldOrMethod($value);
                        }
                    } elseif ($foreignObject->hasField($fieldOrMethod)) {
                        $foreignObject->$fieldOrMethod = $value;
                        $foreignObject->write();
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
        if (Director::is_ajax()) {
            return 'success';
        } else {
            return $this->redirectBack();
        }
    }


    /**
     *
     */
    public function frontendaddrelation()
    {
        Config::modify()->merge(DataObject::class, 'validation_enabled', false);
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
        //Controller::curr()->getRequest()->getSession()->save();
        return $this->redirect($obj->FrontEndEditLink());
    }

    protected function addGoBackLink()
    {
        return FrontEndEditorSessionManager::add_go_back_link($this->recordBeingEdited);
    }

    /**
     *
     * @return null | arraylist
     */
    public function GoBackLinks()
    {
        $al = null;
        $alDone = array($this->recordBeingEdited->ClassName.",".$this->recordBeingEdited->ID => true);
        $sequenceNumber = FrontEndEditorSessionManager::get_sequence_number();
        if ($sequenceNumber) {
            for ($i = $sequenceNumber; $i >=0; $i--) {
                $value = FrontEndEditorSessionManager::get_sequence_number_details($i);
                if (!isset($alDone[$value])) {
                    $array = explode(",", $value);
                    if (count($array) == 2) {


                        list($className, $id) = $array;


                        $obj = $className::get()->byID($id);
                        if ($obj && $obj->hasExtension(FrontEndDataExtension::class)) {
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
    private static $_front_end_determine_relation_type = [];

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
    private static $_front_end_determine_relation_classname = [];

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


    #####################################
    # SEQUENCES
    #####################################


    public function stopsequence($request)
    {
        FrontEndEditorSessionManager::clear_sequencer();
        return $this->redirect('/');
    }

    public function showsequences($request)
    {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: ->RenderWith( (ignore case)
  * NEW: ->RenderWith( (COMPLEX)
  * EXP: Check that the template location is still valid!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return $this->RenderWith('FrontEndEditorPageStartSequence', 'Page');
    }

    public function startsequence($request) : HTTPResponse
    {


        $className = $this->request->param('ID');


        $startLink = $this->PreviousAndNextProvider($className)
            ->StartSequence()
            ->getPageLink();
        FrontEndEditorSessionManager::set_note_current_record(true);
        if ($startLink) {
            return $this->redirect($startLink);
        } else {
            return $this->redirect('can-not-find-sequence');
        }
    }
    public function gotopreviouspageinsequence($request) : HTTPResponse
    {
        FrontEndEditorSessionManager::set_note_current_record(true);
        $link = $this->PreviousAndNextProvider()->goPreviousPage();

        return $this->redirect($link);
    }

    public function gotonextpageinsequence($request) : HTTPResponse
    {
        FrontEndEditorSessionManager::set_note_current_record(true);
        $link = $this->PreviousAndNextProvider()->goNextPage();

        return $this->redirect($link);
    }

    public function gotoaddanother($request) : HTTPResponse
    {
        FrontEndEditorSessionManager::set_note_current_record(true);
        $link = $this->PreviousAndNextProvider()->goAddAnother();

        return $this->redirect($link);
    }

    public function debugsequencer()
    {
        $html = '';
        if ($this->recordBeingEdited && $this->recordBeingEdited->exists()) {
            //do nothing
        } else {
            $this->recordBeingEdited = FrontEndEditorSessionManager::get_record_being_edited_in_sequence();
        }
        if ($this->HasSequence()) {
            $html .= $this->PreviousAndNextProvider()->debug($this->recordBeingEdited);
        } else {
            $html .= '<h1>There is no active sequence</h1>';
        }
        $this->Content = $html;
        $this->Form = $html;
        return [];
    }


    /**
     * provides the FrontEndEditorPreviousAndNextProvider class
     * that helps going back and forth between items
     *
     * @param string|null $sequencerClassName
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public function PreviousAndNextProvider($sequencerClassName = null) : FrontEndEditorPreviousAndNextProvider
    {
        return FrontEndEditorPreviousAndNextProvider::inst($sequencerClassName, $this->recordBeingEdited);
    }


    /**
     * provides the actual sequence of pages.
     *
     * @return FrontEndEditorPreviousAndNextSequencer|null
     */
    public function CurrentSequence()
    {
        if ($this->HasSequence()) {
            return $this->PreviousAndNextProvider()->getSequencer();
        }
    }

    /**
     * @alias
     * @return bool
     */
    public function InSequence()
    {
        return $this->HasSequence();
    }

    /**
     * @return bool
     */
    public function NotInSequence()
    {
        return ! $this->HasSequence();
    }

    public function HasSequence(): bool
    {
        if (FrontEndEditorSessionManager::get_sequencer()) {
            return $this->PreviousAndNextProvider()->HasSequencer();
        }

        return false;
    }

    /**
     *
     * @return string
     */
    public function StopSequenceLink() : string
    {
        return $this->link('stopsequence');
    }

    /**
     * you must use this link to go to PREV / NEXT
     * @return string
     */
    public function NextSequenceLink() : string
    {
        return $this->Link('gotonextpageinsequence');
    }

    /**
     * you must use this link to go to PREV / NEXT
     * @return string
     */
    public function AddAnotherSequenceLink() : string
    {
        return $this->Link('gotoaddanother');
    }

    /**
     * You muse use this link to go to PREV / NEXT PAGE
     * @return string
     */
    public function PreviousSequenceLink() : string
    {
        return $this->Link('gotopreviouspageinsequence');
    }


    /**
     * @return ArrayList
     */
    public function ListOfSequences()
    {
        return $this->PreviousAndNextProvider()->ListOfSequences();
    }

    /**
     * @return ArrayList
     */
    public function AllPages()
    {
        return $this->PreviousAndNextProvider()->AllPages();
    }
}

