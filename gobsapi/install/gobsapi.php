<?php
/**
 * Entry point for G-Obs API.
 *
 * @author    3liz
 * @copyright 2018-2020 3liz
 *
 * @see      http://3liz.com
 *
 * @license   GPL 3
 */
require '../application.init.php';
require JELIX_LIB_CORE_PATH.'request/jClassicRequest.class.php';

checkAppOpened();

// mapping of url to basic url (/module/controller/method)
$mapping = array(
    '/user/login' => '/gobsapi/user/logUserIn',
    '/user/logout' => '/gobsapi/user/logUserOut',
    '/user/projects' => '/gobsapi/user/getUserProjects',

    '/project/:projectKey' => '/gobsapi/project/getProjectByKey',
    '/project/:projectKey/indicators' => '/gobsapi/project/getProjectIndicators',

    '/indicator/:indicatorCode' => '/gobsapi/indicator/getIndicatorByCode',
    '/indicator/:indicatorCode/documents' => '/gobsapi/indicator/getIndicatorDocuments',
    '/indicator/:indicatorCode/observations' => '/gobsapi/indicator/getObservationsByIndicator',
    '/indicator/:indicatorCode/deletedObservations' => '/gobsapi/indicator/getDeletedObservationsByIndicator',

    '/observation' => array(
        'POST' => '/gobsapi/observation/createObservation',
        'PUT' => '/gobsapi/observation/updateObservation',
    ),
    '/observation/:observationId' => array(
        'GET' => '/gobsapi/observation/getObservation',
        'DELETE' => '/gobsapi/observation/deleteObservation',
    ),
);

jApp::loadConfig('gobsapi/config.ini.php');

jApp::setCoord(new jCoordinator());
jApp::coord()->process(new \Gobsapi\Request($mapping));
