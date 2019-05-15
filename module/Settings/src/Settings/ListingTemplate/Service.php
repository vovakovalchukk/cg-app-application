<?php
namespace Settings\ListingTemplate;

class Service
{
    const FEATURE_FLAG = 'Ebay Listing Templates';

    public function getListingTemplateTags(){
        return [
            ['id'=> 1, 'tag' => 'title', 'label' => 'title'],
            ['id'=> 2, 'tag' => 'description', 'label' => 'description'],
            ['id'=> 2, 'tag' => 'bin', 'label' => 'bin'],
            ['id'=> 2, 'tag' => 'brand', 'label' => 'brand'],
            ['id'=> 2, 'tag' => 'manufacturer', 'label' => 'manufacturer'],
            ['id'=> 2, 'tag' => 'barcode', 'label' => 'barcode'],
            ['id'=> 2, 'tag' => 'weight', 'label' => 'weight'],
            ['id'=> 2, 'tag' => 'tax', 'label' => 'tax'],
            ['id'=> 2, 'tag' => 'condition', 'label' => 'condition'],
            ['id'=> 2, 'tag' => 'image1', 'label' => 'image1'],
            ['id'=> 2, 'tag' => 'image2', 'label' => 'image2'],
            ['id'=> 2, 'tag' => 'image3', 'label' => 'image3'],
            ['id'=> 2, 'tag' => 'image4', 'label' => 'image4'],
            ['id'=> 2, 'tag' => 'image5', 'label' => 'image5'],
            ['id'=> 2, 'tag' => 'image6', 'label' => 'image6'],
            ['id'=> 2, 'tag' => 'image7', 'label' => 'image7'],
            ['id'=> 2, 'tag' => 'image8', 'label' => 'image8'],
            ['id'=> 2, 'tag' => 'image9', 'label' => 'image9'],
            ['id'=> 2, 'tag' => 'image10', 'label' => 'image10']
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
                'name' => 'template2',
                'content' => "<h1>Template 2 Title</h1>
                    some content in the template
                "
            ],
            [
                'id' => 3,
                'name' => 'template3',
                'content' => "<h1>Template 3 Title</h1>
                    some content in the template
                "
            ]
        ];
    }
}
