<?php
return array (
  'install.server-response-check.host' => 
  array (
    'access' => 'public',
    'path' => '/install/server-response-check/host',
    'target' => 'TYPO3\\CMS\\Install\\Controller\\ServerResponseCheckController::checkHostAction',
    'packageName' => 'typo3/cms-install',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-install/Classes/../',
  ),
  'install.redirect' => 
  array (
    'access' => 'public',
    'path' => '/install',
    'target' => 'TYPO3\\CMS\\Install\\Controller\\EntryPointRedirectController::redirectAction',
    'packageName' => 'typo3/cms-install',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-install/Classes/../',
  ),
  'install.php.redirect' => 
  array (
    'access' => 'public',
    'path' => '/install.php',
    'target' => 'TYPO3\\CMS\\Install\\Controller\\EntryPointRedirectController::redirectAction',
    'packageName' => 'typo3/cms-install',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-install/Classes/../',
  ),
  'login' => 
  array (
    'path' => '/login',
    'access' => 'public',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LoginController::formAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'main' => 
  array (
    'path' => '/main',
    'referrer' => 'required,refresh-always',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\BackendController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'state-tracker' => 
  array (
    'path' => '/state-tracker',
    'access' => 'public',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\StateTrackerController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'logout' => 
  array (
    'path' => '/logout',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LogoutController::logoutAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'password_forget' => 
  array (
    'path' => '/login/password-reset/forget',
    'access' => 'public',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ResetPasswordController::forgetPasswordFormAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'password_forget_initiate_reset' => 
  array (
    'path' => '/login/password-reset/initiate-reset',
    'access' => 'public',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ResetPasswordController::initiatePasswordResetAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'password_reset_validate' => 
  array (
    'path' => '/login/password-reset/validate',
    'access' => 'public',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ResetPasswordController::passwordResetAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'password_reset_finish' => 
  array (
    'path' => '/login/password-reset/finish',
    'access' => 'public',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ResetPasswordController::passwordResetFinishAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'sudo_mode_module' => 
  array (
    'path' => '/sudo-mode/module',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Security\\SudoModeController::moduleAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'sudo_mode_apply' => 
  array (
    'path' => '/sudo-mode/apply',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Security\\SudoModeController::applyAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'sudo_mode_error' => 
  array (
    'path' => '/sudo-mode/error',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Security\\SudoModeController::errorAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'login_frameset' => 
  array (
    'path' => '/login/frame',
    'access' => 'public',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LoginController::refreshAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'login_request_token' => 
  array (
    'path' => '/login/request-token',
    'access' => 'public',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LoginController::requestTokenAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'auth_mfa' => 
  array (
    'path' => '/auth/mfa',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\MfaController::handleRequest',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'setup_mfa' => 
  array (
    'path' => '/setup/mfa',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\MfaSetupController::handleRequest',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'mfa' => 
  array (
    'path' => '/mfa',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\MfaConfigurationController::handleRequest',
    'sudoMode' => 
    array (
      'group' => 'mfa',
      'lifetime' => 
      \TYPO3\CMS\Backend\Security\SudoMode\Access\AccessLifetime::medium,
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'wizard_add' => 
  array (
    'path' => '/wizard/add',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\AddController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'wizard_list' => 
  array (
    'path' => '/wizard/list',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\ListController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'wizard_edit' => 
  array (
    'path' => '/wizard/edit',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\EditController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'wizard_element_browser' => 
  array (
    'path' => '/wizard/record/browse',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ElementBrowserController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'wizard_link' => 
  array (
    'path' => '/wizard/link/browse',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LinkBrowserController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'online_media' => 
  array (
    'path' => '/online-media',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\OnlineMediaController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'record_download' => 
  array (
    'path' => '/record/download',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\RecordListDownloadController::handleDownloadRequest',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'record_history' => 
  array (
    'path' => '/record/history',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ContentElement\\ElementHistoryController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'db_new' => 
  array (
    'path' => '/record/new',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\NewRecordController::mainAction',
    'redirect' => 
    array (
      'enable' => true,
      'parameters' => 
      array (
        'id' => true,
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'db_new_pages' => 
  array (
    'path' => '/record/new-page',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\NewRecordController::newPageAction',
    'redirect' => 
    array (
      'enable' => true,
      'parameters' => 
      array (
        'id' => true,
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'pages_sort' => 
  array (
    'path' => '/pages/sort',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\SortSubPagesController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'pages_new' => 
  array (
    'path' => '/pages/new',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\NewMultiplePagesController::mainAction',
    'redirect' => 
    array (
      'enable' => true,
      'parameters' => 
      array (
        'id' => true,
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'new_content_element_wizard' => 
  array (
    'path' => '/record/content/wizard/new',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ContentElement\\NewContentElementController::handleRequest',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'move_page' => 
  array (
    'path' => '/page/move',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\MovePageController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'move_element' => 
  array (
    'path' => '/record/move',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ContentElement\\MoveElementController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'show_item' => 
  array (
    'path' => '/record/info',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ContentElement\\ElementInformationController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'dummy' => 
  array (
    'path' => '/empty',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\DummyController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'tce_db' => 
  array (
    'path' => '/record/commit',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\SimpleDataHandlerController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'tce_file' => 
  array (
    'path' => '/file/commit',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\File\\FileController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'record_edit' => 
  array (
    'path' => '/record/edit',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\EditDocumentController::mainAction',
    'redirect' => 
    array (
      'enable' => true,
      'parameters' => 
      array (
        'edit' => true,
        'columnsOnly' => true,
        'module' => true,
      ),
    ),
    'options' => 
    array (
      'requestPageContext' => true,
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'image_processing' => 
  array (
    'path' => '/image/process',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\File\\ImageProcessController::process',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'clipboard_process' => 
  array (
    'path' => '/clipboard/process',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ClipboardController::processRequest',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'resource_request_thumbnail' => 
  array (
    'path' => '/resource/request-thumbnail',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Resource\\ResourceController::requestThumbnailAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'ajax_resource_rename' => 
  array (
    'path' => '/ajax/resource/rename',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Resource\\ResourceController::renameResourceAction',
    'inheritAccessFromModule' => 'media_management',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_resource_gather' => 
  array (
    'path' => '/ajax/resource/gather',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Resource\\ResourceController::gatherInformationAction',
    'inheritAccessFromModule' => 'media_management',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_resource_replace' => 
  array (
    'path' => '/ajax/resource/replace',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Resource\\ResourceController::replaceResourceAction',
    'inheritAccessFromModule' => 'media_management',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_link_resource' => 
  array (
    'path' => '/ajax/link/resource',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LinkController::resourceAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_file_process' => 
  array (
    'path' => '/ajax/file/process',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\File\\FileController::processAjaxRequest',
    'inheritAccessFromModule' => 'media_management',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_file_exists' => 
  array (
    'path' => '/ajax/file/exists',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\File\\FileController::fileExistsInFolderAction',
    'inheritAccessFromModule' => 'media_management',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_file_reference_details' => 
  array (
    'path' => '/ajax/file/reference/details',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormFilesAjaxController::detailsAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_file_reference_create' => 
  array (
    'path' => '/ajax/file/reference/create',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormFilesAjaxController::createAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_file_reference_synchronizelocalize' => 
  array (
    'path' => '/ajax/file/reference/synchronizelocalize',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormFilesAjaxController::synchronizeLocalizeAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_file_reference_expandcollapse' => 
  array (
    'path' => '/ajax/file/reference/expandcollapse',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormFilesAjaxController::expandOrCollapseAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_inline_details' => 
  array (
    'path' => '/ajax/record/inline/details',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormInlineAjaxController::detailsAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_inline_create' => 
  array (
    'path' => '/ajax/record/inline/create',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormInlineAjaxController::createAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_inline_synchronizelocalize' => 
  array (
    'path' => '/ajax/record/inline/synchronizelocalize',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormInlineAjaxController::synchronizeLocalizeAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_inline_expandcollapse' => 
  array (
    'path' => '/ajax/record/inline/expandcollapse',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormInlineAjaxController::expandOrCollapseAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_site_configuration_inline_create' => 
  array (
    'path' => '/ajax/siteconfiguration/inline/create',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteInlineAjaxController::newInlineChildAction',
    'inheritAccessFromModule' => 'site_configuration',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_slug_suggest' => 
  array (
    'path' => '/ajax/record/slug/suggest',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormSlugAjaxController::suggestAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_site_configuration_inline_details' => 
  array (
    'path' => '/ajax/siteconfiguration/inline/details',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteInlineAjaxController::openInlineChildAction',
    'inheritAccessFromModule' => 'site_configuration',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_flex_container_add' => 
  array (
    'path' => '/ajax/record/flex/containeradd',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormFlexAjaxController::containerAdd',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_suggest' => 
  array (
    'path' => '/ajax/wizard/suggest/search',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\SuggestWizardController::searchAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_tree_data' => 
  array (
    'path' => '/ajax/record/tree/fetchData',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FormSelectTreeAjaxController::fetchDataAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_page_tree_data' => 
  array (
    'path' => '/ajax/page/tree/fetchData',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\TreeController::fetchDataAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_page_tree_rootline' => 
  array (
    'path' => '/ajax/page/tree/fetchRootline',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\TreeController::fetchRootlineAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_page_tree_filter' => 
  array (
    'path' => '/ajax/page/tree/filterData',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\TreeController::filterDataAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_page_tree_configuration' => 
  array (
    'path' => '/ajax/page/tree/fetchConfiguration',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\TreeController::fetchConfigurationAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_page_tree_browser_configuration' => 
  array (
    'path' => '/ajax/browser/page/tree/fetchConfiguration',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\TreeController::fetchReadOnlyConfigurationAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_page_tree_set_temporary_mount_point' => 
  array (
    'path' => '/ajax/page/tree/setTemporaryMountPoint',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Page\\TreeController::setTemporaryMountPointAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_filestorage_tree_data' => 
  array (
    'path' => '/ajax/filestorage/tree/fetchData',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FileStorage\\TreeController::fetchDataAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_filestorage_tree_rootline' => 
  array (
    'path' => '/ajax/filestorage/tree/fetchRootline',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FileStorage\\TreeController::fetchRootlineAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_filestorage_tree_filter' => 
  array (
    'path' => '/ajax/filestorage/tree/filterData',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\FileStorage\\TreeController::filterDataAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_shortcut_editform' => 
  array (
    'path' => '/ajax/shortcut/editform',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ShortcutController::showEditFormAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_shortcut_saveform' => 
  array (
    'path' => '/ajax/shortcut/saveform',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ShortcutController::updateAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_shortcut_list' => 
  array (
    'path' => '/ajax/shortcut/list',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ShortcutController::menuAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_shortcut_remove' => 
  array (
    'path' => '/ajax/shortcut/remove',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ShortcutController::removeAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_shortcut_create' => 
  array (
    'path' => '/ajax/shortcut/create',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ShortcutController::addAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_systeminformation_render' => 
  array (
    'path' => '/ajax/system-information/render',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\SystemInformationController::renderMenuAction',
    'parameters' => 
    array (
      'skipSessionUpdate' => 1,
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_modulemenu' => 
  array (
    'path' => '/ajax/module-menu',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\BackendController::getModuleMenu',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_topbar' => 
  array (
    'path' => '/ajax/topbar',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\BackendController::getTopbar',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_login' => 
  array (
    'path' => '/ajax/login',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\AjaxLoginController::loginAction',
    'access' => 'public',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_logout' => 
  array (
    'path' => '/ajax/logout',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\AjaxLoginController::logoutAction',
    'access' => 'public',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_login_preflight' => 
  array (
    'path' => '/ajax/login/preflight',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\AjaxLoginController::preflightAction',
    'access' => 'public',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_login_refresh' => 
  array (
    'path' => '/ajax/login/refresh',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\AjaxLoginController::refreshAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_login_timedout' => 
  array (
    'path' => '/ajax/login/timedout',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\AjaxLoginController::isTimedOutAction',
    'access' => 'public',
    'parameters' => 
    array (
      'skipSessionUpdate' => 1,
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_switch_user' => 
  array (
    'path' => '/ajax/switch/user',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\SwitchUserController::switchUserAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_switch_user_exit' => 
  array (
    'path' => '/ajax/switch/user/exit',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\SwitchUserController::exitSwitchUserAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_mfa' => 
  array (
    'path' => '/ajax/mfa',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\MfaAjaxController::handleRequest',
    'sudoMode' => 
    array (
      'group' => 'mfa',
      'lifetime' => 
      \TYPO3\CMS\Backend\Security\SudoMode\Access\AccessLifetime::medium,
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_contextmenu' => 
  array (
    'path' => '/ajax/context-menu',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ContextMenuController::getContextMenuAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_contextmenu_clipboard' => 
  array (
    'path' => '/ajax/context-menu/clipboard',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ContextMenuController::clipboardAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_process' => 
  array (
    'path' => '/ajax/record/process',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\SimpleDataHandlerController::processAjaxRequest',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_usersettings_process' => 
  array (
    'path' => '/ajax/usersettings/process',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\UserSettingsController::processAjaxRequest',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_image_manipulation' => 
  array (
    'path' => '/ajax/wizard/image-manipulation',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\ImageManipulationController::getWizardContent',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_livesearch' => 
  array (
    'path' => '/ajax/livesearch/search',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LiveSearchController::searchAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_livesearch_form' => 
  array (
    'path' => '/ajax/livesearch/form',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LiveSearchController::formAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_online_media_create' => 
  array (
    'path' => '/ajax/online-media/create',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\OnlineMediaController::createAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_icons' => 
  array (
    'path' => '/ajax/icons',
    'target' => 'TYPO3\\CMS\\Core\\Controller\\IconController::getIcon',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_link_browser_encodetypolink' => 
  array (
    'path' => '/ajax/link-browser/encode-typolink',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\LinkBrowserController::encodeTypoLink',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_localization_get_record' => 
  array (
    'path' => '/ajax/wizard/localization/record',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\LocalizationController::getRecord',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_localization_get_targets' => 
  array (
    'path' => '/ajax/wizard/localization/targets',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\LocalizationController::getTargets',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_localization_get_sources' => 
  array (
    'path' => '/ajax/wizard/localization/sources',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\LocalizationController::getSources',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_localization_get_modes' => 
  array (
    'path' => '/ajax/wizard/localization/modes',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\LocalizationController::getModes',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_localization_get_handlers' => 
  array (
    'path' => '/ajax/wizard/localization/handlers',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\LocalizationController::getHandlers',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_localization_get_content' => 
  array (
    'path' => '/ajax/wizard/localization/content',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\LocalizationController::getContent',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_wizard_localization_localize' => 
  array (
    'path' => '/ajax/wizard/localization/localize',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Wizard\\LocalizationController::localize',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_show_columns' => 
  array (
    'path' => '/ajax/show/columns',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ColumnSelectorController::updateVisibleColumnsAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_show_columns_selector' => 
  array (
    'path' => '/ajax/show/columns/selector',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ColumnSelectorController::showColumnsSelectorAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_records_clearpagecache' => 
  array (
    'path' => '/ajax/records/clearpagecache',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ClearPageCacheController::mainAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_download_settings' => 
  array (
    'path' => '/ajax/record/download/settings',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\RecordListDownloadController::downloadSettingsAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_record_toggle_visibility' => 
  array (
    'path' => '/ajax/record/toggle-visibility',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\RecordListController::toggleRecordVisibilityAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_password_generate' => 
  array (
    'path' => '/ajax/password/generate',
    'target' => 'TYPO3\\CMS\\Core\\Controller\\PasswordGeneratorController::generate',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_security_csp_control' => 
  array (
    'access' => 'systemMaintainer',
    'path' => '/ajax/security/csp/control',
    'target' => 'TYPO3\\CMS\\Backend\\Security\\ContentSecurityPolicy\\CspAjaxController::handleRequest',
    'inheritAccessFromModule' => 'content_security_policy',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_sudo_mode_control' => 
  array (
    'path' => '/ajax/sudo-mode/verify',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\Security\\SudoModeController::verifyAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_codeeditor_tsref' => 
  array (
    'path' => '/ajax/code-editor/tsref',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\CodeEditor\\TypoScriptReferenceController::loadReference',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_codeeditor_codecompletion_loadtemplates' => 
  array (
    'path' => '/ajax/code-editor/codecompletion/load-templates',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\CodeEditor\\CodeCompletionController::loadCompletions',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_color_scheme_update' => 
  array (
    'path' => '/ajax/color-scheme/update',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\ColorSchemeController::updateAction',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_qrcode_generator' => 
  array (
    'path' => '/ajax/qrcode/generate',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\QrCodeController::getQrCodeAction',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_qrcode_download' => 
  array (
    'path' => '/ajax/qrcode/download',
    'target' => 'TYPO3\\CMS\\Backend\\Controller\\QrCodeController::downloadAction',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_dashboards_get' => 
  array (
    'path' => '/ajax/dashboard/dashboards/get',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::getDashboards',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_dashboard_add' => 
  array (
    'path' => '/ajax/dashboard/dashboard/add',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::addDashboard',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_dashboard_edit' => 
  array (
    'path' => '/ajax/dashboard/dashboard/edit',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::editDashboard',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_dashboard_update' => 
  array (
    'path' => '/ajax/dashboard/dashboard/update',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::updateDashboard',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_dashboard_delete' => 
  array (
    'path' => '/ajax/dashboard/dashboard/delete',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::deleteDashboard',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_presets_get' => 
  array (
    'path' => '/ajax/dashboard/presets/get',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::getPresets',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_categories_get' => 
  array (
    'path' => '/ajax/dashboard/categories/get',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::getCategories',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_widget_get' => 
  array (
    'path' => '/ajax/dashboard/widget/get',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::getWidget',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_widget_add' => 
  array (
    'path' => '/ajax/dashboard/widget/add',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::addWidget',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_widget_remove' => 
  array (
    'path' => '/ajax/dashboard/widget/remove',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::removeWidget',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_widget_settings_get' => 
  array (
    'path' => '/ajax/dashboard/widget/settings/get',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::getWidgetSettings',
    'methods' => 
    array (
      0 => 'GET',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'ajax_dashboard_widget_settings_update' => 
  array (
    'path' => '/ajax/dashboard/widget/settings/update',
    'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardAjaxController::updateWidgetSettings',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'inheritAccessFromModule' => 'dashboard',
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
    'ajax' => true,
  ),
  'file_edit' => 
  array (
    'path' => '/file/editcontent',
    'target' => 'TYPO3\\CMS\\Filelist\\Controller\\File\\EditFileController::mainAction',
    'packageName' => 'typo3/cms-filelist',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-filelist/',
  ),
  'file_download' => 
  array (
    'path' => '/file/download',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Filelist\\Controller\\FileDownloadController::handleRequest',
    'packageName' => 'typo3/cms-filelist',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-filelist/',
  ),
  'file_update_online_media' => 
  array (
    'path' => '/file/update-online-media',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Filelist\\Controller\\FileUpdateOnlineMediaController::handleRequest',
    'packageName' => 'typo3/cms-filelist',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-filelist/',
  ),
  'tx_impexp_export' => 
  array (
    'path' => '/record/importexport/export',
    'target' => 'TYPO3\\CMS\\Impexp\\Controller\\ExportController::handleRequest',
    'packageName' => 'typo3/cms-impexp',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-impexp/',
  ),
  'tx_impexp_import' => 
  array (
    'path' => '/record/importexport/import',
    'target' => 'TYPO3\\CMS\\Impexp\\Controller\\ImportController::handleRequest',
    'packageName' => 'typo3/cms-impexp',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-impexp/',
  ),
  'rteckeditor_wizard_browse_links' => 
  array (
    'path' => '/rte/wizard/browselinks',
    'target' => 'TYPO3\\CMS\\RteCKEditor\\Controller\\BrowseLinksController::mainAction',
    'packageName' => 'typo3/cms-rte-ckeditor',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-rte-ckeditor/',
  ),
  'ajax_user_access_permissions' => 
  array (
    'path' => '/ajax/users/access/permissions',
    'target' => 'TYPO3\\CMS\\Beuser\\Controller\\PermissionController::handleAjaxRequest',
    'inheritAccessFromModule' => 'permissions_pages',
    'packageName' => 'typo3/cms-beuser',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-beuser/',
    'ajax' => true,
  ),
  'reaction' => 
  array (
    'path' => '/reaction/{reactionIdentifier?}',
    'access' => 'public',
    'methods' => 
    array (
      0 => 'POST',
    ),
    'target' => 'TYPO3\\CMS\\Reactions\\Http\\ReactionHandler::handleReaction',
    'packageName' => 'typo3/cms-reactions',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-reactions/',
  ),
  'ajax_recycler' => 
  array (
    'path' => '/ajax/recycler',
    'target' => 'TYPO3\\CMS\\Recycler\\Controller\\RecyclerAjaxController::dispatch',
    'inheritAccessFromModule' => 'recycler',
    'packageName' => 'typo3/cms-recycler',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-recycler/',
    'ajax' => true,
  ),
);
#