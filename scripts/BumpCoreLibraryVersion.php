<?php
/**
 * Copyright (c) Microsoft Corporation.  All Rights Reserved.
 * Licensed under the MIT License.  See License in the project root
 * for license information.
 *
 * Updates this package's dependency to always use the latest version of https://packagist.org/packages/microsoft/microsoft-graph-core
 */

require_once 'PackagistUtils.php';

const COMPOSER_JSON_PATH = "./composer.json";
const PACKAGE_NAME = "microsoft-graph-core";
const COMPOSER_JSON_CORE_REGEX = '#"microsoft/'. PACKAGE_NAME . '"\s*:\s*"(.+)"#';

$composerJsonContents = file_get_contents(COMPOSER_JSON_PATH);
if (!$composerJsonContents) {
    throw new \Exception("Could not read composer.json at: ".COMPOSER_JSON_PATH);
}

$matches = [];
if (!preg_match(COMPOSER_JSON_CORE_REGEX, $composerJsonContents, $matches)) {
   throw new \Exception("Could not find Core Library dependency in composer.json using regex: ".COMPOSER_JSON_CORE_REGEX);
}
$currentCoreVersion = $matches[1];
$currentCoreVersion = ($currentCoreVersion[0] === "^") ? substr($currentCoreVersion, 1) : $currentCoreVersion;

echo "CurrentCoreVersion: {$currentCoreVersion}.";


// $latestCoreVersion = getLatestPackagistVersion(PACKAGE_NAME);
$latestCoreVersion = "2.0.1-preview";

echo "Latest core version: {$latestCoreVersion}.";

if (!$latestCoreVersion) {
    exit("No non-branch version of the Core Library is available on Packagist");
}

$replacement = sprintf("\"microsoft/%s\": \"^{$latestCoreVersion}\"", PACKAGE_NAME);
if (!file_put_contents(
    COMPOSER_JSON_PATH,
    preg_replace(COMPOSER_JSON_CORE_REGEX, $replacement, $composerJsonContents))) {

    throw new \Exception("Unable to overwrite Core library version in composer.json");
}

// output informs GitHub Action whether to bump minor SDK version or bump major SDK version
if (intval($latestCoreVersion[0]) > intval($currentCoreVersion[0])) {
    exit("MAJOR_REV_SDK_VERSION");
}
exit("MINOR_REV_SDK_VERSION");
