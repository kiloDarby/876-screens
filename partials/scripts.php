<?php

    if (!isset($basePath)) {
        $basePath = '../';
    }
?>

<script>
    const BASE_URL = "<?= url() ?>";
</script>

<script src="<?= $basePath ?>assets/js/script.js"></script>
<script src="<?= $basePath ?>assets/js/register.js"></script>