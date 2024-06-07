<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define('TRIAL_DAYS', '30');
return [
    'countryCode' => '+91', //For SMS Twillo
    'violationEmailBody' => 'We are informing you that a new violation has been reported against your apartment/home. Please view the following link for details of the violation.',
    'violationEmailSubject' => 'Violation reported from your valet trash provider.',
    'violationTemplateId' => 1,
    'adminRoleId' => 1,
    'propertyManager' => 10,
];
