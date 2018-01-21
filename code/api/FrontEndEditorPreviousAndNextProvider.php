<?php

class FrontEndEditorPreviousAndNextProvider extends Object
{


    /**
     *
     * @var int
     */
    protected $currentPage = 1;

    /**
     *
     * @var FrontEndEditable (DataObject)
     */
    protected $currentRecordBeingEdited = null;

    /**
     * cached variable
     * @var FrontEndEditorPreviousAndNextProvider
     */
    private static $_me_cached = null;

    /**
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public static function inst($currentRecordBeingEdited) : FrontEndEditorPreviousAndNextProvider
    {
        $this->setRecordBeingEdited($currentRecordBeingEdited);
        if(self::$_me_cached === null) {
            self::$_me_cached = Injector::inst()->get('FrontEndEditorPreviousAndNextProvider');
        }

        return self::$_me_cached;
    }

    /**
     *
     * @param  FrontEndEditable (DataObject)
     *
     * @return FrontEndEditorPreviousAndNextProvider
     */
    public function setRecordBeingEdited($currentRecordBeingEdited) : FrontEndEditorPreviousAndNextProvider
    {
        if($currentRecordBeingEdited) {
            $this->currentRecordBeingEdited = $currentRecordBeingEdited;
            $obj = $this->getSequenceProvider();
            $obj->setRecordBeingEdited($currentRecordBeingEdited);
        }

        return $this;
    }

    /**
     * @return ArrayList
     */
    public function ListOfSequences() : ArrayList
    {
        $list = ClassInfo::implementorsOf();
        $page = DataObject::get_one('FrontEndEditorPage');
        foreach($list as $className) {
            $class = Injector::inst()->get($className);
            $array = [
                'Link' => $page->Link('startsequence/'.strtolower($class));
            ]
        }
        $arrayList = ArrayList::create($array);
        $this->extend('UpdateListOfSequences', $arrayList, $currentRecordBeingEdited);

        return $arrayList;
    }

    /**
     *
     * @return bool
     */
    public function isOn()
    {
        return $this->getClassName() ? true : false;
    }

    /**
     *
     * @param string
     * @return this
     */
    public function setSequenceProvider($className, $currentRecordBeingEdited = null)
    {
        if(is_subclass_of($className, 'FrontEndEditorPreviousAndNextSequencer')) {
            $this->setRecordBeingEdited($currentRecordBeingEdited);
            Session::set('FrontEndEditorPreviousAndNextProviderClassName', $className);
        }

        return $this
    }

    /**
     *
     * @param string
     * @return FrontEndEditorPreviousAndNextSequencer
     */
    protected function getSequenceProvider()
    {
        $obj =  Injector::inst()->get($this->getClassName());

        return $obj;
    }

    /**
     *
     * @return string
     */
    protected function getClassName()
    {
        return Session::get('FrontEndEditorPreviousAndNextProviderClassName');
    }


    public function setPage(int $page, $currentRecordBeingEdited = null)
    {
        $this->setCurrentRecordBeingEdited($currentRecordBeingEdited);
        if($page > 0 && $page <= $this->TotalNumberOfPages())
        Session::set('FrontEndEditorPreviousAndNextProviderCurrentPage', $page);

        return $this;
    }


    /**
     *
     * @return int
     */
    public function getPage() : int
    {
        return Session::set('FrontEndEditorPreviousAndNextProviderCurrentPage');
    }


    /**
     *
     * @return string
     */
    public function getPageLink(int $currentPage): string
    {
        return $this->getSequenceProvider()->getLink($this->currentPage);
    }

    /**
     *
     * @return int
     */
    public function TotalNumberOfPages() : int
    {
        return $this->getSequenceProvider()->TotalNumberOfPages();
    }

    /**
     *
     * @return ArrayList
     */
    public function AllLinks() : ArrayList
    {
        return $this->getSequenceProvider()->AllLinks();
    }

    /**
     *
     * @return string
     */
    public function PreviousLink() : string
    {
        $this->currentPage--;

        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function getLink() : string
    {
        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function NextLink() : string
    {
        $this->currentPage++;

        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function goNextPage() : string
    {
        $this->currentPage++;
        $this->setPage($this->currentPage);

        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function goPreviousPage() : string
    {
        $this->currentPage--;
        $this->setPage($this->currentPage);

        return $this->getPageLink();
    }



}
