require(["Orders/courierManifest"], function(CourierManifestAction)
{
    var templateMap = {
        "popup": "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'template/courier/popup/manifest.mustache' ?>",
        "popupGenerate": "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'template/courier/popup/manifest/generate.mustache' ?>",
        "popupHistoric": "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'template/courier/popup/manifest/historic.mustache' ?>",
        "buttons": "<?= $this->baseUrl . CG_UI\Module::PUBLIC_FOLDER . 'templates/elements/buttons.mustache' ?>",
        "select": "<?= $this->baseUrl . CG_UI\Module::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache' ?>"
    };
    var courierManifestAction = new CourierManifestAction(templateMap);
    courierManifestAction.init('<?=$selector;?>');
});
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>