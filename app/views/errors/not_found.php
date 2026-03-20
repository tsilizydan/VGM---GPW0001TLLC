<?php
/**
 * Not-found view — rendered by ErrorController@notFound.
 *
 * Called via route: GET /{locale}/not-found
 * HTTP status 404 is set by the controller before this view is included.
 */
$errorCode    = 404;
$errorTitle   = $title ?? 'Page introuvable';
$errorMessage = 'La page que vous cherchez n\'existe pas ou a été déplacée.';
$errorEmoji   = '🔍';
require __DIR__ . '/layout.php';
