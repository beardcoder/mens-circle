<?php
return array (
  'orderedSets' => 
  array (
    'typo3/form' => 
    \TYPO3\CMS\Core\Site\Set\SetDefinition::__set_state(array(
       'name' => 'typo3/form',
       'label' => 'Form Framework',
       'dependencies' => 
      array (
      ),
       'optionalDependencies' => 
      array (
      ),
       'settingsDefinitions' => 
      array (
      ),
       'categoryDefinitions' => 
      array (
      ),
       'typoscript' => 'EXT:form/Configuration/Sets/Form/',
       'pagets' => 'EXT:form/Configuration/Sets/Form/page.tsconfig',
       'settings' => 
      array (
      ),
       'hidden' => false,
       'routeEnhancers' => 
      array (
      ),
    )),
    'beardcoder/mens-circle' => 
    \TYPO3\CMS\Core\Site\Set\SetDefinition::__set_state(array(
       'name' => 'beardcoder/mens-circle',
       'label' => 'Männerkreis Niederbayern',
       'dependencies' => 
      array (
        0 => 'typo3/form',
      ),
       'optionalDependencies' => 
      array (
      ),
       'settingsDefinitions' => 
      array (
      ),
       'categoryDefinitions' => 
      array (
      ),
       'typoscript' => 'EXT:mens_circle/Configuration/Sets/MensCircle/',
       'pagets' => 'EXT:mens_circle/Configuration/Sets/MensCircle/page.tsconfig',
       'settings' => 
      array (
        'settings' => 
        array (
          'tx_menscircle' => 
          array (
            'siteName' => 'Männerkreis Niederbayern / Straubing',
            'siteTagline' => 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer',
            'siteDescription' => 'Männerkreis Niederbayern - Eine Plattform für Männerkreis-Veranstaltungen in Niederbayern und Straubing.',
            'contactEmail' => '',
            'contactPhone' => '',
            'location' => 'Straubing, Niederbayern',
            'whatsappCommunityLink' => '',
            'eventPageId' => 0,
            'footerPageId' => 0,
            'impressumPageId' => 0,
            'datenschutzPageId' => 0,
            'features' => 
            array (
              'enableNewsletter' => true,
              'enableTestimonials' => true,
              'enableSmsNotifications' => false,
            ),
            'sms' => 
            array (
              'apiKey' => '',
              'senderName' => 'Maennerkreis',
            ),
            'email' => 
            array (
              'fromEmail' => '',
              'fromName' => 'Männerkreis Niederbayern',
            ),
          ),
        ),
      ),
       'hidden' => false,
       'routeEnhancers' => 
      array (
      ),
    )),
    'typo3/seo-sitemap' => 
    \TYPO3\CMS\Core\Site\Set\SetDefinition::__set_state(array(
       'name' => 'typo3/seo-sitemap',
       'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:label',
       'dependencies' => 
      array (
      ),
       'optionalDependencies' => 
      array (
      ),
       'settingsDefinitions' => 
      array (
        0 => 
        \TYPO3\CMS\Core\Settings\SettingDefinition::__set_state(array(
           'key' => 'seo.sitemap.view.templateRootPath',
           'type' => 'string',
           'default' => 'EXT:seo/Resources/Private/Templates/',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.seo.sitemap.view.templateRootPath',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.description.seo.sitemap.view.templateRootPath',
           'readonly' => false,
           'enum' => 
          array (
          ),
           'category' => 'seo.templates',
           'tags' => 
          array (
          ),
           'options' => 
          array (
          ),
        )),
        1 => 
        \TYPO3\CMS\Core\Settings\SettingDefinition::__set_state(array(
           'key' => 'seo.sitemap.view.partialRootPath',
           'type' => 'string',
           'default' => 'EXT:seo/Resources/Private/Partials/',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.seo.sitemap.view.partialRootPath',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.description.seo.sitemap.view.partialRootPath',
           'readonly' => false,
           'enum' => 
          array (
          ),
           'category' => 'seo.templates',
           'tags' => 
          array (
          ),
           'options' => 
          array (
          ),
        )),
        2 => 
        \TYPO3\CMS\Core\Settings\SettingDefinition::__set_state(array(
           'key' => 'seo.sitemap.view.layoutRootPath',
           'type' => 'string',
           'default' => 'EXT:seo/Resources/Private/Layouts/',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.seo.sitemap.view.layoutRootPath',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.description.seo.sitemap.view.layoutRootPath',
           'readonly' => false,
           'enum' => 
          array (
          ),
           'category' => 'seo.templates',
           'tags' => 
          array (
          ),
           'options' => 
          array (
          ),
        )),
        3 => 
        \TYPO3\CMS\Core\Settings\SettingDefinition::__set_state(array(
           'key' => 'seo.sitemap.pages.excludedDoktypes',
           'type' => 'string',
           'default' => '3, 4, 6, 7, 199, 254',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.seo.sitemap.pages.excludedDoktypes',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.description.seo.sitemap.pages.excludedDoktypes',
           'readonly' => false,
           'enum' => 
          array (
          ),
           'category' => 'seo',
           'tags' => 
          array (
          ),
           'options' => 
          array (
          ),
        )),
        4 => 
        \TYPO3\CMS\Core\Settings\SettingDefinition::__set_state(array(
           'key' => 'seo.sitemap.pages.excludePagesRecursive',
           'type' => 'string',
           'default' => '',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.seo.sitemap.pages.excludePagesRecursive',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.description.seo.sitemap.pages.excludePagesRecursive',
           'readonly' => false,
           'enum' => 
          array (
          ),
           'category' => 'seo',
           'tags' => 
          array (
          ),
           'options' => 
          array (
          ),
        )),
        5 => 
        \TYPO3\CMS\Core\Settings\SettingDefinition::__set_state(array(
           'key' => 'seo.sitemap.pages.additionalWhere',
           'type' => 'string',
           'default' => '{#no_index} = 0 AND {#canonical_link} = \'\'',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.seo.sitemap.pages.additionalWhere',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:settings.description.seo.sitemap.pages.additionalWhere',
           'readonly' => false,
           'enum' => 
          array (
          ),
           'category' => 'seo',
           'tags' => 
          array (
          ),
           'options' => 
          array (
          ),
        )),
      ),
       'categoryDefinitions' => 
      array (
        0 => 
        \TYPO3\CMS\Core\Settings\CategoryDefinition::__set_state(array(
           'key' => 'seo',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:categories.seo',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:categories.description.seo',
           'icon' => NULL,
           'parent' => NULL,
        )),
        1 => 
        \TYPO3\CMS\Core\Settings\CategoryDefinition::__set_state(array(
           'key' => 'seo.templates',
           'label' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:categories.seo.templates',
           'description' => 'LLL:EXT:seo/Configuration/Sets/Sitemap/labels.xlf:categories.description.seo.templates',
           'icon' => NULL,
           'parent' => 'seo',
        )),
      ),
       'typoscript' => 'EXT:seo/Configuration/Sets/Sitemap/',
       'pagets' => 'EXT:seo/Configuration/Sets/Sitemap/page.tsconfig',
       'settings' => 
      array (
      ),
       'hidden' => false,
       'routeEnhancers' => 
      array (
        'PageTypeSuffix' => 
        array (
          'type' => 'PageType',
          'map' => 
          array (
            'sitemap.xml' => 1533906435,
          ),
        ),
        'Sitemap' => 
        array (
          'type' => 'Simple',
          'routePath' => 'sitemap-type/{sitemap}',
          'aspects' => 
          array (
            'sitemap' => 
            array (
              'type' => 'StaticValueMapper',
              'map' => 
              array (
                'pages' => 'pages',
              ),
            ),
          ),
          '_arguments' => 
          array (
            'sitemap' => 'tx_seo/sitemap',
          ),
        ),
      ),
    )),
  ),
  'invalidSets' => 
  array (
  ),
);
#