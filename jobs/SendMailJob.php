<?php
/**
 * @author akiraz@bk.ru
 * @link https://github.com/akiraz2/yii2-ticket-support
 * @copyright 2018 akiraz2
 * @license MIT
 */

namespace akiraz2\support\jobs;

use akiraz2\support\Mailer;
use akiraz2\support\models\Content;
use akiraz2\support\traits\ModuleTrait;
use yii\base\BaseObject;

class SendMailJob extends BaseObject implements \yii\queue\JobInterface
{
    use ModuleTrait;

    public $contentId;

    public $sender;

    public $email;

    public function execute($queue)
    {
        $this->getModule()->sendMail($this->contentId, $this->sender, $this->email);
    }

    protected function getMailer()
    {
        return \Yii::$container->get(Mailer::className());
    }
}
