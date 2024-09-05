<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Department;
use App\Models\MailDetails;
use Illuminate\Http\Request;
use \App\Models\MailList;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\VarDumper\VarDumper;
use Illuminate\Support\Facades\Storage;

class ComposeController extends Controller
{
    //
    private Department $department;

    // public function __construct()
    // {
    //     $this->middleware("auth");
    // }

    /**
     * Show the form for creating a new resource.
     */
    function composeView($type, $id = null)
    {
        $data = new MailList();
        if ($id) {
            $data = MailList::where('id', $id)->with('MailDetails')->first();
        }
        return view("compose.new")->with(['type' => $type, 'data' => $data]);
    }



    public function compose_inline(Request $request)
    {
        $composeData = $request->all();
        $data           = array();

        //----------------------------------------------------------------
        $attachments = [
            'attachments' => array(),
            'inlineAttachments' => array(),
        ];

        $id             = $request->has('id') ? $request->id : false;
        $addresses      = $request->has('address') ? $request->address : [];
        $status         = $request->has('status') ? $request->status : 'DRAFT';
        $subject        = $request->has('subject') ? $request->subject : '';
        $body           = $request->has('body') ? $request->body : '';
        $attachments['attachments'] = $request->has('attachments') ? $request->attachments : [];
        $attachments['inlineAttachments'] = $request->has('inlineAttachments') ? $request->inlineAttachments : [];
        $replyOf        = $request->has('replyOf') ? $request->replyOf : [];

        // //Attachment Modify
        // if (!isset($attachments['attachments'])) {
        //     $attachments['attachments'] = [];
        // }
        // if (!isset($attachments['inlineAttachments'])) {
        //     $attachments['inlineAttachments'] = [];
        // }

        // dd($attachments);
        //Data Array
        $data['subject'] = $subject;

        //Status Modifying
        if ($status == 'active') {
            if (Auth()->user()->canSend()) {
                $status = 'OUT';
            } else {
                $status = 'RELEASE';
            }
        } else {
            $status = 'DRAFT';
        }

        //Headers ----------------------------------------------------------------
        $replyOfId = false;
        $headers = [
            'date' => date('D, d M Y H:i:s O'), //'Tue, 17 Jan 2023 03:20:12 +0000 (UTC)',
            'from' => [],
            'message_id' => '',
            'subject' => $data['subject'],
            'to' => [
                //['display' => 'name', 'address' => '']
            ],
            'cc' => [
                //['display' => 'name', 'address' => '']
            ],
            'bcc' => [
                //['display' => 'name', 'address' => '']
            ],
        ];

        //Reply or New or forward

        if (
            isset($replyOf['type']) &&
            ($replyOf['type'] == 'reply' || $replyOf['type'] == 'replyall') &&
            isset($replyOf['id']) &&
            $replyOf['id'] != ''
        ) {
            //Existing Customer
            $replyOfId = $replyOf['id'];
            //existing data of reply
            $replyOfData = MailList::find($replyOfId);
            //dd($replyOfData->getCustomer);
            $customer = $replyOfData->getCustomer;
            $exHeaders = $replyOfData->headers();
            //Reply headers
            $headers['to'] = (array) $exHeaders->from;
            //Reply All headers
            if ($replyOf['type'] == 'replyall') {
                //all cc and bcc will be add in new headers
                $headers['cc'] = (array) $exHeaders->cc;
                $headers['bcc'] = (array) $exHeaders->bcc;
            }
            //dd($replyOfData->getDepartment);
            $this->department = $replyOfData->getDepartment;
        } else {
            ################################
            ## TODO::Default department Flag for department
            ################################
            $defaultDepartment = 1;
            $this->department = Department::find($defaultDepartment);

            //Customar Creation if Forword or New Message
            //TO -Header
            if (isset($addresses['to']) && !empty($addresses['to'])) {
                foreach ($addresses['to'] as $address) {

                    $headers['to'][] = ['display' => $this->getNameFromEmail($address), 'address' => $address];
                }
            }
            //CC -Header
            if (isset($addresses['cc']) && !empty($addresses['cc'])) {
                foreach ($addresses['cc'] as $address) {
                    $headers['cc'][] = ['display' => $this->getNameFromEmail($address), 'address' => $address];
                }
            }
            //BCC -Header
            if (isset($addresses['bcc']) && !empty($addresses['bcc'])) {
                foreach ($addresses['bcc'] as $address) {
                    $headers['bcc'][] = ['display' => $this->getNameFromEmail($address), 'address' => $address];
                }
            }

            if (isset($addresses['to'][0])) {
                $customer = $this->customerGetCreate($addresses['to'][0]);
            } else {
                //When No receipents are available
                return response()->json([
                    'error' => true,
                    'message' => 'Receipent not found',
                ]);
            }
        }
        //Set From In Headers
        $headers['from'] = ['display' => $this->department->name, 'address' => $this->department->email, 'is_group' => false]; //{"display":"Apon Ahmed","address":"spiapon@gmail.com","is_group":false}

        //List Data Build
        $listData = [
            'snippet'       => $this->makeSnipet($body),
            'subject'       => $data['subject'],
            'user'          => auth()->user()->id,
            'customer'      => $customer->id,
            'department'    => $this->department->id,
            'rs'            => 1,
            'labels'        => "$status",
            'date'          => date("D M j G:i:s T Y"),
        ];
        //If This is a reply mail
        if ($replyOfId) {
            $listData['reply_of'] = $replyOfId;
            $listData['msg_theread'] = $replyOfData->msg_theread;
        }
        //dd($listData);
        //List Data ready before here
        $detailsData = [
            'msg_body' => $body,
            'header' => json_encode($headers),
            'attachments' => json_encode($attachments),
        ];

        $response = ['error' => false, 'message' => ''];
        if ($id) {
            //Updated
            if (MailList::find($id)->update($listData)) {
                MailDetails::where('list_id', $id)->update($detailsData);
                $response['error'] = false;
                $response['id'] =  $id;
            } else {
                $response['error'] = true;
                $response['id'] =  $id;
                $response['message'] = 'Update failed';
            }
        } else {
            //Add
            //$listData['msg_theread'] = randString();
            $list = new MailList($listData);
            if ($list->save()) {
                $detailsData['list_id'] = $list->id;
                MailDetails::updateOrCreate($detailsData);
                $response['error'] = false;
                $response['id'] =  $list->id;
            } else {
                $response['error'] = true;
                $response['id'] =  false;
                $response['message'] =  'Failed to create a new Mail';
            }
        }
        //Autosave Response
        if ($status == 'DRAFT') {
            $response['auto_save'] = true;
        } else {
            $response['auto_save'] = false;
            if ($status == 'OUT') {
                $box = ' to Outbox';
            } else {
                $box = ', waiting for Release';
            }

            $response['message'] =  (isset($replyOf['type']) ? ucfirst($replyOf['type']) : "New") . ' Mail Sent successfully' . $box;
        }

        return response()->json($response);
        //dd($detailsData);

        //response {"error":false,"message":"","id":"10","auto_save":true}
    }
    /**
     * Store a newly created Message in Database.
     */
    public function compose(Request $req)
    {
        if ($req->has('formData')) {
            $serializeData  = $req->formData;
            $data           = array();
            parse_str($serializeData, $data);
            $status = $req->status;
            //Message Body
            $body = $req->has('body') ? $req->body : '';

            if (isset($data['to'][0])) {
                $customer = $this->customerGetCreate($data['to'][0]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Receipent not found',
                ]);
            }

            $defaultDepartment = 1; // TODO::Default department Flag for department
            if ($status == 'active') {
                if (auth()->user()->canSend()) {
                    $status = 'OUT';
                } else {
                    $status = 'RELEASE';
                }
            } else {
                $status = 'DRAFT';
            }

            $listData = [
                'snippet'       => $this->makeSnipet($body),
                'subject'       => $data['subject'],
                'user'          => auth()->user()->id,
                'customer'      => $customer->id,
                'department'    => $defaultDepartment,
                'rs'            => 1,
                'labels'        => "$status",
                'date'          => date("D M j G:i:s T Y"),
            ];


            $headers = $this->buildHeaders($req);
            $attachments = isset($data['attachments']) ? $data['attachments'] : [];
            //Attachment Modify
            if (!isset($attachments['attachments'])) {
                $attachments['attachments'] = [];
            }
            if (!isset($attachments['inlineAttachments'])) {
                $attachments['inlineAttachments'] = [];
            }

            $detailsData = [
                'msg_body' => $body,
                'header' => $headers,
                //[Have to Work With Details Data of Attachment]
                // {
                //     attachments:[
                //         {
                //            filename:[orginal,actual],
                //            type : "", //MIME type
                //            contentId : ""
                //         },{}...
                //     ],
                //     inlineAttachments:[
                //          ................
                //     ]
                // }
                'attachments' => json_encode($attachments),

            ];

            $response = ['error' => false, 'message' => ''];
            if ($req->has('id') && !empty($req->id)) {
                //Updated
                if (MailList::find($req->id)->update($listData)) {
                    MailDetails::where('list_id', $req->id)->update($detailsData);
                    $response['error'] = false;
                    $response['id'] =  $req->id;
                } else {
                    $response['error'] = true;
                    $response['id'] =  $req->id;
                    $response['message'] = 'Update failed';
                }
            } else {
                //Add
                $listData['msg_theread'] = randString();
                $list = new MailList($listData);
                if ($list->save()) {
                    $detailsData['list_id'] = $list->id;
                    MailDetails::updateOrCreate($detailsData);
                    $response['error'] = false;
                    $response['id'] =  $list->id;
                } else {
                    $response['error'] = true;
                    $response['id'] =  false;
                    $response['message'] =  'Failed to create a new Mail';
                }
            }
            //Autosave Response
            if ($status == 'DRAFT') {
                $response['auto_save'] = true;
            } else {
                $response['auto_save'] = false;
                if ($status == 'OUT') {
                    $box = ' to Outbox';
                } else {
                    $box = ', waiting for Release';
                }
                $response['message'] =  'New Mail Sent successfully' . $box;
            }
            return response()->json($response);
        }
    }

    function getNameFromEmail($email)
    {
        // Attempt to find the customer by email
        $customer = Customer::where('email', $email)->first();
        // If a record is found, return the name
        if ($customer) {
            return $customer->name;
        }
        // If no record found, generate and return name from email address
        $name = $this->generateNameFromEmail($email);

        // Create a new customer record with the generated name and email
        Customer::create(['name' => $name, 'email' => $email]);

        return $name;
    }

    function generateNameFromEmail($email)
    {
        // Split the email address into an array
        $emailParts = explode('@', $email);
        // Get the first part of the email address
        $name = $emailParts[0];
        // Replace any underscores with spaces
        $name = str_replace('_', ' ', $name);
        //replace all numaric characters with null
        $name = preg_replace('/[0-9]+/', '', $name);
        // Return the name
        return ucwords($name);
    }



    /**
     * Build Local header string
     * @param Request $request
     * @return string
     */
    function buildHeaders(Request $req)
    {
        $formData = [];
        parse_str($req->formData, $formData);

        $headers = [
            'date' => date('D, d M Y H:i:s O'), //'Tue, 17 Jan 2023 03:20:12 +0000 (UTC)',
            'from' => [],
            'message_id' => '',
            'subject' => $formData['subject'],
            'to' => [
                //['display' => 'name', 'address' => '']
            ],
            'cc' => [
                //['display' => 'name', 'address' => '']
            ],
            'bcc' => [
                //['display' => 'name', 'address' => '']
            ],
        ];

        if (isset($formData['to']) && !empty($formData['to'])) {
            foreach ($formData['to'] as $address) {
                $headers['to'][] = ['display' => $this->getNameFromEmail($address), 'address' => $address];
            }
        }
        if (isset($formData['cc']) && !empty($formData['cc'])) {
            foreach ($formData['cc'] as $address) {
                $headers['cc'][] = ['display' =>  $this->getNameFromEmail($address), 'address' => $address];
            }
        }
        if (isset($formData['bcc']) && !empty($formData['bcc'])) {
            foreach ($formData['bcc'] as $address) {
                $headers['bcc'][] = ['display' =>  $this->getNameFromEmail($address), 'address' => $address];
            }
        }

        return json_encode($headers);
    }

    /**
     * Get existing or Create New Customer
     * @param Request $req
     */
    function customerGetCreate($email)
    {
        $customer = Customer::where('email', $email)->first();
        if ($customer) {
            return $customer;
        } else {
            $name = '';
            $part = explode('@', $email);
            $name = ucfirst($part[0]);
            $customer = new Customer(['email' => $email, 'name' => $name]);
            $customer->save();
        }
        return $customer;
    }

    /**
     * Get Dnipet from Full Body Message
     * @param String Full Message
     */
    function makeSnipet($strings, $length = 80)
    {
        $strings = strip_tags($strings);
        $strings = preg_replace('/\s+/', ' ', $strings);
        if (strlen($strings) > $length) {
            return substr($strings, 0, $length) . "...";
        }
        return $strings;
    }

    /**
     * Ajax Upload Attachment into storage
     * @param Request $request
     */
    public function uploadAttachment(Request $request)
    {
        if ($request->hasFile('mail_attachment')) {
            $file = $request->file('mail_attachment');
            $orgName = $file->getClientOriginalName();
            $fileName = time() . '-_-' . $orgName;
            $filePath = config('attachment.attachment_path') . "/$fileName";
            if (Storage::disk(config('attachment.disk'))->put($filePath, file_get_contents($file))) {
                $info = [
                    'filename' => [$orgName, $fileName],
                    'contentID' => randString(11),
                    'Type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
                echo json_encode(array("error" => false, "fileName" => $fileName, 'details' => $info));
            } else {
                echo json_encode(array("error" => true, "message" => 'File Upload Failed'));
            }
        }
        return;
    }

    public function removeAttachment() {}
}
