<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleService\Gmail;
use App\Http\Controllers\GoogleService\OAuth;
use App\Models\Department;
use App\Models\MailList;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\VarDumper\VarDumper;
use PHPMailer\PHPMailer\PHPMailer;

class SendMailController extends Controller
{
    /**
     * @var integer the number of Limit to send mail each trigger
     */
    private $limit = 5;
    private $apendReplyToBody = false;
    private $replyOf;
    private $debug = true;

    private OAuth $oAuth;
    private Gmail $Gmail;
    private Collection $mails;
    private $attachmentData;
    private $headers;
    private MailList $mail;
    private Department $department;

    private $attachmentDir;
    private $inlineAttachmentDir;

    public function __construct()
    {
        $this->attachmentDir = Storage::disk(config('attachment.disk'))->path(config('attachment.attachment_path'));
        $this->inlineAttachmentDir = Storage::disk(config('attachment.disk'))->path(config('attachment.inline_attachment_path'));
        $this->getSendableMail();
    }

    public function send()
    {
        $this->LoopMails();
    }


    function checkIsReply()
    {
        if (!empty($this->mail->reply_of)) {
            $this->replyOf = MailList::find($this->mail->reply_of);
        }
        // if ($this->replyOf) {
        //     dd($this->replyOf);
        // }
    }

    /**
     * Loop through all sendable mail and send
     * @return void
     */
    function LoopMails()
    {
        if ($this->mails->count() <= 0) {
            echo "Outbox Empty !";
            return;
        }
        foreach ($this->mails as $mail) {
            $this->mail = $mail;
            $this->checkIsReply();
            $this->department = $this->mail->getDepartment;
            $this->buildConnection();
            $this->attachmentData = $this->mail->getAttachments();
            $this->headers = $this->mail->headers();

            $message = $this->Gmail->createMessage($this->GetMimeMessage());
            if ($this->replyOf) {
                $message->setThreadId($this->replyOf->msg_theread);
            }
            try {
                //var_dump($this->mail->userID,$message);
                //exit;
                $message = $this->Gmail->service->users_messages->send($this->department->email, $message);
                $this->mail->msg_id = $message->id;
                $this->mail->msg_theread = $message->threadId;
                $this->mail->removeLabel("OUT")->addLabel('SENT')->update();
                //dd($message);
                echo "Success message sent: " . $message->id;
            } catch (Exception $e) {
                echo $e->getMessage();
                //dd($e);
            }
        }
    }

    function updateToken()
    {
        if ($this->oAuth->tokenStatus == "refreshed") {
            $this->department->oauth_token($this->oAuth->token);
            $this->department->save();
        }
    }


    /**
     * Build Conneection with sender
     */
    private function buildConnection()
    {
        $this->oAuth = new OAuth(config('app.google_app_credentials'), $this->department->oauth_token());
        $this->oAuth->tokenCheck(); //Check Access Token is Valid or Not;
        $this->updateToken(); //Update Access Token if it is expired and refreshed;

        if ($this->oAuth->connect) {
            $this->Gmail = new Gmail(
                $this->oAuth->client,
                empty($this->department['email']) ? 'me' : $this->department['email']
            );
        } else {
            if ($this->debug) {
                throw new Exception('Google Web Service Connection Error');
            } else {
                die();
            }
        }
    }

    /**
     * Get Sendable Mails to queue
     * @return void
     */
    private function getSendableMail(): void
    {
        $labels = ['OUT'];

        $mailQuery = MailList::orderBy("id", "DESC")
            ->whereRs('1');
        /** Label Query */
        if (count($labels) > 0) {
            $mailQuery->where(function ($query) use ($labels) {
                foreach ($labels as $label) {
                    //var_dump($label);
                    $query->orWhere('labels', 'LIKE', "%$label%");
                };
            });
        }

        // Add condition to exclude TRASH label
        $mailQuery->where(function ($query) {
            $query->where('labels', 'NOT LIKE', '%TRASH%');
        });
        
        $this->mails = $mailQuery->get();
    }


