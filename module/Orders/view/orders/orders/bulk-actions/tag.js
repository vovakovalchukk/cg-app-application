<?php $this->headScript()->appendFile($this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/tag.js'); ?>
$("#<?= $id ?>").bulkActions("set", "<?= $action ?>", TagBulkAction);