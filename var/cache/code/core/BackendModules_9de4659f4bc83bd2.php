<?php
return array (
  'content' => 
  array (
    'labels' => 'core.modules.content',
    'iconIdentifier' => 'modulegroup-web',
    'navigationComponent' => '@typo3/backend/tree/page-tree-element',
    'aliases' => 
    array (
      0 => 'web',
    ),
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'media' => 
  array (
    'position' => 
    array (
      'after' => 'content',
    ),
    'labels' => 'core.modules.media',
    'iconIdentifier' => 'modulegroup-file',
    'navigationComponent' => '@typo3/backend/tree/file-storage-tree-container',
    'aliases' => 
    array (
      0 => 'file',
    ),
    'appearance' => 
    array (
      'promotesSingleSubmoduleToStandalone' => true,
    ),
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'site' => 
  array (
    'labels' => 'core.modules.site',
    'workspaces' => 'live',
    'iconIdentifier' => 'modulegroup-site',
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'user' => 
  array (
    'labels' => 'core.modules.user',
    'iconIdentifier' => 'modulegroup-user',
    'workspaces' => '*',
    'appearance' => 
    array (
      'renderInModuleMenu' => false,
    ),
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'admin' => 
  array (
    'labels' => 'core.modules.admin',
    'iconIdentifier' => 'modulegroup-tools',
    'aliases' => 
    array (
      0 => 'tools',
    ),
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'system' => 
  array (
    'labels' => 'core.modules.system',
    'iconIdentifier' => 'modulegroup-system',
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'integrations' => 
  array (
    'parent' => 'admin',
    'position' => 
    array (
      'after' => 'permissions_pages',
    ),
    'access' => 'admin',
    'workspaces' => 'live',
    'path' => '/module/integrations',
    'iconIdentifier' => 'module-integrations',
    'labels' => 'core.modules.integrations',
    'appearance' => 
    array (
      'dependsOnSubmodules' => true,
    ),
    'showSubmoduleOverview' => true,
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'help' => 
  array (
    'labels' => 'core.modules.help',
    'iconIdentifier' => 'modulegroup-help',
    'appearance' => 
    array (
      'renderInModuleMenu' => false,
    ),
    'packageName' => 'typo3/cms-core',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-core/Classes/../',
  ),
  'system_maintenance' => 
  array (
    'parent' => 'system',
    'access' => 'systemMaintainer',
    'position' => 
    array (
      'before' => '*',
    ),
    'path' => '/module/system/maintenance',
    'aliases' => 
    array (
      0 => 'tools_toolsmaintenance',
    ),
    'iconIdentifier' => 'module-install-maintenance',
    'labels' => 'install.modules.maintenance',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Install\\Controller\\BackendModuleController::maintenanceAction',
      ),
    ),
    'routeOptions' => 
    array (
      'sudoMode' => 
      array (
        'group' => 'systemMaintainer',
        'lifetime' => 
        \TYPO3\CMS\Backend\Security\SudoMode\Access\AccessLifetime::medium,
      ),
    ),
    'packageName' => 'typo3/cms-install',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-install/Classes/../',
  ),
  'system_settings' => 
  array (
    'parent' => 'system',
    'access' => 'systemMaintainer',
    'position' => 
    array (
      'before' => '*',
    ),
    'path' => '/module/system/settings',
    'aliases' => 
    array (
      0 => 'tools_toolssettings',
    ),
    'iconIdentifier' => 'module-install-settings',
    'labels' => 'install.modules.settings',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Install\\Controller\\BackendModuleController::settingsAction',
      ),
    ),
    'routeOptions' => 
    array (
      'sudoMode' => 
      array (
        'group' => 'systemMaintainer',
        'lifetime' => 
        \TYPO3\CMS\Backend\Security\SudoMode\Access\AccessLifetime::medium,
      ),
    ),
    'packageName' => 'typo3/cms-install',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-install/Classes/../',
  ),
  'system_upgrade' => 
  array (
    'parent' => 'system',
    'access' => 'systemMaintainer',
    'position' => 
    array (
      'before' => '*',
    ),
    'path' => '/module/system/upgrade',
    'aliases' => 
    array (
      0 => 'tools_toolsupgrade',
    ),
    'iconIdentifier' => 'module-install-upgrade',
    'labels' => 'install.modules.upgrade',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Install\\Controller\\BackendModuleController::upgradeAction',
      ),
    ),
    'routeOptions' => 
    array (
      'sudoMode' => 
      array (
        'group' => 'systemMaintainer',
        'lifetime' => 
        \TYPO3\CMS\Backend\Security\SudoMode\Access\AccessLifetime::medium,
      ),
    ),
    'packageName' => 'typo3/cms-install',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-install/Classes/../',
  ),
  'system_environment' => 
  array (
    'parent' => 'system',
    'access' => 'systemMaintainer',
    'position' => 
    array (
      'before' => '*',
    ),
    'path' => '/module/system/environment',
    'aliases' => 
    array (
      0 => 'tools_toolsenvironment',
    ),
    'iconIdentifier' => 'module-install-environment',
    'labels' => 'install.modules.environment',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Install\\Controller\\BackendModuleController::environmentAction',
      ),
    ),
    'routeOptions' => 
    array (
      'sudoMode' => 
      array (
        'group' => 'systemMaintainer',
        'lifetime' => 
        \TYPO3\CMS\Backend\Security\SudoMode\Access\AccessLifetime::medium,
      ),
    ),
    'packageName' => 'typo3/cms-install',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-install/Classes/../',
  ),
  'web_layout' => 
  array (
    'parent' => 'content',
    'position' => 
    array (
      'before' => '*',
    ),
    'access' => 'user',
    'path' => '/module/web/layout',
    'iconIdentifier' => 'module-page',
    'labels' => 'backend.modules.layout',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\PageLayoutController::mainAction',
      ),
    ),
    'moduleData' => 
    array (
      'viewMode' => 1,
      'showHidden' => true,
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'records' => 
  array (
    'parent' => 'content',
    'position' => 
    array (
      'after' => 'web_layout',
    ),
    'access' => 'user',
    'path' => '/module/content/records',
    'iconIdentifier' => 'module-list',
    'labels' => 'backend.modules.list',
    'aliases' => 
    array (
      0 => 'web_list',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\RecordListController::mainAction',
      ),
    ),
    'moduleData' => 
    array (
      'clipBoard' => true,
      'searchBox' => false,
      'collapsedTables' => 
      array (
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'content_status' => 
  array (
    'parent' => 'content',
    'position' => 
    array (
      'after' => 'web_FormFormbuilder',
      'before' => 'recycler',
    ),
    'access' => 'user',
    'path' => '/module/content/status',
    'iconIdentifier' => 'module-info',
    'labels' => 'backend.modules.status',
    'aliases' => 
    array (
      0 => 'web_info',
    ),
    'navigationComponent' => '@typo3/backend/tree/page-tree-element',
    'appearance' => 
    array (
      'dependsOnSubmodules' => true,
    ),
    'showSubmoduleOverview' => true,
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'site_configuration' => 
  array (
    'parent' => 'site',
    'position' => 
    array (
      'before' => '*',
    ),
    'access' => 'admin',
    'path' => '/module/site/configuration',
    'iconIdentifier' => 'module-sites',
    'labels' => 'backend.modules.site_configuration',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteConfigurationController::overviewAction',
      ),
      'detail' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteConfigurationController::detailAction',
      ),
      'edit' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteConfigurationController::editAction',
      ),
      'save' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteConfigurationController::saveAction',
        'methods' => 
        array (
          0 => 'POST',
        ),
      ),
      'delete' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteConfigurationController::deleteAction',
        'methods' => 
        array (
          0 => 'POST',
        ),
      ),
      'editSettings' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteSettingsController::editAction',
      ),
      'saveSettings' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteSettingsController::saveAction',
        'methods' => 
        array (
          0 => 'POST',
        ),
      ),
      'dumpSettings' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\SiteSettingsController::dumpAction',
        'methods' => 
        array (
          0 => 'POST',
        ),
      ),
    ),
    'moduleData' => 
    array (
      'viewMode' => 'tiles',
      'settingsMode' => 'basic',
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'link_management' => 
  array (
    'parent' => 'site',
    'position' => 
    array (
      'after' => 'site_configuration',
    ),
    'access' => 'user',
    'path' => '/module/link-management',
    'iconIdentifier' => 'module-urls',
    'labels' => 'backend.modules.link_management',
    'appearance' => 
    array (
      'dependsOnSubmodules' => true,
    ),
    'showSubmoduleOverview' => true,
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'about' => 
  array (
    'parent' => 'help',
    'position' => 
    array (
      'before' => '*',
    ),
    'access' => 'user',
    'path' => '/module/help/about',
    'iconIdentifier' => 'module-about',
    'labels' => 'backend.modules.about',
    'aliases' => 
    array (
      0 => 'help_AboutAbout',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\AboutController::handleRequest',
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'pagetsconfig' => 
  array (
    'parent' => 'site',
    'access' => 'admin',
    'path' => '/module/pagetsconfig',
    'iconIdentifier' => 'module-tsconfig',
    'labels' => 'backend.modules.pagetsconfig',
    'navigationComponent' => '@typo3/backend/tree/page-tree-element',
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'pagetsconfig_pages' => 
  array (
    'parent' => 'pagetsconfig',
    'access' => 'admin',
    'path' => '/module/pagetsconfig/records',
    'iconIdentifier' => 'module-tsconfig',
    'labels' => 'backend.modules.pagetsconfig_pages',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\PageTsConfig\\PageTsConfigRecordsOverviewController::handleRequest',
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'pagetsconfig_active' => 
  array (
    'parent' => 'pagetsconfig',
    'access' => 'admin',
    'path' => '/module/pagetsconfig/active',
    'iconIdentifier' => 'module-tsconfig',
    'labels' => 'backend.modules.pagetsconfig_active',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\PageTsConfig\\PageTsConfigActiveController::handleRequest',
      ),
    ),
    'moduleData' => 
    array (
      'sortAlphabetically' => true,
      'displayComments' => true,
      'displayConstantSubstitutions' => true,
      'pageTsConfigConditions' => 
      array (
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'pagetsconfig_includes' => 
  array (
    'parent' => 'pagetsconfig',
    'access' => 'admin',
    'path' => '/module/pagetsconfig/includes',
    'iconIdentifier' => 'module-tsconfig',
    'labels' => 'backend.modules.pagetsconfig_includes',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\PageTsConfig\\PageTsConfigIncludesController::indexAction',
      ),
      'source' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\PageTsConfig\\PageTsConfigIncludesController::sourceAction',
      ),
      'sourceWithIncludes' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Controller\\PageTsConfig\\PageTsConfigIncludesController::sourceWithIncludesAction',
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'content_security_policy' => 
  array (
    'parent' => 'system',
    'access' => 'systemMaintainer',
    'iconIdentifier' => 'module-security',
    'labels' => 'backend.modules.content_security_policy',
    'aliases' => 
    array (
      0 => 'tools_csp',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Backend\\Security\\ContentSecurityPolicy\\CspModuleController::mainAction',
      ),
    ),
    'packageName' => 'typo3/cms-backend',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-backend/Classes/../',
  ),
  'dashboard' => 
  array (
    'position' => 
    array (
      'before' => '*',
    ),
    'standalone' => true,
    'access' => 'user',
    'path' => '/module/dashboard',
    'iconIdentifier' => 'module-dashboard',
    'labels' => 'dashboard.module',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Dashboard\\Controller\\DashboardController::mainAction',
      ),
    ),
    'packageName' => 'typo3/cms-dashboard',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-dashboard/Classes/../',
  ),
  'media_management' => 
  array (
    'parent' => 'media',
    'access' => 'user',
    'path' => '/module/file/list',
    'iconIdentifier' => 'module-filelist',
    'labels' => 'filelist.module',
    'aliases' => 
    array (
      0 => 'file_FilelistList',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Filelist\\Controller\\FileListController::handleRequest',
      ),
    ),
    'moduleData' => 
    array (
      'displayThumbs' => true,
      'clipBoard' => true,
      'sortField' => 'name',
      'sortDirection' => 'asc',
      'viewMode' => NULL,
    ),
    'packageName' => 'typo3/cms-filelist',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-filelist/',
  ),
  'web_FormFormbuilder' => 
  array (
    'parent' => 'content',
    'position' => 
    array (
      'after' => 'workspaces_admin',
    ),
    'access' => 'user',
    'iconIdentifier' => 'module-form',
    'inheritNavigationComponentFromMainModule' => false,
    'labels' => 'form.module',
    'path' => '/module/manage/forms',
    'extensionName' => 'Form',
    'controllerActions' => 
    array (
      'TYPO3\\CMS\\Form\\Controller\\FormManagerController' => 
      array (
        0 => 'index',
        1 => 'show',
        2 => 'create',
        3 => 'duplicate',
        4 => 'references',
        5 => 'delete',
      ),
      'TYPO3\\CMS\\Form\\Controller\\FormEditorController' => 
      array (
        0 => 'index',
        1 => 'saveForm',
        2 => 'renderFormPage',
      ),
    ),
    'packageName' => 'typo3/cms-form',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-form/',
  ),
  'user_setup' => 
  array (
    'parent' => 'user',
    'access' => 'user',
    'path' => '/module/user/setup',
    'iconIdentifier' => 'module-setup',
    'labels' => 'setup.module',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Setup\\Controller\\SetupModuleController::mainAction',
      ),
    ),
    'packageName' => 'typo3/cms-setup',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-setup/',
  ),
  'system_log' => 
  array (
    'parent' => 'admin',
    'position' => 
    array (
      'after' => 'integrations',
    ),
    'access' => 'user',
    'iconIdentifier' => 'module-belog',
    'labels' => 'belog.module',
    'path' => '/module/system/log',
    'aliases' => 
    array (
      0 => 'system_BelogLog',
    ),
    'extensionName' => 'Belog',
    'controllerActions' => 
    array (
      'TYPO3\\CMS\\Belog\\Controller\\BackendLogController' => 
      array (
        0 => 'list',
        1 => 'deleteMessage',
      ),
    ),
    'packageName' => 'typo3/cms-belog',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-belog/',
  ),
  'permissions_pages' => 
  array (
    'parent' => 'admin',
    'position' => 
    array (
      'after' => 'scheduler',
    ),
    'access' => 'admin',
    'path' => '/module/users/permissions',
    'iconIdentifier' => 'module-permission',
    'navigationComponent' => '@typo3/backend/tree/page-tree-element',
    'labels' => 'beuser.modules.permissions',
    'aliases' => 
    array (
      0 => 'system_BeuserTxPermission',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Beuser\\Controller\\PermissionController::handleRequest',
      ),
    ),
    'packageName' => 'typo3/cms-beuser',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-beuser/',
  ),
  'backend_user_management' => 
  array (
    'parent' => 'admin',
    'position' => 
    array (
      'before' => '*',
    ),
    'access' => 'admin',
    'path' => '/module/users/management',
    'iconIdentifier' => 'module-beuser',
    'labels' => 'beuser.modules.user_management',
    'aliases' => 
    array (
      0 => 'system_BeuserTxBeuser',
    ),
    'extensionName' => 'Beuser',
    'controllerActions' => 
    array (
      'TYPO3\\CMS\\Beuser\\Controller\\BackendUserController' => 
      array (
        0 => 'index',
        1 => 'list',
        2 => 'show',
        3 => 'addToCompareList',
        4 => 'removeFromCompareList',
        5 => 'removeAllFromCompareList',
        6 => 'compare',
        7 => 'online',
        8 => 'terminateBackendUserSession',
        9 => 'initiatePasswordReset',
        10 => 'groups',
        11 => 'showGroup',
        12 => 'addGroupToCompareList',
        13 => 'removeGroupFromCompareList',
        14 => 'removeAllGroupsFromCompareList',
        15 => 'compareGroups',
        16 => 'filemounts',
      ),
    ),
    'packageName' => 'typo3/cms-beuser',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-beuser/',
  ),
  'web_info_overview' => 
  array (
    'parent' => 'content_status',
    'position' => 
    array (
      'before' => '*',
    ),
    'access' => 'user',
    'path' => '/module/web/info/overview',
    'iconIdentifier' => 'module-info',
    'labels' => 'info.modules.overview',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Info\\Controller\\PageInformationController::handleRequest',
      ),
    ),
    'moduleData' => 
    array (
      'pages' => '0',
      'depth' => 0,
      'lang' => 0,
    ),
    'packageName' => 'typo3/cms-info',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-info/',
  ),
  'web_info_translations' => 
  array (
    'parent' => 'content_status',
    'position' => 
    array (
      'after' => 'web_info_overview',
    ),
    'access' => 'user',
    'path' => '/module/web/info/translations',
    'iconIdentifier' => 'module-info',
    'labels' => 'info.modules.translations',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Info\\Controller\\TranslationStatusController::handleRequest',
      ),
    ),
    'moduleData' => 
    array (
      'depth' => 0,
      'lang' => 0,
    ),
    'packageName' => 'typo3/cms-info',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-info/',
  ),
  'integrations_reactions' => 
  array (
    'parent' => 'integrations',
    'access' => 'admin',
    'workspaces' => 'live',
    'path' => '/module/integrations/reactions',
    'iconIdentifier' => 'module-reactions',
    'labels' => 'reactions.module',
    'aliases' => 
    array (
      0 => 'system_reactions',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Reactions\\Controller\\ManagementController::handleRequest',
      ),
    ),
    'packageName' => 'typo3/cms-reactions',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-reactions/',
  ),
  'recycler' => 
  array (
    'parent' => 'content',
    'position' => 
    array (
      'after' => 'content_status',
    ),
    'access' => 'user',
    'workspaces' => 'live',
    'path' => '/module/web/recycler',
    'iconIdentifier' => 'module-recycler',
    'labels' => 'recycler.module',
    'aliases' => 
    array (
      0 => 'web_RecyclerRecycler',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Recycler\\Controller\\RecyclerModuleController::handleRequest',
      ),
    ),
    'packageName' => 'typo3/cms-recycler',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-recycler/',
  ),
  'web_ts' => 
  array (
    'parent' => 'site',
    'access' => 'admin',
    'path' => '/module/web/ts',
    'iconIdentifier' => 'module-template',
    'labels' => 'tstemplate.modules.ts',
    'navigationComponent' => '@typo3/backend/tree/page-tree-element',
    'packageName' => 'typo3/cms-tstemplate',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-tstemplate/',
  ),
  'web_typoscript_recordsoverview' => 
  array (
    'parent' => 'web_ts',
    'access' => 'admin',
    'path' => '/module/web/typoscript/records-overview',
    'iconIdentifier' => 'module-template',
    'labels' => 'tstemplate.modules.recordsoverview',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\TemplateRecordsOverviewController::handleRequest',
      ),
    ),
    'packageName' => 'typo3/cms-tstemplate',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-tstemplate/',
  ),
  'web_typoscript_constanteditor' => 
  array (
    'parent' => 'web_ts',
    'access' => 'admin',
    'path' => '/module/web/typoscript/constant-editor',
    'iconIdentifier' => 'module-template',
    'labels' => 'tstemplate.modules.constanteditor',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\ConstantEditorController::handleRequest',
      ),
    ),
    'moduleData' => 
    array (
      'selectedTemplatePerPage' => 
      array (
      ),
      'selectedCategory' => '',
    ),
    'packageName' => 'typo3/cms-tstemplate',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-tstemplate/',
  ),
  'web_typoscript_infomodify' => 
  array (
    'parent' => 'web_ts',
    'access' => 'admin',
    'path' => '/module/web/typoscript/overview',
    'iconIdentifier' => 'module-template',
    'labels' => 'tstemplate.modules.infomodify',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\InfoModifyController::handleRequest',
      ),
    ),
    'moduleData' => 
    array (
      'selectedTemplatePerPage' => 
      array (
      ),
    ),
    'packageName' => 'typo3/cms-tstemplate',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-tstemplate/',
  ),
  'typoscript_active' => 
  array (
    'parent' => 'web_ts',
    'access' => 'admin',
    'path' => '/module/typoscript/active',
    'iconIdentifier' => 'module-template',
    'labels' => 'tstemplate.modules.active',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\ActiveTypoScriptController::indexAction',
      ),
      'edit' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\ActiveTypoScriptController::editAction',
      ),
      'update' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\ActiveTypoScriptController::updateAction',
        'methods' => 
        array (
          0 => 'POST',
        ),
      ),
    ),
    'moduleData' => 
    array (
      'sortAlphabetically' => true,
      'displayConstantSubstitutions' => true,
      'displayComments' => true,
      'selectedTemplatePerPage' => 
      array (
      ),
      'constantConditions' => 
      array (
      ),
      'setupConditions' => 
      array (
      ),
    ),
    'packageName' => 'typo3/cms-tstemplate',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-tstemplate/',
  ),
  'web_typoscript_analyzer' => 
  array (
    'parent' => 'web_ts',
    'access' => 'admin',
    'path' => '/module/web/typoscript/analyzer',
    'iconIdentifier' => 'module-template',
    'labels' => 'tstemplate.modules.analyzer',
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\TemplateAnalyzerController::indexAction',
      ),
      'source' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\TemplateAnalyzerController::sourceAction',
      ),
      'sourceWithIncludes' => 
      array (
        'target' => 'TYPO3\\CMS\\Tstemplate\\Controller\\TemplateAnalyzerController::sourceWithIncludesAction',
      ),
    ),
    'moduleData' => 
    array (
      'selectedTemplatePerPage' => 
      array (
      ),
    ),
    'packageName' => 'typo3/cms-tstemplate',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-tstemplate/',
  ),
  'page_preview' => 
  array (
    'parent' => 'content',
    'position' => 
    array (
      'after' => 'records',
    ),
    'access' => 'user',
    'path' => '/module/page-preview',
    'iconIdentifier' => 'module-viewpage',
    'labels' => 'viewpage.module',
    'aliases' => 
    array (
      0 => 'web_ViewpageView',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Viewpage\\Controller\\ViewModuleController::handleRequest',
      ),
    ),
    'packageName' => 'typo3/cms-viewpage',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-viewpage/',
  ),
  'integrations_webhooks' => 
  array (
    'parent' => 'integrations',
    'access' => 'admin',
    'workspaces' => 'live',
    'path' => '/module/integrations/webhooks',
    'iconIdentifier' => 'module-webhooks',
    'labels' => 'webhooks.module',
    'aliases' => 
    array (
      0 => 'webhooks_management',
    ),
    'routes' => 
    array (
      '_default' => 
      array (
        'target' => 'TYPO3\\CMS\\Webhooks\\Controller\\ManagementController::overviewAction',
      ),
    ),
    'packageName' => 'typo3/cms-webhooks',
    'absolutePackagePath' => '/var/www/html/vendor/typo3/cms-webhooks/',
  ),
  'web_MensCircleEvents' => 
  array (
    'path' => '/mens-circle/events',
    'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:module.events',
    'iconidentifier' => 'tx-menscircle-event',
    'navigationComponents' => 
    array (
      0 => 'app:module-tree',
    ),
    'packageName' => 'beardcoder/mens-circle',
    'absolutePackagePath' => '/var/www/html/vendor/beardcoder/mens-circle/',
  ),
  'web_MensCircleNewsletter' => 
  array (
    'path' => '/mens-circle/newsletter',
    'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:module.newsletter',
    'iconidentifier' => 'tx-menscircle-newsletter',
    'navigationComponents' => 
    array (
      0 => 'app:module-tree',
    ),
    'packageName' => 'beardcoder/mens-circle',
    'absolutePackagePath' => '/var/www/html/vendor/beardcoder/mens-circle/',
  ),
);
#