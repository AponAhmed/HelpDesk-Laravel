<?php

namespace App\Http\Controllers;

use App\Models\MailDetails;
use App\Models\MailList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailReadController extends Controller
{
    public $id;
    public $listItem;
    public $thread;
    public $data;


    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }


    /**
     * @param Request $request
     */
    function details(Request $request)
    {
        if ($request->has('id')) {
            return $this->read($request->get('id'));
        }
    }
    /**
     * Get Single mail Details
     * Returns JSON response
     */
    function GetBody_($id)
    {
        //return $this->read($id);
        $details = MailDetails::select(['msg_body', 'attachments', 'list_id'])
            ->where('list_id', '=', $id)->first()->toArray();
        return response()->json($details);
    }

    function GetBody($id)
    {
        // Fetch the mail details
        $details = MailDetails::select(['msg_body', 'attachments', 'list_id'])
            ->where('list_id', '=', $id)->first()->toArray();

        // Process the message body to separate the new and replied parts
        $details['msg_body'] = $this->separateRepliedPartWithToggler($details['msg_body'], $id);

        // Return the modified details as JSON
        return response()->json($details);
    }

    private function separateRepliedPartWithToggler($msgBody, $id)
    {
        // Regex patterns for different email service providers
        $patterns = [
            // Gmail
            '/(<div class="gmail_quote">.*<\/blockquote><\/div>)/sU',
            // Outlook
            '/(<div id="appendonsend">.*)<\/body>/sU',
            // Yahoo (placeholder, adjust based on actual structure)
            '/(<div class="yahoo_quoted">.*<\/blockquote><\/div>)/sU'
        ];

        // Loop through patterns and replace matched content with the toggler structure
        foreach ($patterns as $pattern) {
            $msgBody = preg_replace(
                $pattern,
                '<div class="body-toggler" id="tgigger-' . $id . '"><span><span class="dot"></span><span class="dot"></span><span class="dot"></span></span></div><div id="' . $id . '" class="body-toggle-section" style="display:none;">$0</div></div>',
                $msgBody
            );
        }


        return $msgBody;
    }


    function loadMoreMail(Request $request)
    {
        if ($request->has('id') && $request->has('lastid')) {
            $id = $request->get('id');
            //$mail=MailList::find($id);
            //dd($mail);
            $limit = $request->has('number_of_items') ? $request->get('number_of_items') : 1;

            return MailList::find($id)->getHistory($request->get('lastid'), $limit);
        } else {
            return response()->json(['error' => true, 'message' => 'Bad Request, No id provided !']);
        }
    }

    /**
     * Get Mail Thread data from database
     */
    public function read($id = false)
    {
        if ($id) {
            $this->id = $id;
        }

        $this->listItem = MailList::from('mail_list as l')
            ->select([
                'l.id',
                'l.department',
                'l.msg_theread',
                'l.subject',
                'l.date',
                'l.rs',
                'l.labels',
                'd.msg_body as body',
                'd.attachments',
                'd.header',
                'c.name',
                'c.email',
            ])
            ->join('mail_details as d', 'd.list_id', 'l.id')
            ->join('customers as c', 'c.id', 'l.customer')
            ->where('l.id', $this->id)->first();

        //all Siblings of the same thread
        $this->data['id'] = $this->listItem->id;
        $this->data['theread'] = $this->listItem->msg_theread;
        $this->data['name'] = $this->listItem->name;

        if (Auth::user()->roles == 'Admin' || Auth::user()->roles == 'Super Admin') {
            $this->data['email'] = $this->listItem->email;
        }

        $this->data['subject'] = $this->listItem->subject;
        $this->data['headers'] =  FilterControl::apply($this->listItem->header, $this->listItem->department); //$this->listItem->header;
        $this->data['rs'] = $this->listItem->rs;

        //$historyData=$this->listItem->getHistory(1);

        $this->data['historyData'] = $this->listItem->getHistory(); //$this->listItem->getHistory()->toArray();
        $this->data['body'] =  $this->separateRepliedPartWithToggler(
            FilterControl::apply($this->listItem->body, $this->listItem->department),
            $this->listItem->id
        );

        $this->data['attachments'] = $this->listItem->attachments;
        $this->data['date'] = timeFormat($this->listItem->date->toString()) . " (" . timeago($this->listItem->date->toString()) . " ago)";

        $this->updateStatus();
        return $this->response();
    }

    /**
     * View Filter Data for Details View
     */
    static function filterData($data)
    {
        //User
        $user = Auth::user();
        if ($user->roles == 'Admin' || $user->roles == 'Super Admin') {
            return $data;
        }
        //

        //Filter Roles
        return $data;
    }

    /**
     * Return Response object as response JSON
     */
    function response()
    {

        return response()->json(self::filterData($this->data));
    }

    /**
     * Update the status of Read mail
     */
    function updateStatus()
    {
        $labels = $this->listItem->getLabels();
        $indx = array_search('UNREAD', $labels);

        if ($indx !== false) {
            unset($labels[$indx]);
            return $this->listItem->setLabels($labels);
        }
        return false;
    }
}
