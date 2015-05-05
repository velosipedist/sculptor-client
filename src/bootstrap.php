<?php
if (PHP_SAPI == 'cli') {
    return;
}
// setup defaults
\velosipedist\SculptorClient\SculptorClient::configureLeadsJavascript();