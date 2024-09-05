<?php

namespace App\Http\Controllers\GoogleService;

use Exception;
use App\Http\Controllers\Controller;
use Google\Service\Gmail as GoogleMail;
use PHPMailer\PHPMailer\PHPMailer;
use PhpMimeMailParser\Parser;

class Gmail extends Controller
{

    public array $message;
    public array $attachments;
    public array $headers;

    /**
     * Mail Parser Instance
     * @var \PhpMimeMailParser\Parser $parser
     */
    public $parser;

    /**
     * Google Client Instance
     */
    public $client;

    /**
     * Label IDs
     */
    public $lebelIds = ["UNREAD", "INBOX"]; // "IMPORTANT", "STARRED", "TRASH", "SPAM"
    public $allLabels = [];

    /**
     * Downloaded messages at a time
     */
    public $limit = 2;

    /**
     * @var String
     * Attachment Directory
     */
    public $attachmentDir = "attachments/";
    public $attachmentUri = "";

    /**
     * @var String
     * inline attachments Directory
     */
    public $inlineAttachmentDir = "attachments/inline/";
    public $inlineAttachmentUri = "";
    /**
     * @var Boolean
     * Atachment file name is automatically generated randomly
     */
    public $random_filename = false;


    /**
     * Modify message After Download From Gmail
     * @type array [action=>remove/add,labelIds=>[UNREAD]]
     */
    public $modifyMessage = ['action' => 'remove', 'labelIds' => ['UNREAD', 'INBOX']];
    public $modifyMessageAfterDownload = [
        'add' => [],
        'remove' => ['UNREAD']
    ];

    public $userID;
    public $service;
    public $header;

    public function __construct($client, string $userId = "me")
    {
        if (!extension_loaded("mailparse")) {
            throw new Exception("mailparse php:extension not loaded, (? help) if plesk server then - Install extension  via SSH\n 1.Enable EPEL repository (#yum install epel-release) \n 2.Install devel packages: (#yum install gcc plesk-php70-devel zlib-devel re2c)\n 3. Installmailparse using PECL (#/opt/plesk/php/7.0/bin/pecl install mailparse)");
            /*
             * Install extension in Plesk Server via SSH
             * 1.Enable EPEL repository:
             * #yum install epel-release
             * 2.Install devel packages:
             * #yum install gcc plesk-php70-devel zlib-devel re2c
             * 3.Installmailparse using PECL:
             * #/opt/plesk/php/7.0/bin/pecl install mailparse
             */
        }

        $this->parser = new Parser();
        $this->userID = $userId;
        $this->client = $client;
        $this->service = new GoogleMail($this->client);
        $this->manageLabels();
    }

    function labelIds($arr)
    {
        $nIds = [];
        foreach ($arr as $k => $label) {
            $ExKe = array_search($label, $this->allLabels);
            if ($ExKe) {
                $nIds[$k] = $ExKe;
            } else {
                $nIds[$k] = $label;
            }
        }
        return $nIds;
    }

    function parseData($rawEmail)
    {
        // Assuming you have loaded the raw email into $rawEmail variable

        $mail = new PHPMailer();
        $mail->Body = $rawEmail;
        dd($mail);
        $singleRow['messageId'] = $mail->MessageID;
        //$singleRow['html'] = $mail->msgHTML(); // Assuming getMessageHtml() is replaced by msgHTML() in PHPMailer
        $singleRow['attachments'] = $mail->getAttachments();
        $singleRow['from'] = [
            'name' => $mail->FromName,
            'email' => $mail->From
        ];
        $singleRow['to'] = $mail->getToAddresses();
        $singleRow['cc'] = $mail->getCcAddresses();
        $singleRow['bcc'] = $mail->getBccAddresses();
        $singleRow['subject'] = $mail->Subject;
        $singleRow['text'] = $mail->AltBody;
        $singleRow['date'] = $mail->getHeader('Date'); // Access the 'Date' header

        $singleRow['headers'] = $mail->getCustomHeaders();
        $singleRow['in_reply_to'] = $mail->getInReplyTo();
        $singleRow['reply_to'] = $mail->getReplyToAddresses();
        $singleRow['received'] = $mail->getReceivedBy();
        $singleRow['message_id'] = $mail->getMessageID();
        $singleRow['references'] = $mail->getReferences();
        $singleRow['x_mailer'] = $mail->getMailer();
        $singleRow['x_originating_ip'] = $mail->getOriginatingIP();
        $singleRow['x_original_arrival_time'] = $mail->getOriginalArrivalTime();
        return $singleRow;
    }


