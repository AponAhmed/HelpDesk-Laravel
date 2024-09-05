<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Http\Controllers\GoogleApiService;
use Google\Service\Gmail;
use Google\Service\Gmail\Draft;
use Google\Service\Gmail\Message;
use Google\Service\Gmail\ModifyMessageRequest;
use Exception;
use Symfony\Component\Mime\Part\MessagePart;
use Symfony\Component\VarDumper\VarDumper;
use Illuminate\Support\Facades\Storage;

use App\Models\Customer;
use App\Models\MailList;
use App\Models\MailDetails;

class CronController extends Controller
{
    //
    /**
     *
     */
    function index($action)
    {
        // dd($action);
        $this->action = trim($action);
        if ($this->action == "in") {
            $this->in();
        } else {
            $this->out();
        }
    }

    public function decodeBody($body)
    {
        $rawData = $body;
        $sanitizedData = strtr($rawData, "-_", "+/");
        $decodedMessage = base64_decode($sanitizedData);
        if (!$decodedMessage) {
            $decodedMessage = false;
        }
        return $decodedMessage;
    }
    /**
     *
     */
    private function in()
    {
        $departments = Department::all();
        foreach ($departments as $department) {
            $this->department = $department;
            $tokenJson = $department->oauth_token;
            $tokenObj = json_decode($tokenJson);
            if ($tokenObj) {
                $client = new GoogleApiService($department);
                if ($client->is_connected) {
                    $this->gClient = $client->client;
                    echo $department->email;
                    echo $this->getMail();
                } else {
                    echo "Connection Error";
                }
                //dd($Gapi);
            } else {
                echo "Connection Error, Re login ($department->email) With Google OAuth<br>";
            }
        }
    }
    /**
     *
     */
    private function out()
    {
        $departments = Department::all();
        foreach ($departments as $department) {
            $this->department = $department;
            $tokenJson = $department->oauth_token;
            $tokenObj = json_decode($tokenJson);
            if ($tokenObj) {
                $client = new GoogleApiService($department);
                if ($client->is_connected) {
                    $this->gClient = $client->client;
                    $this->sendMail();
                } else {
                    echo "Connection Error";
                }
            } else {
                echo "Connection Error, Re login ($department->email) With Google OAuth<br>";
            }
        }
    }

