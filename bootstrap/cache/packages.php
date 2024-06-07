<?php return array (
  'amamarul/integer-hashids' => 
  array (
    'providers' => 
    array (
      0 => 'Amamarul\\Hashids\\HashidsServiceProvider',
    ),
    'aliases' => 
    array (
    ),
  ),
  'aws/aws-sdk-php-laravel' => 
  array (
    'providers' => 
    array (
      0 => 'Aws\\Laravel\\AwsServiceProvider',
    ),
    'aliases' => 
    array (
      'AWS' => 'Aws\\Laravel\\AwsFacade',
    ),
  ),
  'barryvdh/laravel-dompdf' => 
  array (
    'providers' => 
    array (
      0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    ),
    'aliases' => 
    array (
      'PDF' => 'Barryvdh\\DomPDF\\Facade',
    ),
  ),
  'edujugon/push-notification' => 
  array (
    'providers' => 
    array (
      0 => 'Edujugon\\PushNotification\\Providers\\PushNotificationServiceProvider',
    ),
    'aliases' => 
    array (
      'PushNotification' => 'Edujugon\\PushNotification\\Facades\\PushNotification',
    ),
  ),
  'laravel/socialite' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Socialite\\SocialiteServiceProvider',
    ),
    'aliases' => 
    array (
      'Socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'laravelcollective/html' => 
  array (
    'providers' => 
    array (
      0 => 'Collective\\Html\\HtmlServiceProvider',
    ),
    'aliases' => 
    array (
      'Form' => 'Collective\\Html\\FormFacade',
      'Html' => 'Collective\\Html\\HtmlFacade',
    ),
  ),
  'maatwebsite/excel' => 
  array (
    'providers' => 
    array (
      0 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    ),
    'aliases' => 
    array (
      'Excel' => 'Maatwebsite\\Excel\\Facades\\Excel',
    ),
  ),
  'nesbot/carbon' => 
  array (
    'providers' => 
    array (
      0 => 'Carbon\\Laravel\\ServiceProvider',
    ),
  ),
  'semyonchetvertnyh/laravel-apn-notification-channel' => 
  array (
    'providers' => 
    array (
      0 => 'SemyonChetvertnyh\\ApnNotificationChannel\\ApnServiceProvider',
    ),
  ),
);