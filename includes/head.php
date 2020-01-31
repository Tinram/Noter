<?php
declare(strict_types=1);
ini_set('date.timezone', TIMEZONE);
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo CONFIG_APP_NAME; ?></title>
        <meta name="application-name" content="<?php echo CONFIG_APP_NAME; ?>">
        <meta name="author" content="Martin Latter">
        <meta name="copyright" content="&copy; 2015">
        <link rel="stylesheet" type="text/css" href="css/noter.css">
        <script src="js/common.js"></script>
    </head>

    <body>
