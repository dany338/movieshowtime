<?php
namespace console\controllers;
use yii\console\Controller;
use backend\models\Movie;
use backend\models\Moviebillboard;
use backend\models\Movietheater;
use backend\models\Subscription;

class DaemonController extends Controller
{
  public function actionCreateLocations()
  {
    ini_set("upload_max_filesize", "256M");
    ini_set("post_max_size", "256M");
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', -1);
    ini_set('max_input_time', -1);
    set_time_limit(0);

    $subscriptions = Subscription::find()->where('status = 1')->all();
    foreach ($subscriptions as $index => $subscription):
      $subscription->setCreateLocation();
    endforeach;

    $rootyii = realpath(dirname(__FILE__).'/../../');
    $folder  = $rootyii.'/cronjob/log_create_location.txt';
    $f       = fopen($folder, 'a');
    $fw      = fwrite($f, 'Automatic Process : ' . date('d M, Y g:i A') . PHP_EOL);
    fclose($f);

    echo 'Automatic Process Create Theaters and Schedules: ' . date('d M, Y g:i A');
  }

  public function actionSendNotificationsSubscribers()
  {
    ini_set("upload_max_filesize", "256M");
    ini_set("post_max_size", "256M");
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', -1);
    ini_set('max_input_time', -1);
    set_time_limit(0);
    $moviebillboards = Moviebillboard::find()->where('DATE_FORMAT(start_date,"%Y-%m-%d") = DATE_FORMAT(NOW(),"%Y-%m-%d")')->limit(50)->all();
    foreach ($moviebillboards as $index => $moviebillboard):
      $moviebillboard->setSendNotificationsSubscribers();
    endforeach;

    $rootyii = realpath(dirname(__FILE__).'/../../');
    $folder  = $rootyii.'/cronjob/log_send_notifications_subscribers.txt';
    $f       = fopen($folder, 'a');
    $fw      = fwrite($f, 'Automatic Process : ' . date('d M, Y g:i A') . PHP_EOL);
    fclose($f);

    echo 'Automatic Process Send Notifications by Subscribers: ' . date('d M, Y g:i A');
  }
}
?>
