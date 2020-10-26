<?php

//TODO: utiliser un plugin de coordinateur pour tester les méthodes HTTP
//$method = $_SERVER['REQUEST_METHOD'];
//if ($method != 'GET') {

    //return $this->apiResponse(
        //'405',
        //'error',
        //'"project/{projectKey}" api entry point only accepts GET request method'
    //);
//}

include jApp::getModulePath('gobs').'controllers/apiController.php';

class projectCtrl extends apiController
{
    /**
     * Check given project can be accessed by the user
     * and that it is a G-Obs project with indicators.
     */
    private function checkProject()
    {
        // Get authenticated user
        $this->authenticate();
        if (!$this->user) {
            return array(
                '401',
                'error',
                'Access token is missing or invalid',
                null,
            );
        }
        $user = $this->user;
        $login = $user['usr_login'];

        // Check projectKey parameter
        $project_key = $this->param('projectKey');
        if (!$project_key) {
            return array(
                '400',
                'error',
                'The projectKey parameter is mandatory',
                null,
            );
        }

        // Check project is valid
        try {
            $project = lizmap::getProject($project_key);
            if (!$project) {
                return array(
                    '404',
                    'error',
                    'The given project key does not refer to a known project',
                    null,
                );
            }
        } catch (UnknownLizmapProjectException $e) {
            return array(
                '404',
                'error',
                'The given project key does not refer to a known project',
                null,
            );
        }

        // Check the authenticated user can access to the project
        if (!$project->checkAclByUser($login)) {
            return array(
                '403',
                'error',
                jLocale::get('view~default.repository.access.denied'),
                null,
            );
        }

        // Get gobs project manager
        jClasses::inc('gobs~Project');
        $gobs_project = new Project($project);

        // Test if project has and indicator
        $indicators = $gobs_project->getProjectIndicators();
        if (!$indicators) {
            return array(
                '404',
                'error',
                'The given project key does not refer to a G-Obs project',
                null,
            );
        }

        // Ok
        return array('200', 'success', 'Project is a G-Obs project', $gobs_project);
    }

    /**
     * Get a project by Key
     * /project/{projectKey}
     * Redirect to specific function depending on http method.
     *
     * @httpparam string Project Key
     *
     * @return jResponseJson Project object or standard api response
     */
    public function getProjectByKey()
    {

        // Check project can be accessed and is a valid G-Obs project
        list($code, $status, $message, $gobs_project) = $this->checkProject();
        if ($status == 'error') {
            return $this->apiResponse(
                $code,
                $status,
                $message
            );
        }

        // Get gobs project object
        $data = $gobs_project->get();

        return $this->objectResponse($data);
    }

    /**
     * Get indicators for a project by project Key
     * /project/{projectKey}/indicators.
     *
     * @param string Project Key
     * @httpresponse JSON Indicator data
     *
     * @return jResponseJson Indicator data
     */
    public function getProjectIndicators()
    {

        // Check project can be accessed and is a valid G-Obs project
        list($code, $status, $message, $gobs_project) = $this->checkProject();
        if ($status == 'error') {
            return $this->apiResponse(
                $code,
                $status,
                $message
            );
        }

        $indicators = $gobs_project->getProjectIndicators();

        return $this->objectResponse($indicators);
    }
}
