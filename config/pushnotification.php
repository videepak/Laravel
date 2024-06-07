<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false,
      'apiKey' => 'My_ApiKey',
  ],
  'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'AAAAfS_rMEA:APA91bGQ8GBm9Y4ARo_DWEYtXveUclW21pMPKhb17wf55_9MQ_LgSrrNX8q13Gw55e87Rwbaud9O13Q0NJ7Ti_3dY8vXHCToDFKNEziCwdBIUmisQP0gXNdI_gEZSMN4AP9-LCL82-Lh',
  ],
  'apn' => [
      'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
      'passPhrase' => '1234', //Optional
      'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
      'dry_run' => true
  ]
];