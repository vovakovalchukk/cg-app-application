<?php
namespace Settings\ListingTemplate;

use CG\Image\Collection as ImageCollection;
use CG\Image\Entity as Image;
use CG\Product\Entity as Product;

class PreviewProduct extends Product
{
    const SKU = '5PCS_GARDEN_DINING';
    const NAME = '5Pcs Solid Acacia Wooden Outdoor Garden Table Chairs Set Kitchen Home Dining Set';
    const IMAGE_COUNT = 12;

    public function __construct($organisationUnitId)
    {
        parent::__construct(
            $organisationUnitId,
            static::SKU,
            0,
            false
        );
        $this->setId(PHP_INT_MAX);
        $this->setName(static::NAME);
        $this->setPreviewImages();
    }

    protected function setPreviewImages(): void
    {
        $imageIds = [];
        $images = new ImageCollection(Image::class, 'PreviewProduct');
        for ($count = 1; $count <= static::IMAGE_COUNT; $count++) {
            $file = PROJECT_ROOT . '/public/channelgrabber/settings/img/ListingTemplate/PreviewProduct/Image ' . $count . '.jpg';
            $imageData = base64_encode(file_get_contents($file));
            $dataUrl = 'data:image/jpeg;base64,'.$imageData;
            $image = new Image($this->getOrganisationUnitId(), $dataUrl, $count);
            $images->attach($image);
            $imageIds[] = ['id' => $count, 'order' => $count];
        }
        $this->setImages($images)
            ->setImageIds($imageIds);
    }
}