    private function getMail()
    {
        $gmail = new Gmail($this->gClient);
        $search = "category:updates"; //primary
        $list = $gmail->users_messages->listUsersMessages("me", [
            "maxResults" => 2,
            "labelIds" => "UNREAD",
            "q" => $search,
        ]); //maxResults,labelIds,q,

        $messageList = $list->getMessages();
        $inboxMessage = [];
        echo "<pre>";
        foreach ($messageList as $mlist) {
            $optParamsGet2["format"] = "full";

            $messageId = $mlist->getId(); // Grab  Message ID

            $single_message = $gmail->users_messages->get(
                "me",
                $mlist->id,
                $optParamsGet2
            );
            $thereadId = $single_message->threadId;
            $message_id = $mlist->id;
            $headers = $single_message->getPayload()->getHeaders();
            $snippet = $single_message->getSnippet();
            $message_parts = $single_message->getPayload()->getParts();
            //$attachId = $message_parts[1]["body"]["attachmentId"];
            $attachments = [];
            foreach ($message_parts as $pert) {
                if ($pert["body"]["attachmentId"]) {
                    $attachId = $pert["body"]["attachmentId"];
                    $attachments[] = [
                        "attachID" => $attachId,
                        "filename" => $pert->filename,
                        "sizes" => $pert->getBody()->size,
                        "mime" => $pert->mimeType,
                    ];
                }
            }
            //VarDumper::dump($attachments);
            /* $attach = false;
      if ($attachId) {
        $attach = $gmail->users_messages_attachments->get(
          "me",
          $mlist->id,
          $attachId
        );
      } */

            $headersParse = [
                "Delivered-To" => "",
                "To" => "",
                "From" => "",
                "Return-Path" => "",
                "Date" => "",
                "Message-ID" => "",
                "Subject" => "",
                "Content-Type" => "",
            ];
            foreach ($headers as $single) {
                if (array_key_exists($single->getName(), $headersParse)) {
                    $headersParse[$single->getName()] = $single->getValue(); //All Header Parseed
                }
                if ($single->getName() == "Subject") {
                    $message_subject = $single->getValue();
                } elseif ($single->getName() == "Date") {
                    $message_date = $single->getValue();
                    $message_date = date("M jS Y h:i A", strtotime($message_date));
                } elseif ($single->getName() == "From") {
                    $message_sender = $single->getValue();
                    $message_sender = str_replace('"', "", $message_sender);
                }
            }

            $body = $this->getBody($gmail, $mlist, true);
            //VarDumper::dump($body);
            //continue;
            $senderInfo = $this->nameParse($headersParse["From"]);

            //Start Customer Section
            $customers = Customer::where("email", "=", "$senderInfo[email]")
                ->limit(1)
                ->get();
            if (count($customers) == 0) {
                $customer = new Customer();
                $customer->name = $senderInfo["name"];
                $customer->email = $senderInfo["email"];
                $customer->save();
            } else {
                $customer = $customers[0];
            }
            //End Customer
            $inboxMessageListData = [
                "msg_theread" => $thereadId,
                "msg_id" => $message_id,
                "snippet" => $snippet,
                "subject" => $message_subject,
                "date" => $message_date,
                "department" => $this->department->id,
                "rs" => "0",
                "labels" => "IN",
                "customer" => $customer->id,
            ];

            VarDumper::dump($inboxMessageListData);
            //continue;
            $mailExist = MailList::where("msg_id", "=", $message_id)
                ->limit(1)
                ->get();
            if (count($mailExist) > 0) {
                $this->removeLabel($gmail, $message_id, "UNREAD");
                continue;
            }

            $mailList = new MailList($inboxMessageListData);
            if ($mailList->save()) {
                $inboxMessageDetails = [
                    "msg_body" => $body,
                    "attachments" => json_encode($attachments),
                    "header" => json_encode($headersParse),
                    "list_id" => $mailList->id,
                ];
                $mailDetails = new MailDetails($inboxMessageDetails);
                $mailDetails->save();
            }

            //VarDumper::dump();
            $this->removeLabel($gmail, $message_id, "UNREAD");
            //$this->AddLabel($gmail, $message_id, "IMPORTANT");
        }
        //DD Modified
        //VarDumper::dump($headers);
        //VarDumper::dump($inboxMessageListData);
    }

    /**
     *Get Full Name of mail sender from string
     */
    public function nameParse($str)
    {
        $info = [];
        if (strpos($str, "<") !== false) {
            $prt = explode("<", $str);
            $info["name"] = trim($prt[0]);
            $emPrt = explode(">", $prt[1]);
            $info["email"] = trim($emPrt[0]);
        }
        return $info;
    }

    /**
     * Remove label from Message
     * @param $service
     * $service is google gmail service
     * @param $messageID
     * @param Google Message label string ()
     * @return Google message object
     */

    public function removeLabel($service, $messageID, $label)
    {
        $mods = new ModifyMessageRequest();
        $mods->setRemoveLabelIds([$label]);
        $modified = $service->users_messages->modify("me", $messageID, $mods);
        return $modified;
    }

    /**
     * Add  label In Message
     * @param $service
     * $service is google gmail service
     * @param $messageID
     * @param Label strind what to Add
     * @return Google message object
     */

    public function AddLabel($service, $messageID, $label)
    {
        $mods = new ModifyMessageRequest();
        $mods->setAddLabelIds([$label]);
        $modified = $service->users_messages->modify("me", $messageID, $mods);
        return $modified;
    }

