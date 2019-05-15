<?php
namespace Settings\ListingTemplate;

class Service
{
    const FEATURE_FLAG = 'Ebay Listing Templates';

    public function getListingTemplateTags(){
        return [
            ['id'=> 1, 'tag' => 'title'],
            ['id'=> 2, 'tag' => 'description'],
            ['id'=> 2, 'tag' => 'bin'],
            ['id'=> 2, 'tag' => 'brand'],
            ['id'=> 2, 'tag' => 'manufacturer'],
            ['id'=> 2, 'tag' => 'barcode'],
            ['id'=> 2, 'tag' => 'weight'],
            ['id'=> 2, 'tag' => 'tax'],
            ['id'=> 2, 'tag' => 'condition'],
            ['id'=> 2, 'tag' => 'image1'],
            ['id'=> 2, 'tag' => 'image2'],
            ['id'=> 2, 'tag' => 'image3'],
            ['id'=> 2, 'tag' => 'image4'],
            ['id'=> 2, 'tag' => 'image5'],
            ['id'=> 2, 'tag' => 'image6'],
            ['id'=> 2, 'tag' => 'image7'],
            ['id'=> 2, 'tag' => 'image8'],
            ['id'=> 2, 'tag' => 'image9'],
            ['id'=> 2, 'tag' => 'image10']
        ];
    }

    public function getUsersTemplates(){
        // todo - replace with non dummy data as part of TAC-433
        return [
            [
                'id' => 1,
                'name' => 'template1',
                'html' => "<h1>Template 1 Title</h1>
                    some content in the template
                "
            ],
            [
                'id' => 2,
                'name' => 'template2',
                'html' => "<h1>Template 2 Title</h1>
                    some content in the template
                "
            ],
            [
                'id' => 3,
                'name' => 'template3',
                'html' => "<h1>Template 3 Title</h1>
                    some content in the template
                "
            ]
        ];
    }
}
