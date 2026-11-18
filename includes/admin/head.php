<?php /** Shared <head> for admin pages. Expects $pageTitle. */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> · Atom Services Admin</title>
    <link rel="icon" href="<?= admin_asset('/assets/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= admin_asset('/assets/css/admin.css') ?>">
</head>
