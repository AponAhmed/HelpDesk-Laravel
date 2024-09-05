<?php

namespace App\Http\Controllers;

use App\Models\MailList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Json;
use stdClass;

class MailActionController extends Controller
{
    private static $actions = [
        'assign' => [
            'label' => 'Assign',
            'box'   => ['unassigned', 'new', 'hold', 'outbox']
        ],
        'reminder' => [
            'label' => 'Reminder',
            'box'   => ['unassigned', 'new']
        ],
        'markSpam' =>  [
            'label' => 'Mark Spam',
            'box'   => ['unassigned', 'new']
        ],
        'markNotSpam' => [
            'label' => 'Mark Not Spam',
            'box'   => ['spam']
        ],
        'hold' => [
            'label' => 'Hold',
            'box'   => ['new', 'assigned']
        ],
        'unHold' => [
            'label' => 'Unhold',
            'box'   => ['hold']
        ],
        'markUnread' => [
            'label' => 'Mark Unread',
            'box'   => ['unassigned', 'new']
        ],
        'markRead' => [
            'label' => 'Mark Read',
            'box'   => ['unassigned', 'new']
        ],
        'resend' => [
            'label' => 'Re-Send',
            'box'   => ['sent']
        ],
        'trash' => [
            'label' => 'Trash',
            'box'   => "*",
            'box_not' => ['trash']
        ],
        'deleteC' => [
            'label' => 'Delete Conversation',
            'box'   => ['trash', 'draft'],
        ],
        'delete' => [
            'label' => 'Delete',
            'box'   => ['trash', 'draft'],
        ],
        'unTrash' =>  [
            'label' => 'Untrash',
            'box'   => ["trash"],
        ],
        'edit' => [
            'label' => 'Edit',
            'box' => ['unassigned', 'new', 'outbox'],
        ],
        'forward' => [
            'label' => 'Forward',
            'box' => "*",
            'box_not' => ['trash']
        ],
        'reply' => [
            'label' => 'Reply',
            'box' => ['new', 'unassigned'],
        ],
        'replyAll' => [
            'label' => 'Reply All',
            'box' => ['new', 'unassigned'],
        ],
        'print' => [
            'label' => 'Print',
            'box'   => "*",
        ]

    ];
    private static $user;
    private static $permission;
    private static $publicActions = ['assign', 'markRead', 'markUnread', 'print'];

    // function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Get User Actionset
     * @param string $box
     */
    static function  userActions($box = 'new')
    {
        //Pre setting
        self::$user = Auth::user();

        if (self::$user->roles !== 'Super Admin') {
            self::$permission = isset(self::$user->permission->mailAction) ? self::$user->permission->mailAction : (object) [];
        }

        $actions = [];
        foreach (self::$actions as $k => $props) {
            if (
                (is_array($props['box']) && in_array($box, $props['box']))
                || $props['box'] == '*'
            ) {
                if (isset($props['box_not']) && in_array($box, $props['box_not'])) {
                    continue;
                } //Self action Skipping Action
                if (!in_array($k, self::$publicActions)) //Public Actions
                    if (self::$user->roles != 'Super Admin' && self::$user->roles != 'Admin') { //User Role Action
                        if (self::$permission && !property_exists(self::$permission, $k)) //Peimissions
                            continue;
                    }
                $actions[] = $k;
            }
        }
        return $actions;
    }

    /**
     * Get Action from the request of client side
     */
    function actions()
    {
        return $this->userActions('unassigned');
    }

    /**
     * Get information what needs to perform any action
     * @param Request $req
     * @return mixed
     */
    function get(Request $req)
    {
        if ($req->has('type')) {
            $type = $req->get('type');
            if ($type == 'user') {
                $data = User::select("users.id", DB::raw("CONCAT(users.name,' (',substr(user_roles.name, 1, 1),')') AS name"))
                    ->orderBy("users.created_at", "DESC")
                    ->join('user_has_role', 'users.id', '=', 'user_has_role.user_id')
                    ->join('user_roles', 'user_has_role.role_id', '=', 'user_roles.id')
                    ->where('user_roles.name', '!=', 'Super Admin')
                    //->where('user_roles.name', '!=', 'Admin')
                    ->where('status', '1')->get();
                $data->prepend(['id' => '0', 'name' => 'Unassign']);
                return $data;
                //return User::select('id', 'name')->where('status', '1')->get();
            } elseif ($type == 'actions') {
            } else {
                return response()->json(['error' => true, 'message' => "Data Type not defined in controller"]);
            }
            //
        } else {
            return response()->json(['error' => true, 'message' => "Request data type is missing"]);
        }
    }

