# Code Citations

## License: GPL-2.0
https://github.com/vanilla/vanilla/blob/9ee55adaaa0e0b0a177a1dffa1377289465cd2fa/library/core/class.upload.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```


## License: GPL-2.0
https://github.com/vanilla/vanilla/blob/9ee55adaaa0e0b0a177a1dffa1377289465cd2fa/library/core/class.format.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```


## License: MIT
https://github.com/igorw/modcasts/blob/5eb5feae8e3b1850f534325f56bdcba73d2a3afc/app/lib/Modcasts/TwigExtension.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```


## License: MIT
https://github.com/KaeruCT/CuteViewer/blob/6209b17acc8a6f2017bfe7bc8999a3868f65c478/fviewer.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```


## License: GPL-2.0
https://github.com/wikimedia/mediawiki-extensions-MathSearch/blob/c1a0bce1f9ff34cd38645d87905805b145e89123/includes/FormulaInfo.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```


## License: MPL-2.0
https://github.com/MissAllSunday/Breeze/blob/11c069e02733b77ace7fecbe655876f3d76e8672/Sources/Breeze/Traits/TextTrait.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```


## License: unknown
https://github.com/smcgov/OpenSanMateo/blob/5772529d95898784a5cda23b7a40c957db44dc99/profile/themes/smc_base/template.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```


## License: MIT
https://github.com/WeareJH/m2-module-import/blob/9621939c75a0e872cbc85995f1c8873410f5b5c4/functions.php

```
(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
```

