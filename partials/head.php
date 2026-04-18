<?php

    if (!isset($basePath)) {
        $basePath = '../';
    }

    if (!isset($activePage)) {
        $activePage = '';
    }
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="Website description here">
    <meta name="author" content="Kevin Darby">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $basePath ?>assets/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="<?= $basePath ?>assets/images/favicon/favicon.svg">
    <link rel="shortcut icon" href="<?= $basePath ?>favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $basePath ?>assets/images/favicon/apple-touch-icon.png">
    <link rel="manifest" href="<?= $basePath ?>assets/images/favicon/site.webmanifest">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/typography.css">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
</head>