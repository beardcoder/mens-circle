<?php
return array (
  'typo3/cms-core/response-propagation' => 'TYPO3\\CMS\\Core\\Middleware\\ResponsePropagation',
  'typo3/cms-backend/response-headers' => 'TYPO3\\CMS\\Backend\\Middleware\\AdditionalResponseHeaders',
  'typo3/cms-backend/page-context' => 'TYPO3\\CMS\\Backend\\Middleware\\PageContextInitialization',
  'typo3/cms-backend/site-resolver' => 'TYPO3\\CMS\\Backend\\Middleware\\SiteResolver',
  'typo3/cms-backend/sudo-mode-interceptor' => 'TYPO3\\CMS\\Backend\\Middleware\\SudoModeInterceptor',
  'typo3/cms-backend/csp-headers' => 'TYPO3\\CMS\\Backend\\Middleware\\ContentSecurityPolicyHeaders',
  'typo3/cms-backend/backend-module-validator' => 'TYPO3\\CMS\\Backend\\Middleware\\BackendModuleValidator',
  'typo3/cms-backend/authentication' => 'TYPO3\\CMS\\Backend\\Middleware\\BackendUserAuthenticator',
  'typo3/cms-reactions/resolver' => 'TYPO3\\CMS\\Reactions\\Http\\Middleware\\ReactionResolver',
  'typo3/cms-core/request-token-middleware' => 'TYPO3\\CMS\\Core\\Middleware\\RequestTokenMiddleware',
  'typo3/cms-backend/backend-routing' => 'TYPO3\\CMS\\Backend\\Middleware\\BackendRouteInitialization',
  'typo3/cms-backend/csp-report' => 'TYPO3\\CMS\\Backend\\Middleware\\ContentSecurityPolicyReporter',
  'typo3/cms-backend/https-redirector' => 'TYPO3\\CMS\\Backend\\Middleware\\ForcedHttpsBackendRedirector',
  'typo3/cms-backend/locked-backend' => 'TYPO3\\CMS\\Backend\\Middleware\\LockedBackendGuard',
  'typo3/cms-core/normalized-params-attribute' => 'TYPO3\\CMS\\Core\\Middleware\\NormalizedParamsAttribute',
);
#