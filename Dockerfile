FROM registry.cn-shenzhen.aliyuncs.com/fu2/php:php80-cli-buster

ADD . /data
WORKDIR /data

CMD ["php", "index.php"]