<?php


namespace app\common\service;

if(is_file('../vendor/qiniu/php-sdk/autoload.php')){
    require '../vendor/qiniu/php-sdk/autoload.php';
}
if(is_file('./vendor/qiniu/php-sdk/autoload.php')){
    require './vendor/qiniu/php-sdk/autoload.php';
}
use Qiniu\Auth as Qiniu;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
class Upload
{
    public static function uploadQiniu($file, $fileName)
    {
        $upload = \think\Config::get('upload')['qiniu'];
        $result = [
            'status' => true,
            'msg' => 'ok',
            'url' => '',
        ];
        try {
            $qiniu = new Qiniu($upload['access_key'], $upload['secret_Key']);
            $bucket = $qiniu->uploadToken($upload['bucket']);
            $uploadMgr = new UploadManager();
            list($ret, $err) = $uploadMgr->putFile($bucket, $fileName, $file);
            if ($err != null) {
                echo $err;
                $result['status'] = false;
                $result['msg'] = $err;
            } else {
                $result['url'] = 'http://q3oja40bh.bkt.clouddn.com/' . ($ret['key'] ?? '');
            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['msg'] = $e->getTraceAsString();
        }
        return $result;
    }

}