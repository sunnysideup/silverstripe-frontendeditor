<?php

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
        'FrontEndEditorSequencerExplanation',
        'FrontEndEditorClassExplanation',
        'FrontEndEditorRightTitle'
    );

    private static $menu_icon = 'frontendeditor/images/treeicons/FrontEndEditorAdmin.png';

}