    /**
     * To get Message Full Body
     * @param Gmail Service
     * @param Gmail single Message from  ($list->getMessages())
     * @return String decoded Body
     */

    public function getBody($gmail, $mlist, $saveInline2Local = false)
    {
        $message_id = $mlist->id;
        $optParamsGet2["format"] = "full";
        $single_message = $gmail->users_messages->get(
            "me",
            $message_id,
            $optParamsGet2
        );
        $payload = $single_message->getPayload();
        $parts = $payload->getParts();
        // With no attachment, the payload might be directly in the body, encoded.
        $body = $payload->getBody();
        $FOUND_BODY = false;
        // If we didn't find a body, let's look for the parts
        if (!$FOUND_BODY) {
            foreach ($parts as $part) {
                if ($part["parts"] && !$FOUND_BODY) {
                    foreach ($part["parts"] as $p) {
                        if ($p["parts"] && count($p["parts"]) > 0) {
                            foreach ($p["parts"] as $y) {
                                if ($y["mimeType"] === "text/html" && $y["body"]) {
                                    $FOUND_BODY = $this->decodeBody($y["body"]->data);
                                    break;
                                }
                            }
                        } elseif ($p["mimeType"] === "text/html" && $p["body"]) {
                            $FOUND_BODY = $this->decodeBody($p["body"]->data);
                            break;
                        }
                    }
                }
                if ($FOUND_BODY) {
                    break;
                }
            }
        }
        // let's save all the images linked to the mail's body:
        if ($FOUND_BODY && count($parts) > 1) {
            $images_linked = [];
            foreach ($parts as $part) {
                if ($part["filename"]) {
                    array_push($images_linked, $part);
                } else {
                    if ($part["parts"]) {
                        foreach ($part["parts"] as $p) {
                            if ($p["parts"] && count($p["parts"]) > 0) {
                                foreach ($p["parts"] as $y) {
                                    if ($y["mimeType"] === "text/html" && $y["body"]) {
                                        array_push($images_linked, $y);
                                    }
                                }
                            } elseif ($p["mimeType"] !== "text/html" && $p["body"]) {
                                array_push($images_linked, $p);
                            }
                        }
                    }
                }
            }
            // special case for the wdcid...
            preg_match_all('/wdcid(.*)"/Uims', $FOUND_BODY, $wdmatches);
            if (count($wdmatches)) {
                $z = 0;
                foreach ($wdmatches[0] as $match) {
                    $z++;
                    if ($z > 9) {
                        $FOUND_BODY = str_replace($match, "image0" . $z . "@", $FOUND_BODY);
                    } else {
                        $FOUND_BODY = str_replace(
                            $match,
                            "image00" . $z . "@",
                            $FOUND_BODY
                        );
                    }
                }
            }
            preg_match_all('/src="cid:(.*)"/Uims', $FOUND_BODY, $matches);
            if (count($matches)) {
                $search = [];
                $replace = [];
                // let's trasnform the CIDs as base64 attachements
                foreach ($matches[1] as $match) {
                    foreach ($images_linked as $img_linked) {
                        foreach ($img_linked["headers"] as $img_lnk) {
                            if (
                                $img_lnk["name"] === "Content-ID" ||
                                $img_lnk["name"] === "Content-Id" ||
                                $img_lnk["name"] === "X-Attachment-Id"
                            ) {
                                if (
                                    $match ===
                                    str_replace(
                                        ">",
                                        "",
                                        str_replace("<", "", $img_lnk->value)
                                    ) ||
                                    explode("@", $match)[0] ===
                                    explode(".", $img_linked->filename)[0] ||
                                    explode("@", $match)[0] === $img_linked->filename
                                ) {
                                    $search = "src=\"cid:$match\"";
                                    $mimetype = $img_linked->mimeType;
                                    $attachment = $gmail->users_messages_attachments->get(
                                        "me",
                                        $mlist->id,
                                        $img_linked["body"]->attachmentId
                                    );
                                    $data64 = strtr($attachment->getData(), [
                                        "-" => "+",
                                        "_" => "/",
                                    ]);

                                    if ($saveInline2Local) {
                                        //Store into Local Folder
                                        $inlineAttachmentPath = "attachments/inline/";
                                        $fileName = $message_id . "__" . $img_linked->filename;
                                        $filePath = $inlineAttachmentPath . $fileName;
                                        //VarDumper::dump($filePath);
                                        if (
                                            Storage::disk("local")->put(
                                                $filePath,
                                                base64_decode($data64)
                                            )
                                        ) {
                                            $url = Storage::url("app/" . $filePath);
                                            $replace = "src=\"$url\"";
                                        } else {
                                            $replace =
                                                "src=\"data:" . $mimetype . ";base64," . $data64 . "\"";
                                        }
                                    } else {
                                        $replace =
                                            "src=\"data:" . $mimetype . ";base64," . $data64 . "\"";
                                    }
                                    $FOUND_BODY = str_replace($search, $replace, $FOUND_BODY);
                                }
                            }
                        }
                    }
                }
            }
        }
        // If we didn't find the body in the last parts,
        // let's loop for the first parts (text-html only)
        if (!$FOUND_BODY) {
            foreach ($parts as $part) {
                if ($part["body"] && $part["mimeType"] === "text/html") {
                    $FOUND_BODY = $this->decodeBody($part["body"]->data);
                    break;
                }
            }
        }
        // With no attachment, the payload might be directly in the body, encoded.
        if (!$FOUND_BODY) {
            $FOUND_BODY = $this->decodeBody($body["data"]);
        }
        // Last try: if we didn't find the body in the last parts,
        // let's loop for the first parts (text-plain only)
        if (!$FOUND_BODY) {
            foreach ($parts as $part) {
                if ($part["body"]) {
                    $FOUND_BODY = $this->decodeBody($part["body"]->data);
                    break;
                }
            }
        }
        if (!$FOUND_BODY) {
            $FOUND_BODY = "(No message)";
        }
        // Finally, print the message ID and the body
        return $FOUND_BODY;
    }

