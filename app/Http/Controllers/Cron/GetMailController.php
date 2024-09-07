<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleService\Gmail;
use App\Http\Controllers\GoogleService\OAuth;
use App\Models\Customer;
use App\Models\Department;
use App\Models\MailDetails;
use App\Models\MailList;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GetMailController extends Controller
{
    private $debug = true;
    private $quote = '_HIDE'; //'_HIDE', '_SHOW', '_REMOVE';

    /**
     * All Department helpdesk have
     * @var type Array
     */
    private $departments = [];

    /**
     * Current Department when Loop
     * @var type array
     */
    private $department;


    /**
     * Google OAuth
     * @var type Object
     */
    protected $oAuth;

    /**
     * aponahmed\gmailapi\src\Gmail Instance
     * @var type Object
     */
    public $mail;

    function __construct()
    {
        $this->departments = Department::where('status', '1')->get();
    }

    public function index()
    {
        // if (!extension_loaded("mailparse")) {
        //     phpinfo();
        //} else {
        $this->deptLoop();
        // }
    }

    function updateToken()
    {
        if ($this->oAuth->tokenStatus == "refreshed") {
            $this->department->oauth_token($this->oAuth->token);
            $this->department->save();
        }
    }

    function buildConnection()
    {
        $this->oAuth = new OAuth(config('app.google_app_credentials'), $this->department->oauth_token());
        $this->oAuth->tokenCheck(); //Check Access Token is Valid or Not;
        $this->updateToken(); //Update Access Token if it is expired and refreshed;

        if ($this->oAuth->connect) {
            $this->mail = new Gmail(
                $this->oAuth->client,
                empty($this->department['email']) ? 'me' : $this->department['email']
            );

            $this->mail->lebelIds = ['INBOX', 'UNREAD'];
            //$this->mail->lebelIds = ['q'=>'category:primary label:inbox is:unread'];
            //Attachment Directory set
            $this->mail->modifyMessageAfterDownload = env('MODIFY_LABEL_IDS', [
                'add' => ['TestDown'],
                'remove' => ['INBOX']
            ]);
            $this->mail->attachmentDir = Storage::disk(config('attachment.disk'))->path(config('attachment.attachment_path'));
            $this->mail->attachmentUri = Storage::disk(config('attachment.disk'))->url(config('attachment.attachment_path'));
            $this->mail->inlineAttachmentDir = Storage::disk(config('attachment.disk'))->path(config('attachment.inline_attachment_path'));
            $this->mail->inlineAttachmentUri = Storage::disk(config('attachment.disk'))->url(config('attachment.inline_attachment_path'));
        } else {
            if ($this->debug) {
                throw new Exception('Google Web Service Connection Error');
            } else {
                die();
            }
        }
    }

    function deptLoop()
    {
        if ($this->departments) {
            foreach ($this->departments as $department) {
                $this->department = $department;
                $this->buildConnection();
                $this->getMail();
            }
        } else {
            //Department Not Found
            echo "Department Not Found";
        }
    }

    function customerInsertIfNew($data)
    {
        return $this->findOrCreateCustomer($data['display'], $data['address']);
        //return Customer::firstOrCreate(['name' => $data['display'], 'email' => $data['address']]);

        //User::firstOrCreate(['name' => 'John Doe']);
    }

    // Your function to check and insert the customer
    function findOrCreateCustomer($name, $email)
    {
        // First, check if the customer with the given email already exists
        $existingCustomer = Customer::where('email', $email)->first();

        // If the customer with the email exists, return the instance
        if ($existingCustomer) {
            return $existingCustomer;
        }

        // If the customer with the email doesn't exist, create a new customer
        $newCustomer = new Customer();
        $newCustomer->name = $name;
        $newCustomer->email = $email;
        $newCustomer->save();

        // Return the newly created customer instance
        return $newCustomer;
    }

    function getMail()
    {
        $mails = $this->mail->get(1);
        //dd($mails);
        foreach ($mails as $mail) {
            //dd($mail);
            $customer = $this->customerInsertIfNew($mail['from'][0]);
            try {
                $data = [
                    "msg_id"        => $mail['id'],
                    "msg_theread"   => $mail['threadId'],
                    "snippet"       => $mail['snippet'],
                    "subject"       => $mail['subject'],
                    "user"          => 0,
                    "customer"      => $customer->id,
                    "department"    => $this->department->id,
                    "rs"            => 0,
                    "labels"        => "NEW,UNREAD",
                    "date"          => $mail['date'],
                ];
                $NewList = new MailList($data);
                $NewList->save();
                $details = [
                    "list_id" => $NewList->id,
                    "msg_body" => $mail['html'],
                    "header" => $this->headerBuild($mail), // json_encode($mail['headers']),
                    "attachments" => json_encode($mail['attachments']), //Only Attachments are accepted not inlined
                ];
                $newDetails = new MailDetails($details);
                if ($newDetails->save()) {
                    $info = ['theread' => $mail['threadId'], 'list-id' => $NewList->id, 'details-id' => $newDetails->id];
                    echo "Mail Downloaded:" . json_encode($info) . " <br>";
                    $this->mail->modifyLabel($mail['id']);
                }
                try {
                    broadcast(new \App\Events\MailArrived($NewList,'new'));
                } catch (\Exception $e) {
                    // Handle the exception if broadcast server is down
                    Log::error('Broadcasting failed: ' . $e->getMessage());
                }
            } catch (Exception $ex) {
                //throw $th;
                echo $ex->getMessage();
            }
        }
    }

    function headerBuild($mail)
    {
        $headers = [
            'date' => $mail['date'], //'Tue, 17 Jan 2023 03:20:12 +0000 (UTC)',
            'to' => $mail['to'], //['name' => 'address'],
            'from' => $mail['from'], //['name' => 'address'],
            'message_id' => $mail['messageId'],
            'subject' => $mail['subject'],
            'cc' =>  $mail['cc'],
            'bcc' => $mail['bcc'],
        ];
        return json_encode($headers);
    }
}
