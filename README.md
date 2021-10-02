A library of M3U8
====

can crop m3u8 and .ts files if necessary.

## How to use?

### Example: crop m3u8

```php
# load m3u8 file from url...
$filename = new Filename("https://video3.futurelink.live/record/aliyun/en2/a.m3u8");
$m3u8 = new M3U8($filename);
// set save handler to persistent cropped blocks
$m3u8->setBlockSaveHandler(function(string $saveName, string $tempName): string {
    echo $saveName . PHP_EOL;
    echo $tempName . PHP_EOL;
    return "https://video3.futurelink.live/record/aliyun/en2/another.ts";
});
$m3u8->setIndexSaveHandler(function(string $m3u8Name, string $content): string {
    $path = "/tmp/{$m3u8Name}";
    $fp = fopen($path, 'wb');
    fwrite($fp, $content);
    fclose($fp);
    return $path;
});

# drop clips before 10 seconds and after 200 seconds...
$start = 10.0;
$end = 200.0;
$m3u8->getTimeline()->clip($start, $end)->cut()->saveAs();

# save clips between start and end...
$start = 10.0;
$end = 200.0;
$m3u8->getTimeline()->merge($start, $end)->cut()->saveAs();
```