    public function get($limit = 2)
    {
        $this->limit = $limit;

        //var_dump($this->labelIds($this->lebelIds));

        $optParams = [];
        $optParams["maxResults"] = $this->limit; // Return Only 2 Messages
        $optParams["labelIds"] = $this->labelIds($this->lebelIds); // Only show messages in Inbox

        //$optParams["q"] = "from:pranub@siatexbd.com"; // Only show messages from


        $messages = $this->service->users_messages->listUsersMessages($this->userID, $optParams);

        $data = [];
        foreach ($messages as $messageResponse) {
            $messageId = $messageResponse->getId();
            $singleRow = [];
            $singleRow["id"] = $messageId;
            $singleRow["threadId"] = $messageResponse->getThreadId();

            $message = $this->service->users_messages->get(
                $this->userID,
                $messageId,
                [
                    'format' => 'raw',
                ]
            );

            $singleRow["labelIds"] = $message->getLabelIds();
            $singleRow["snippet"] = $message->getSnippet();
            $singleRow['historyId'] = $message->getHistoryId();
            //replyto
            //$singleRow['replyTo'] = $message->getReplyTo();

            $sanitizedData = strtr($message->raw, "-_", "+/");
            $decodedMessage = base64_decode($sanitizedData);
            //var_dump($this->parseData($decodedMessage));
            // exit;

            $this->parser->setText($decodedMessage);
            $headers = $this->header = $this->parser->getHeaders();
            //Store Attachment
            $this->getAttachments();

            $singleRow['messageId'] = $headers['message-id'];
            $singleRow['html'] = $this->getMessageHtml();
            $singleRow['attachments'] = $this->attachments;
            $singleRow['from'] = $this->parser->getAddresses('from');
            $singleRow['to'] = $this->parser->getAddresses('to');
            $singleRow['cc'] = $this->parser->getAddresses('cc');
            $singleRow['bcc'] = $this->parser->getAddresses('bcc');
            $singleRow['subject'] = $this->parser->getHeader('subject');
            $singleRow['text'] = $this->parser->getMessageBody('text');
            $singleRow['date'] = $this->parser->getHeader('date');
            $singleRow['headers'] = $headers;
            $singleRow['in_reply_to'] = $this->parser->getHeader('in-reply-to');
            $singleRow['reply_to'] = $this->parser->getAddresses('reply-to');
            $singleRow['received'] = $this->parser->getHeader('received');
            $singleRow['message_id'] = $this->parser->getHeader('message-id');
            $singleRow['references'] = $this->parser->getHeader('references');
            $singleRow['x_mailer'] = $this->parser->getHeader('x-mailer');
            $singleRow['x_originating_ip'] = $this->parser->getHeader('x-originating-ip');
            $singleRow['x_original_arrival_time'] = $this->parser->getHeader('x-original-arrival-time');
            //Assign to Data array
            $data[$singleRow['id']] = $singleRow;
            //Modify Message After Download
            //$this->modifyMessageAfterDownload($messageId);
        }

        return $data;
    }

    function modifyLabel($messageId)
    {
        $this->modifyMessageAfterDownload($messageId);
    }