    /**
     * Prepare MIME Message data for current Mail
     * @return String MIME Message data
     */
    function GetMimeMessage()
    {
        $mailMime = new PHPMailer();
        $mailMime->setFrom($this->department->email, $this->department->name);

        $mailMime->Subject = $this->mail->subject;
        //Pre Send Filter for Inline Image and Set Body Mail
        $mailMime = $this->preSendFilter($this->mail->MailDetails->msg_body, $mailMime);
        $mailMime->AltBody = trim(strip_tags($this->mail->MailDetails->msg_body));
        /*TO */
        if (is_array($this->headers->to) && count($this->headers->to) > 0) {
            foreach ($this->headers->to as $to) {
                $mailMime->addAddress($to->address, $to->display);
            }
        }
        /*CC */
        if (is_array($this->headers->cc) && count($this->headers->cc) > 0) {
            foreach ($this->headers->cc as $cc) {
                $mailMime->AddCC($cc->address, $cc->display);
            }
        }
        /*BCC */
        if (is_array($this->headers->bcc) && count($this->headers->bcc) > 0) {
            foreach ($this->headers->bcc as $bcc) {
                $mailMime->AddBCC($bcc->address, $bcc->display);
            }
        }

        //In Reply To, Existing Message ID
        if ($this->replyOf) {
            $mesageID = $this->replyOf->headers()->message_id;
            $mailMime->addCustomHeader('In-Reply-To', $mesageID);
            $mailMime->addCustomHeader('References', $mesageID); //<d7751ea969c01cda464ebf2de2fe64e6@example.org>
        }

        //var_dump($mail);exit;
        //Custom Headers
        $mailMime->addCustomHeader('X-Mailer', 'Outlook');
        $mailMime->addCustomHeader('X-Priority', '1');
        $mailMime->addCustomHeader('X-MSMail-Priority', 'High');

        //Attachments
        //dd($this->attachmentData);
        if (count($this->attachmentData->attachments) > 0) {
            foreach ($this->attachmentData->attachments as $attachment) {
                $fileFullPath = $this->attachmentDir . "/" . $attachment->filename[1];
                $mailMime->AddAttachment($fileFullPath, $attachment->filename[0]); // comented to debug speedup
            }
        }

        $mailMime->preSend();
        // var_dump($mailMime->getSentMIMEMessage());
        // exit;
        return $mailMime->getSentMIMEMessage();
    }


    /**
     * MailBody Filter and AddEmbeddedImage for Inline Image
     * @param mixed $body Mail Body
     * @param PHPMailer\PHPMailer\PHPMailer $mail PHPMailer Instance
     * @return PHPMailer\PHPMailer\PHPMailer $mail
     */
    function preSendFilter($body = "", $mail)
    {
        //$mail->Body = $body;
        //return $mail;

        $body = str_replace("\r\n", "", $body);

        if (is_array($this->attachmentData->inlineAttachments) && count($this->attachmentData->inlineAttachments) > 0) {
            foreach ($this->attachmentData->inlineAttachments as $attachment) {
                $imageFile = $this->inlineAttachmentDir . "/" . $attachment->filename[1];
                $mail->AddEmbeddedImage($imageFile, $attachment->filename[0]);
                //$body = str_replace($imageFile, 'cid:' . $image, $body);
            }
        }

        //$mail->MsgHTML($body);
        $pattern = "/<p[^>]*><\\/p[^>]*>/";
        //$pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";  use this pattern to remove any empty tag
        $body = preg_replace($pattern, '', $body);

        $findR = ['/\s?<p[^>]*>(\s+|&nbsp;)*<\/p>/', '/<p[^>]*>/', '/<\/p>/']; //Replace Empty P to  <br> | P Opening Tag to <br> | Closing Tag of P to <br>
        $repR = ['', '<br>', '<br>'];
        $body = preg_replace($findR, $repR, $body); //
        //$mail->MsgHTML($body);
        if ($this->apendReplyToBody) {
            $body = $body . "<br>" . $this->replyOf->getDetails->msg_body; //Reply To Message Body
        }

        $body = $this->convertEmailContentToUtf8($body);
        //Custom Filter Content
        $find = ['Â', "\r\n"];
        $replace = [""];
        //replace body
        $body = str_replace($find, $replace, $body);

        $mail->Body = $body;
        return $mail;
    }

    function convertEmailContentToUtf8($content)
    {
        // Check if the content is already UTF-8 encoded
        if (mb_detect_encoding($content, 'UTF-8', true) === false) {
            // Convert the content to UTF-8 encoding using iconv
            $utf8Content = iconv(mb_detect_encoding($content), 'UTF-8', $content);

            // Return the UTF-8 encoded content
            return $utf8Content;
        }

        // If the content is already UTF-8 encoded, return it as is
        return $content;
    }
}