    /**
     * Update Mail List Label from Request
     * @param Request $request
     */
    function updateLabels(Request $request)
    {
        $id = $request->has('id') ? $request->get('id') : false;
        if ($id) {
            if ($this->updateLabelById($id, $request->get('labels'))) {
                return response()->json(['error' => false, 'message' => 'Action successfully updated']);
            } else {
                return response()->json(['error' => true, 'message' => 'Action Failed']);
            }
        }

        if ($request->has('ids')) {
            $ids = $request->get('ids');
            $label = $request->get('labels');
            $action = $request->get('action');
            $mails = MailList::whereIn('id', $ids)->get();

            $n = 0;
            foreach ($mails as $mail) {
                $labels = explode(',', $mail->labels);
                if ($action == 'add') {
                    if (!in_array($label, $labels)) {
                        $labels[] = $label;
                    } else {
                        continue;
                    }
                } else {
                    if (in_array($label, $labels)) {
                        $indx = array_search($label, $labels);
                        if ($indx) {
                            unset($labels[$indx]);
                        }
                    } else {
                        continue;
                    }
                }
                $mail->labels = implode(',', $labels);
                if ($mail->update()) {
                    $n++;
                }
            }

            if ($n > 0) {
                return response()->json(['error' => false, 'message' => "Action succeed with $n Data"]);
            } else {
                return response()->json(['error' => true, 'message' => 'Action Failed']);
            }
        }
    }


    public function setReminder(Request $request)
    {
        $id = $request->has('id') ? $request->get('id') : false;
        $ids = $request->has('ids') ? $request->get('ids') : false;
        $date = $request->has('date') ? $request->get('date') : false;

        if ($id && $date) {
            // Handle single record update
            $date = Carbon::parse($date);
            $mailList = MailList::find($id);
            if ($mailList) {
                $mailList->reminder = $date;
                if ($mailList->save()) {
                    return response()->json(['error' => false, 'message' => 'Action successfully updated']);
                } else {
                    return response()->json(['error' => true, 'message' => 'Action Failed']);
                }
            } else {
                return response()->json(['error' => true, 'message' => 'Record not found']);
            }
        } elseif ($ids && $date) {
            // Handle multiple records update
            $date = Carbon::parse($date);
            $idsArray = is_array($ids) ? $ids : explode(',', $ids); // Convert to array if needed

            $updated = MailList::whereIn('id', $idsArray)
                ->update(['reminder' => $date]);

            if ($updated) {
                return response()->json(['error' => false, 'message' => 'Actions successfully updated']);
            } else {
                return response()->json(['error' => true, 'message' => 'Action Failed']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Invalid parameters']);
        }
    }

    /**
     * Assign Mails to User
     * @param Request $request
     * @return string
     */
    function assign(Request $request)
    {
        if ($request->has('user') && $request->get('user') !== null) {
            $userID = $request->get('user');
            $succMsg = '';
            if (!empty($userID)) {
                $user = User::select(['name'])->where('id', $userID)->first();
                $succMsg = "Assigned Successfully to " . $user->name;
            } else {
                $succMsg = "Successfully marked as Unassigned";
            }

            if ($request->has('ids')) {
                //Bulk Assign
                $total = count($request->get('ids'));
                $n = 0;
                foreach ($request->get('ids') as $id) {
                    if ($this->assignSingle($id, $userID)) {
                        $n++;
                    }
                }
                if ($n > 0) {
                    if ($n == $total) {
                        return response()->json(['error' => false, 'message' => $succMsg]);
                    } else {
                        return response()->json(['error' => true, 'message' => 'Failed to assign ' . $total - $n . " Mail !"]);
                    }
                } else {
                    return response()->json(['error' => true, 'message' => 'Failed to assign, Nothing  Assigned.']);
                }
            } else if ($request->has('id')) {
                //Single Assign
                if ($this->assignSingle($request->get('id'), $userID)) {
                    return response()->json(['error' => false, 'message' => $succMsg]);
                } else {
                    return response()->json(['error' => true, 'message' => 'Failed to assign']);
                }
            }
        } else {
            return response()->json(['error' => true, 'message' => 'User Id is required']);
        }
    }

    /**
     * Update LAbels By ID
     * @param int $id
     * @param array $labels
     * @return boolean
     */
    function updateLabelById($id, $labels)
    {
        return MailList::find($id)->setLabels($labels);
    }

    /**
     * Delete Mail Thread
     * @param Request $request
     */
    function delete(Request $request)
    {
        if ($request->has('theread') && $request->has('box') && $request->get('theread') != "") {
            $theread = $request->get('theread');
            $box = $request->get('box');
            $actions = self::userActions($box);
            if (in_array('delete', $actions)) {
                try {
                    MailList::whereThread($theread)->delete();
                    return response()->json([
                        'error' => false,
                        'message' => 'Deleted permanently'
                    ]);
                } catch (\Exception $error) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Unable to delete due to error: ' . $error->getMessage()
                    ]);
                }
            } else {
                return response()->json(['error' => true, 'message' => 'You are not allowed to delete this']);
            }
        }
    }

    /**
     * Assign a User to a Mail List
     * @param Integer $mailID
     * @param Integer $userID
     * @return boolean true if mail was successfully updated
     */
    function assignSingle(int $mailID, int $userID)
    {
        return MailList::find($mailID)->setUser($userID);
    }
}
