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
    public static function inst($currentRecordBeingEdited = null)
    {
        if(self::$_me_cached === null) {
            self::$_me_cached = Injector::inst()->get('FrontEndEditorPreviousAndNextProvider');
        }

        return self::$_me_cached;
    }

    /**
     * @return ArrayList
     */
    public function ListOfSequences($currentRecordBeingEdited = null)
    {
        $list = ClassInfo::implementorsOf();
        $page = DataObject::get_one('FrontEndEditorPage');
        foreach($list as $className) {
            $class = Injector::inst()->get($className);
            $array = [
                'Link' => $page->Link('startsequence/'.strtolower($class));
            ]

        }
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
            Session::set('FrontEndEditorPreviousAndNextProviderClassName', $className);
        }

        return $this
    }

    /**
     *
     * @param string
     * @return FrontEndEditorPreviousAndNextSequencer
     */
    protected function getSequenceProvider($currentRecordBeingEdited = null)
    {
        return Injector::inst()->get($this->getClassName());
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
        if($page > 0 && $page <= $this->TotalNumberOfPages())
        Session::set('FrontEndEditorPreviousAndNextProviderCurrentPage', $page);

        return $this;
    }


    /**
     *
     * @return int
     */
    public function getPage()
    {
        return Session::set('FrontEndEditorPreviousAndNextProviderCurrentPage');
    }


    /**
     *
     * @return string
     */
    public function getPageLink(int $currentPage)
    {
        return $this->getSequenceProvider()->getLink($this->currentPage);
    }

    /**
     *
     * @return int
     */
    public function TotalNumberOfPages()
    {
        return $this->getSequenceProvider()->TotalNumberOfPages();
    }

    /**
     *
     * @return ArrayList
     */
    public function AllLinks()
    {
        return $this->getSequenceProvider()->AllLinks();
    }

    /**
     *
     * @return string
     */
    public function PreviousLink()
    {
        $this->currentPage--;

        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function getLink()
    {
        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function NextLink()
    {
        $this->currentPage++;

        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function goNextPage()
    {
        $this->currentPage++;
        $this->setPage($this->currentPage);

        return $this->getPageLink();
    }

    /**
     *
     * @return string
     */
    public function goPreviousPage()
    {
        $this->currentPage--;
        $this->setPage($this->currentPage);

        return $this->getPageLink();
    }



}
