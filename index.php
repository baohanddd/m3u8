<?php
/**
 * docker build . -t cut:v1
 * docker run --rm -it -v=$(pwd):/data -v=/oss:/oss -w=/data cut:v1 php index.php
 */
use Bob\M3U8\Filename\Filename;
use Bob\M3U8\Index\M3U8;
use Bob\M3U8\Session;

require 'vendor/autoload.php';

$log = Session::getLog();
$filename = new Filename("https://video3.futurelink.live/record/aliyun/en2/a.m3u8");
$m3u8 = new M3U8($filename);
$m3u8->setFFMPEG('/data/bin/ffmpeg');
$m3u8->setSavePath("/oss/video3");
$m3u8->addClippableDomain('video3.futurelink.live');
$result = $m3u8->getTimeline()->clip(10, 55);
$result->cut();
$blocks = $m3u8->getTimeline()->getBlocks();
$log->debug('Remain blocks = '.count($blocks));