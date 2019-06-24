require(["Orders/pdfExport"], function(pdfExportAction)
{

    //todo - need to sort this out
    var PDFExportAction = new pdfExportAction(n, "<?= Orders\Module::PUBLIC_FOLDER ?>template/popup/saveTag.mustache");
    PDFExportAction.init('<?=$selector;?>');
});
<?php
//if(isset($order)) {
//    $this->placeholder($id . '-' . $action)
//    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
//    }
?>