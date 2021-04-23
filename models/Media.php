<?php

namespace akiraz2\support\models;

use Yii;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "support_media".
 *
 * @property int $id
 * @property int $content_id
 * @property string $name
 * @property string $extension
 * @property string $size
 * @property int $created_at
 *
 * @property TicketContent $content
 */
class Media extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%support_media}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id', 'name', 'extension', 'created_at'], 'required'],
            [['content_id', 'created_at'], 'integer'],
            [['name', 'extension', 'size'], 'string', 'max' => 255],
            [['extension'], 'string', 'max' => 4],
            [['size'], 'string', 'max' => 20],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => Content::class, 'targetAttribute' => ['content_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content_id' => Yii::t('app', 'Content ID'),
            'path' => Yii::t('app', 'Path'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(TicketContent::class, ['id' => 'content_id']);
    }

    /**
     * Директория временного хранения загруженных файлов
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public static function getTmpDirectory() {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tickets'.DIRECTORY_SEPARATOR.Yii::$app->session->id;
        if (file_exists($path)) {
            return $path;
        } else {
            if (FileHelper::createDirectory($path)) {
                return $path;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * Директория временного хранения загруженных файлов
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public static function getMailTmpDirectory() {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tickets'.DIRECTORY_SEPARATOR.'mails';
        if (file_exists($path)) {
            return $path;
        } else {
            if (FileHelper::createDirectory($path)) {
                return $path;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * Конечная директория хранения загруженных файлов
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public static function getDirectory() {
        $path = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'tickets';
        if (file_exists($path)) {
            return $path;
        } else {
            if (FileHelper::createDirectory($path)) {
                return $path;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * @param $filePath
     * @return bool
     */
    public static function forceDownload($filePath) {
        if (file_exists($filePath)) {
            // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
            // если этого не сделать файл будет читаться в память полностью!
            if (ob_get_level()) {
                ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($filePath));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            // читаем файл и отправляем его пользователю
            if ($fd = fopen($filePath, 'rb')) {
                while (!feof($fd)) {
                    print fread($fd, 1024);
                }
                fclose($fd);
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param $size
     * @param int $precision
     * @return string
     */
    public static function formatBytes($size, int $precision = 2)
    {
        if (!$size) {
            return '0 b';
        }

        $base = log($size, 1024);
        $suffixes = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[(int) floor($base)];
    }
}
