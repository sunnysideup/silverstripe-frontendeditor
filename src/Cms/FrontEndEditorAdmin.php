<?php

namespace SunnySideUp\FrontendEditor\Cms;


use SunnySideUp\FrontendEditor\Model\Explanations\FrontEndEditorSequencerExplanation;
use SunnySideUp\FrontendEditor\Model\Explanations\FrontEndEditorClassExplanation;
use SunnySideUp\FrontendEditor\Model\Explanations\FrontEndEditorRightTitle;
use SilverStripe\Admin\ModelAdmin;



/**
 * This is the modeladmin for the providers
 * and the services.
 *
 *
 */


class FrontEndEditorAdmin extends ModelAdmin
{
    private static $url_segment = 'frontendeditor';

    private static $menu_title = 'Front End Editor';

    private static $managed_models = array(
        FrontEndEditorSequencerExplanation::class,
        FrontEndEditorClassExplanation::class,
        FrontEndEditorRightTitle::class
    );

    private static $menu_icon = 'frontendeditor/images/treeicons/FrontEndEditorAdmin.png';
}