    /**
     * Manage All Labels
     */
    function manageLabels()
    {
        try {
            $existingLabelIds = $this->service->users_labels->listUsersLabels($this->userID);
            foreach ($existingLabelIds->labels as $label) {
                //Label Name
                $this->allLabels[$label->getId()] = $label->getName();
            }
        } catch (Exception $e) {
            $this->allLabels = [];
        }
    }

    /**
     * Create Required Labels if Not Exists and set into $this->allLabels
     */
    function createRequiredlabels()
    {
        //dd($this->modifyMessageAfterDownload);
        $addLabels = $this->modifyMessageAfterDownload['add'];
        foreach ($addLabels as $labelId) {
            if (!in_array($labelId, $this->allLabels)) {
                $label = $this->createLabel($labelId);
                $this->allLabels[$label->getId()] = $label->getName();
                //echo "created label: " . $label->getName() . "\n";
            }
        }
    }

    /**
     * Create Label in Gmail
     * @param String $labelId
     */
    function createLabel(String $labelId)
    {
        $label = new GoogleMail\Label();
        //$label->setId($labelId);
        $label->setName($labelId);
        $label->setLabelListVisibility('labelShow');
        $label->setMessageListVisibility('show');
        $label->setType('system');
        $label = $this->service->users_labels->create($this->userID, $label);
        return $label;
    }

    function modify2($messageId)
    {
        $this->createRequiredlabels(); //Create Required Labels if Not Exists
        $modifyMessageRequest = new GoogleMail\ModifyMessageRequest();

        $addIds = $this->modifyMessageAfterDownload['add'];
        $removeIds = $this->modifyMessageAfterDownload['remove'];

        $addlabalIds = [];
        foreach ($addIds as $labelId) {
            $addlabalIds[] = array_search($labelId, $this->allLabels);
        }
        $removeLabelIds = [];
        foreach ($removeIds as $labelId) {
            $removeLabelIds[] = array_search($labelId, $this->allLabels);
        }

        $modifyMessageRequest->setAddLabelIds($addlabalIds);
        $modifyMessageRequest->setRemoveLabelIds($removeLabelIds);
        $this->service->users_messages->modify($this->userID, $messageId, $modifyMessageRequest);
    }

    function modifyMessageAfterDownload($messageId)
    {
        return $this->modify2($messageId);
        //ModifyMessageRequest
        $modifyMessageRequest = new GoogleMail\ModifyMessageRequest();
        if ($this->modifyMessage['action'] == 'remove') {
            $modifyMessageRequest->setRemoveLabelIds($this->modifyMessage['labelIds']);
        } elseif ($this->modifyMessage['action'] == 'add') {
            $modifyMessageRequest->setAddLabelIds($this->modifyMessage['labelIds']);
        }
        $this->service->users_messages->modify($this->userID, $messageId, $modifyMessageRequest); // Remove UNREAD Label
    }

    /**
     * Store Attachment File and return the path and the filename saved
     */
    public function getAttachments()
    {
        $attachments = $this->parser->getAttachments();

        $localAttachments = [
            'attachments' => [],
            'inlineAttachments' => [],
        ];
        foreach ($attachments as $attachment) {
            $orginalFilename = $attachment->getFilename();
            if ($attachment->getContentDisposition() == "inline") {
                if ($this->random_filename) {
                    $fileName = $attachment->save($this->inlineAttachmentDir, Parser::ATTACHMENT_RANDOM_FILENAME);
                } else {
                    //Inline attachment
                    $fileName = $this->saveAttachment($attachment, $this->inlineAttachmentDir, $size);
                }

                $filenamePathInfo = pathinfo($fileName);
                //$filesinfo = $this->saveAttachment($attachment, $this->inlineAttachmentDir);
                $localAttachments['inlineAttachments'][] = [
                    'filename' => [$orginalFilename, $filenamePathInfo['basename']], //Saved File information
                    'contentID' => $attachment->getContentID(),
                    'Type' => $attachment->getContentType(),
                    'size' => $size
                ];
            } else {
                if ($this->random_filename) {
                    $fileName = $attachment->save($this->attachmentDir, Parser::ATTACHMENT_RANDOM_FILENAME);
                } else {
                    $fileName = $this->saveAttachment($attachment, $this->attachmentDir, $size);
                }

                $filenamePathInfo = pathinfo($fileName);
                //$filesinfo = $this->saveAttachment($attachment, $this->inlineAttachmentDir);
                $localAttachments['attachments'][] = [
                    'filename' => [$orginalFilename, $filenamePathInfo['basename']], //Saved File information
                    'contentID' => $attachment->getContentID(),
                    'Type' => $attachment->getContentType(),
                    'size' => $size
                ];
            }
        }
        $this->attachments = $localAttachments;
    }

