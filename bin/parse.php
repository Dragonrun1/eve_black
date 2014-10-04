<?php
/**
 * Contains parse script.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * Used to work with new Eve Online Oceanus ship black files.
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU GPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Eve_black;

use Symfony\Component\Yaml\Yaml;
use ZipArchive;

require_once __DIR__ . '/bootstrap.php';
$resultName = dirname(__DIR__) . '/clean.yaml';
if (!file_exists($resultName)) {
    $yamlName = dirname(__DIR__) . '/black.yaml';
    if (!file_exists($yamlName)) {
        print 'Could NOT find black file to clean, was given ' . $yamlName
            . PHP_EOL;
        exit(1);
    }
    $yaml = file_get_contents($yamlName);
    $yaml = clean($yaml);
    file_put_contents($resultName, $yaml);
} else {
    $yaml = file_get_contents($resultName);
}
print 'Starting Yaml decode, this will take a while ...' . PHP_EOL;
$yamlResult = Yaml::parse($yaml);
savePart('black', $yamlResult);
split($yamlResult);
/**
 * @param $yaml
 *
 * @return string
 */
function clean($yaml)
{
    $find = ["\r\n-   ", "\r\n    -   ", "\r\n        -   ", ",\r\n"];
    $replace = [
        "\r\n  -\r\n    ",
        "\r\n      -\r\n        ",
        "\r\n          -\r\n            ",
        ","
    ];
    $yaml = str_replace($find, $replace, $yaml);
    return $yaml;
}

/**
 * @param string $name
 * @param array  $part
 */
function savePart($name, array $part)
{
    $baseDir = dirname(__DIR__);
    $fileName = sprintf($baseDir . '/%1$s.json', $name);
    $json = json_encode($part, JSON_PRETTY_PRINT);
    file_put_contents($fileName, $json);
    $json = json_encode($part);
    $fileName = sprintf($baseDir . '/%1$s.minify.json', $name);
    file_put_contents($fileName, $json);
    $fileName = sprintf($baseDir . '/%1$s.minify.json.zip', $name);
    $zip = new ZipArchive();
    if ($zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE)
        !== true
    ) {
        printf('Failed to open %1$s zip file, was given %2$s', $name,
            $fileName);
        print PHP_EOL;
        return;
    }
    $zip->addFromString(sprintf('%1$s.minify.json', $name), $json);
    $zip->close();
}

/**
 * @param array $yaml
 */
function split(array $yaml)
{
    foreach ($yaml as $k => $v) {
        switch ($k) {
            case 'faction':
            case 'hull':
            case 'race':
            case 'material':
                savePart($k, [$k => $v]);
                break;
            default:
                break;
        }
    }
}
