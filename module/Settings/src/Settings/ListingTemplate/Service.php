<?php
namespace Settings\ListingTemplate;

class Service
{
    const FEATURE_FLAG = 'Ebay Listing Templates';

    public function getListingTemplateTags(){
        // todo - replace with non dummy data as part of TAC-433
        return [
            ['id'=> 1, 'tag' => 'title', 'label' => 'title'],
            ['id'=> 2, 'tag' => 'image1', 'label' => 'image1']
        ];
    }
    public function getUsersTemplates(){
        // todo - replace with non dummy data as part of TAC-433
        return [
            [
                'id' => 1,
                'name' => 'template1',
                'content' => "<h1>Template 1 Title</h1>
                    some content in the template
                "
            ],
            [
                'id' => 2,
                'name' => 'template1',
                'content' => "<h1>Template Title</h1>
                    some content in the template
                "
            ],
            [
                'id' => 3,
                'name' => 'template1',
                'content' => "<h1>Template Title</h1>
                    some content in the template
                "
            ]
        ];
    }
}
