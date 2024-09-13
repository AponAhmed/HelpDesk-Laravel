<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SidebarController;
use App\Models\MailDetails;
use App\Models\MailList;
use App\Traits\DataFilter;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//use only in developement ENV
use Symfony\Component\VarDumper\VarDumper;

class MailListController extends Controller
{
    use DataFilter;
    private $detailsControl;

    function __construct()
    {

        $this->detailsControl = [
            "reply" => [
                "box" => ["IN"],
            ],
            "foreword" => [
                "box" => ["IN", "SENT"],
            ],
        ];
    }

    public function index($label = "new")
    {
        $sidebar = new SidebarController();
        $title = $sidebar->label2Title($label);

        $data = ["title" => $title, 'box' => $label];
        return view("mailList", $data);
    }


    function mailStream()
    {
        echo "event: update";
    }


    /**
     * Data Filter By BOX
     */
    function boxFilter($data, $box)
    {
        $user = Auth::user();
        if ($box !== 'unassigned') {
            if ($user->roles != 'Admin' && $user->roles != 'Super Admin') {
                $data->where('user', '=', $user->id);
            }
        }

        $data->whereNull('reminder');


        $notLabels = ['TRASH', 'SPAM', 'HOLD'];

        $labels = [];
        switch ($box) {
            case 'new':
                # code...
                $data->whereRs('0');
                $labels = ['NEW'];
                break;

            case 'draft':
                $data->whereRs('1');
                $labels = ['DRAFT'];
                break;

            case 'sent':
                $data->whereRs('1');
                $labels = ['SENT'];
                break;

            case 'outbox':
                $data->whereRs('1');
                $labels = ['OUT'];
                break;

            case 'hold':
                $notLabels = [];
                //$data->whereRs('0');
                $labels = ['HOLD'];
                break;

            case 'important':
                $labels = ['IMPORTANT'];
                break;

            case 'archive':
                $labels = ['ARCHIVE'];
                break;

            case 'release':
                $data->whereRs('1');
                $labels = ['RELEASE'];
                break;

            case 'trash':
                $notLabels = [];
                $labels = ['TRASH'];
                break;

            case 'spam':
                $notLabels = [];
                $labels = ['SPAM'];
                break;

            default: //Unassigned
                # code...
                $data->whereRs('0');
                $data->whereUser('0');
                break;
        }
        /** Label Query */
        if (count($labels) > 0) {
            $data->where(function ($query) use ($labels) {
                foreach ($labels as $label) {
                    //var_dump($label);
                    $query->orWhere('labels', 'LIKE', "%$label%");
                };
            });
        }
        //Label Not IN
        if (count($notLabels) > 0) {
            $data->where(function ($query) use ($notLabels) {
                foreach ($notLabels as $label) {
                    //var_dump($label);
                    $query->Where('labels', 'NOT LIKE', "%$label%");
                };
            });
        }
        //Test Purpose:
        if (isset($_GET['qr'])) {
            $query = str_replace(array('?'), array('\'%s\''), $data->toSql());
            $query = vsprintf($query, $data->getBindings());
            dd($query);
        }

        return $data;
    }

    /**
     *Data By Eloquent ORM Data
     */
    public function dataOrm($box)
    {
        $data = MailList::orderBy("created_at", "DESC")
            ->whereIn('id', function ($query) use ($box) {
                $query->selectRaw('MAX(id)')
                    ->from('mail_list');
                //Trashed Mail Thread will be default as well;
                if ($box != 'trash') {
                    $query->where('labels', 'not like', '%TRASH%');
                }
                $query->groupBy('msg_theread');
            });

        //Commented today 21/6/23
        // $data = MailList::orderBy("id", "DESC")
        //     ->whereRaw("id IN(SELECT max(id) as id FROM mail_list GROUP BY $GroupByField)");

        $data = $this->boxFilter($data, $box);
        $data = $this->customFilter($data);
        $data = $this->customSearch($data, ['snippet', 'subject']);
        return $data;
    }

    function data($box = "new")
    {
        $this->itemPerPage();
        $data = $this->dataOrm($box);
        //dd($data->get()->toArray());
        $data = $data->paginate($this->MAX_IN_PAGE);
        $data = $this->addExtraData($data);
        $actions = MailActionController::userActions($box);
        $data = $data->toArray();
        $data['actions'] = $actions;
        return response()->json($data);
    }

    public function details($id)
    {
        $mailData = MailDetails::where("list_id", "=", $id)->first();
        $list = $mailData->mail_list;
        $data = [
            "listID"        => $list->id,
            "subject"       => $list->subject,
            "customer"      => $list->customerName,
            "date"          => timeFormat($list->date) . " (" . timeago($list->date) . " ago)",
            "customerEmail" => $list->customer()->first()->email,
            "body"          => $mailData->msg_body,
            "rs"            => $list->rs,
            "actions"       => $this->detailsControl($list),
        ];

        //$data =  FilterControl::apply($data, $list->department());

        return $data;
    }
    /**
     * To get Mail details Control or actions
     * @param MailList Object
     * @return Array of available actions
     */
    public function detailsControl(MailList $list)
    {
        //VarDumper::dump($list);
        return [];
    }
}