    /**
     * Store Attachment File and return the path and the filename saved
     */
    function saveAttachment($attachment, $attach_dir = "attachments/", &$size)
    {
        $uId = uniqid();
        $orginalFileName = $attachment->getFilename();
        $fileName = $uId . "-_-" . $orginalFileName;

        //here if folder not exist then create 
        if (!file_exists($attach_dir)) {
            mkdir($attach_dir, 0777, true);
        }

        // Check if the directory is writable
        if (!is_writable($attach_dir)) {
            throw new Exception('Could not write attachments. Your directory may be unwritable by PHP.');
        }
        // Check if the file
        $attachment_path = preg_replace('#/+#', '/', $attach_dir . "/" . $fileName);

        /** @var resource $fp */
        if ($fp = fopen($attachment_path, 'w')) {
            while ($bytes = $attachment->read()) {
                fwrite($fp, $bytes);
            }
            fclose($fp);
            $size = filesize($attachment_path);
            return $fileName;
        } else {
            throw new Exception('Could not write attachments. Your directory may be unwritable by PHP.');
        }
        return $fileName;
    }

    /**
     * Mail Html Body After Filter by inline Attachment
     */
    public function getMessageHtml()
    {
        $html = $this->parser->getMessageBody('html');
        $html = $this->filterInlineAttachment($html);
        return $html;
    }

    /**
     * Attachment Filter
     */
    function filterInlineAttachment($html = "")
    {
        foreach ($this->attachments['inlineAttachments'] as $k => $att) {
            if (
                ($att['Type'] == "image/png" ||
                    $att['Type'] == "image/jpeg" ||
                    $att['Type'] == "image/jpg" ||
                    $att['Type'] == "image/gif"
                ) && $att['contentID']
            ) {
                $html = str_replace("cid:" . $att['contentID'], $this->inlineAttachmentUri . "/" . $att['filename'][1], $html);
            } else {
                //Unset From InlineAttachment and assign as Attachment
                $this->attachments['attachments'][] = $att; //Assign Inline Attachment as Attachment
                unset($this->attachments['inlineAttachments'][$k]);
            }
        }

        //if attachment key as inline in heml then unset it

        foreach ($this->attachments['attachments'] as $k => $att) {
            if (
                $att['Type'] == "image/png" ||
                $att['Type'] == "image/jpeg" ||
                $att['Type'] == "image/jpg" ||
                $att['Type'] == "image/gif"
            ) {
                if ($att['contentID'] && strpos($html, $att['contentID']) !== false) {
                    $this->attachments['inlineAttachments'][] = $att;
                    unset($this->attachments['attachments'][$k]);
                    $html = str_replace("cid:" . $att['contentID'], $att['filename'][1], $html);
                }
            }
        }

        return $html;
    }

    /**
     * Create Message Instance
     * @param String $mimeMessage MIME Message
     * @return Google\Service\Gmail\Message
     */
    public function createMessage(String $mimeMessage)
    {
        $message = new GoogleMail\Message();
        $mimeMessage = strtr(base64_encode($mimeMessage), array('+' => '-', '/' => '_'));
        $message->setRaw($mimeMessage);
        return $message;
    }
}
