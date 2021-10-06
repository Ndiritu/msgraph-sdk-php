<?php
/**
 * Copyright (c) Microsoft Corporation.  All Rights Reserved.
 * Licensed under the MIT License.  See License in the project root
 * for license information.
 *
 * Bumps up the SDK's minor version in src/GraphConstants.php & README based on the latest published package version on Packagist
 *
 * Assumptions:
 *  - There are new model changes present in the current branch
 *  - Script is run from the repo root
 *  - Script is run on a Unix environment (affects file path separator to files)
*/
require_once 'PackagistUtils.php';

const CONSTANTS_FILEPATH = "./src/Core/GraphConstants.php";
const SDK_VERSION_VAR_NAME = "SDK_VERSION"; # Name of version variable in GraphConstants.php
const PACKAGE_NAME = "microsoft-graph";
const CONSTANTS_README_FILEPATH = "./README.md";

function incrementMinorVersion(string $version): string
{
    $splitVersion = explode(".", $version);
    # Increment minor version
    $splitVersion[1] = strval(intval($splitVersion[1]) + 1);
    # Set patch to 0
    $splitVersion[2] = preg_replace('/[0-9]+/', "0", $splitVersion[2]);
    return implode(".", $splitVersion);
}

function incrementMajorVersion(string $version): string
{
    $splitVersion = explode(".", $version);
    $prevMajorVersion = intval($splitVersion[0]);
    return sprintf("%s.0.0-preview", $prevMajorVersion + 1);
}

function updateGraphConstants(string $version)
{
    $fileContents = file_get_contents(CONSTANTS_FILEPATH);
    if ($fileContents) {
        $pattern = '/'. SDK_VERSION_VAR_NAME . '\s+=\s+".+"/';
        $replacement = SDK_VERSION_VAR_NAME . ' = "' . $version . '"';
        if (!file_put_contents(CONSTANTS_FILEPATH, preg_replace($pattern, $replacement, $fileContents))) {
            throw new \Exception("Unable to find and replace SDK version variable ". SDK_VERSION_VAR_NAME);
        }
        echo "Successfully updated " . CONSTANTS_FILEPATH . "\n";
        return;
    }
    throw new \Exception("Could not read GraphConstants.php at ". CONSTANTS_FILEPATH);
}

function updateReadMe(string $version)
{
    $fileContents = file_get_contents(CONSTANTS_README_FILEPATH);
    if ($fileContents) {
        $pattern = sprintf('/"microsoft\/%s":\s+".+"/', PACKAGE_NAME);
        $replacement = sprintf("\"microsoft/%s\": \"^{$version}\"", PACKAGE_NAME);
        if (!file_put_contents(CONSTANTS_README_FILEPATH, preg_replace($pattern, $replacement, $fileContents))) {
            throw new \Exception("Unable to find and replace SDK version");
        }
        echo "Successfully updated README\n";
        return;
    }
   throw new \Exception("Could not read README.md at " . CONSTANTS_README_FILEPATH);
}

//$latestVersion = getLatestPackagistVersion(PACKAGE_NAME);
$latestVersion = "2.0.0-preview";
if (!$latestVersion) {
    exit("No version found on Packagist for: ".PACKAGE_NAME."\n");
}

$version = ($argv[1] === 'MAJOR_REV_SDK_VERSION') ? incrementMajorVersion($latestVersion) : incrementMinorVersion($latestVersion);
echo "Version after increment: {$version}\n";
updateGraphConstants($version);
updateReadMe($version);