    private function sendMail()
    {
        $service = new Gmail($this->gClient);
        $message = $this->createMessage(
            $this->department->email,
            "islamzakiul1@gmail.com",
            "test mail sender",
            "Hello, this is mail body"
        );

        $message = $service->users_messages->send(
            $this->department->email,
            $message
        );
        //dd($response);
        //$draft = $this->createDraft($service, $this->department->email, $msg);
    }

    /**
     * @param $sender string sender email address
     * @param $to string recipient email address
     * @param $subject string email subject
     * @param $messageText string email text
     * @return Message
     */
    function createMessage($sender, $to, $subject, $messageText)
    {
        $message = new Message();

        $rawMessageString = "From: <{$sender}>\r\n";
        $rawMessageString .= "To: <{$to}>\r\n";
        $rawMessageString .=
            "Subject: =?utf-8?B?" . base64_encode($subject) . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .=
            "Content-Transfer-Encoding: quoted-printable" . "\r\n\r\n";
        $rawMessageString .= "{$messageText}\r\n";

        $rawMessage = strtr(base64_encode($rawMessageString), [
            "+" => "-",
            "/" => "_",
        ]);
        $message->setRaw($rawMessage);
        return $message;
    }

    /**
     * @param $service Google_Service_Gmail an authorized Gmail API service instance.
     * @param $user string User's email address or "me"
     * @param $message Google_Service_Gmail_Message
     * @return Google\Service\Gmail\Draft
     */
    function createDraft($service, $user, $message)
    {
        $draft = new Draft();
        $draft->setMessage($message);

        try {
            $draft = $service->users_drafts->create($user, $draft);
            print "Draft ID: " . $draft->getId();
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }

        return $draft;
    }
}
