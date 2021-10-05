<?php
/**
 * Copyright (c) Microsoft Corporation.  All Rights Reserved.
 * Licensed under the MIT License.  See License in the project root
 * for license information.
 *
 * Methods to fetch package info from Packagist
 */


/**
 * Gets the latest non-branch version of a package (including an unstable release if it's the latest)
 *
 * Assumption: Packagist returns tagged versions in descending order (latest release first)
 *
 * @param string $packageName as on packagist e.g. microsoft-graph-beta
 * @return string|null null if no non-branch version exists
 */
function getLatestPackagistVersion(string $packageName): ?string
{
    $url = "https://packagist.org/packages/microsoft/".$packageName.".json";

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_TIMEOUT, 100);
    curl_setopt($handle, CURLOPT_FAILONERROR, true);

    echo "Fetching latest SDK version from " . $url . "\n";
    $response = curl_exec($handle);

    if (curl_error($handle)) {
        exit("Failed to get latest packagist version: ". curl_error($handle));
    }

    curl_close($handle);

    $responseJson = json_decode($response, true);
    if (!array_key_exists("package", $responseJson)
        || !array_key_exists("versions", $responseJson["package"])
        || empty($responseJson["package"]["versions"])) {

        exit("Unable to find versions in the packagist response JSON: ". $responseJson);
    }

    $versions = $responseJson["package"]["versions"];
    foreach ($versions as $version => $versionMetadata) {
        # Ignore branch versions
        if (!preg_match('/^dev-.*/', $version)) {
            # First non-branch version is the latest based on payload structure
            echo "Latest packagist version: {$version}\n";
            return $version;
        }
    }
    return null;
}
