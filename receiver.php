<?php
$conf = new RdKafka\Conf();

$conf->set('group.id', 'myConsumerGroup');

$consumer  = new RdKafka\Consumer($conf);
$consumer ->addBrokers("127.0.0.1:9092");

$topicConf = new RdKafka\TopicConf();
$topicConf->set('auto.commit.interval.ms', 100);
$topicConf->set('offset.store.method', 'broker');
$topicConf->set('auto.offset.reset', 'earliest');

$topic = $consumer->newTopic("quickstart-events", $topicConf);

$topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);

while (true) {
    $message = $topic->consume(0, 5000);
    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            console_log(json_encode($message));
            break;
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            console_log("No more messages; will wait for more\n");
            break;
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            console_log("Timed out\n");
            break;
        default:
            throw new \Exception($message->errstr(), $message->err);
            break;
    }
}

$consumer->close();

function console_log($message) {
    $STDERR = fopen("php://stderr", "w");
              fwrite($STDERR, "\n".$message."\n\n");
              fclose($STDERR);
}