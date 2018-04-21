<?php
namespace Products\Controller\CreateMultipleListings;

use Application\Controller\AbstractJsonController;

class JsonController extends AbstractJsonController
{
    const ROUTE_SUBMIT_MULTIPLE_LISTINGS = 'SubmitMultipleListings';
    const ROUTE_SUBMIT_MULTIPLE_LISTINGS_PROGRESS = 'SubmitMultipleListingsProgress';

    public function submitMultipleAction()
    {
        // Dummy Data
        return $this->buildResponse([
            'allowed' => true,
            'guid' => uniqid('', true),
        ]);
    }

    public function submitMultipleProgressAction()
    {
        // Dummy Data
        $statuses = ['started', 'complete', 'error'];
        $warnings = ['This is a warning', 'This is a different warning', 'This is also a warning'];
        $errors = ['This is an error', 'This is a different error', 'ERROR!!!!'];
        $dummyData = [];
        foreach ($this->params()->fromPost('accountId', []) as $accountId) {
            $dummyData[$accountId] = ['categories' => []];
            foreach ($this->params()->fromPost('categoryTemplateId', []) as $categoryTemplateId) {
                $status = $statuses[rand(0, count($statuses) - 1)];
                shuffle($warnings);
                shuffle($errors);
                $dummyData[$accountId]['categories'][$categoryTemplateId] = [
                    'status' => $status,
                    'warnings' => array_slice($warnings, 0, rand(0, count($warnings) - 1)),
                    'errors' => $status == 'error' ? array_slice($errors, 0, rand(1, count($errors) - 1)) : [],
                ];
            }
        }
        return $this->buildResponse($dummyData);
    }
}