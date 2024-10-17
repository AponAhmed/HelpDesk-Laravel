<?php

namespace App\Models;

use App\Events\MailArrived;
use App\Http\Controllers\FilterControl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MailDetails;
use App\Models\User;
use App\Models\Department;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class MailList extends Model
{
    use HasFactory;
    public $historyLimit = 1;
    protected $table = "mail_list";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "msg_id",
        "msg_theread",
        "snippet",
        "subject",
        "user",
        "customer",
        "department",
        "rs",
        "labels",
        "date",
        'reply_of',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //"msg_id",
        //"msg_theread",
        "created_at",
        "updated_at",
        //"labels",
        "customer",
        "user",
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "date" => "date:d M, Y",
    ];

    protected $appends = ["customerName", "important", "userName", 'historyCount'];


    public function MailDetails(): HasOne
    {
        $detrails = $this->hasOne(MailDetails::class, "list_id", "id");
        if ($detrails) {
            return $detrails;
        }
        return false;
    }

    /**
     * Get Headers of Mail
     * @return Object Headers
     */
    public function headers()
    {
        $details = $this->MailDetails;
        if ($details) {
            return json_decode($details->header);
        }
        return new stdClass;
    }

    /**
     * @param string $type Address Type
     * @param Array $addresses Array of addresses
     */
    public function addresses($type = 'to')
    {
        if (property_exists($this->headers(), $type)) {
            return $this->headers()->$type;
        }
        return [];
    }

    public function user()
    {
        return $this->hasOne(User::class, "id", "user");
    }

    public function getDepartment(): HasOne
    {
        return $this->hasOne(Department::class, "id", "department");
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, "id", "customer");
    }

    /**
     * Get Labels of Mail list
     * @return array of labels
     */
    public function getLabels()
    {
        return array_filter(explode(",", $this->labels));
    }

    /**
     * Set Label In Mail list
     * @param array $labels
     * @return void
     */
    public function setLabels($labels)
    {
        $this->labels = implode(",", array_filter($labels));
        return $this->update();
    }

    /**
     * Add A Label to Mail
     * @param string $label
     */
    function addLabel($label)
    {

        $labels = explode(",", $this->labels);
        $labels[] = $label;
        $this->labels = $this->labels = implode(",", array_filter($labels));
        if ($label == 'TRASH') {
            $this->broadcast('remove');
        }

        return $this;
    }

    /**
     * Remove A Label from a mail List
     */
    function removeLabel($label)
    {
        $labels = explode(",", $this->labels);
        $labels = array_filter($labels, function ($value) use ($label) {
            return $value !== $label;
        });
        $this->labels = $this->labels = implode(",", array_filter($labels));
        return $this;
    }


    /**
     * Set User Agent To Mail as Assign
     * @param int $userID
     * @return boolean
     */
    function setUser($userID)
    {
        $this->user = $userID;

        if ($this->update()) {
            $action = $this->user != 0 ? "assign" : "unAssign";
            $this->broadcast($action);
            return true;
        }
        return false;
    }

    /**
     * Message STARTED flag
     */
    public function getimportantAttribute()
    {
        $labels = $this->getLabels();
        if (in_array("IMPORTANT", $labels)) {
            return true;
        }
        return false;
    }

    public function getAttachments()
    {
        return $this->MailDetails ? (object) $this->MailDetails->getAttachmentData() : null;
    }

    public function  getUserNameAttribute()
    {
        $user = $this->user()->first();
        if ($user) {
            return $user->name;
        } else {
            return 'Unassigned';
        }
    }

    public function gethistoryCountAttribute()
    {
        return $query = DB::table('mail_list')
            ->where('mail_list.msg_theread', $this->msg_theread)
            ->where('labels', 'not like', '%TRASH%')
            ->count();
    }

    public function getcustomerNameAttribute()
    {
        return $this->customerName = $this->getCustomer->name;
    }


    public function getHistory($nexid = null, $limit = 1)
    {
        $this->historyLimit = $limit;

        $query = DB::table('mail_list')
            ->select('mail_list.id', 'mail_list.rs', 'mail_list.labels', 'mail_list.date', 'mail_list.snippet', 'mail_details.header AS headers')
            ->join('mail_details', 'mail_details.list_id', '=', 'mail_list.id')
            ->where('mail_list.msg_theread', $this->msg_theread)
            ->where('mail_list.id', '!=', $this->id)
            ->where('labels', 'not like', '%TRASH%')
            ->orderBy('mail_list.id', 'DESC');

        if ($limit != 'all') {
            $query->limit($this->historyLimit + 1); // Fetch one more record than the specified limit
        }


        if ($nexid) {
            $query->where('mail_list.id', '<=', $nexid);
        }

        $results = $query->get();
        // Apply timeFormat method to the date field
        $results = $results->map(function ($item) {
            $item->date = timeFormat($item->date) . " (" . timeago($item->date) . " ago)";
            return $item;
        });

        if ($this->historyLimit == "all") {
            $hasMore = false;
        } else {
            $hasMore = count($results) > $this->historyLimit;
        }

        // Remove the extra record if there are more records available
        if ($hasMore) {
            $lastItem = $results->pop();
            $nextid = $lastItem->id;
        } else {
            $nextid = null;
        }

        return [
            'history' => $results,
            'hasMore' => $hasMore,
            'nextid' => $nextid
        ];
    }


    public function toArray()
    {
        $attributes = parent::toArray();
        $attributes['attachments'] = $this->getAttachments();
        //Remove Customer Direct
        unset($attributes['get_customer']);
        //$attributes['headers'] = $this->headers();
        $attributes = FilterControl::apply($attributes, $this->getDepartment->id);
        return $attributes;
    }

    public function scopeWhereThread($query, $thread)
    {
        return $query->where('msg_theread', $thread);
    }

    public function delete()
    {
        $this->broadcast('remove');
        parent::delete();
    }

    public function broadcast($action = "")
    {
        //Other pre delete action will here

        try {
            broadcast(new MailArrived($this, $action));
        } catch (\Exception $e) {
            // Handle the exception if broadcast server is down
            Log::error('Broadcasting failed: ' . $e->getMessage());
        }
    }
}
