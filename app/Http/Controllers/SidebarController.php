<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\iconController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class SidebarController extends Controller
{

    public $labels;
    public $icons;
    public $user;
    public $labelSection2;

    function __construct()
    {
        //$this->middleware("auth");
        $this->labels = [
            "unassigned"    =>  ["label" => "Un Assigned", "icon" => "unassign"],
            "new"           =>  ["label" => "New", "icon" => "new"],
            "hold"          =>  ["label" => "Hold", "icon" => "hold"],
            "sent"          =>  ["label" => "Sent", "icon" => "sent"],
            "draft"         =>  ["label" => "Draft", "icon" => "draft"],
            "important"     =>  ["label" => "Important", "icon" => "important"],
            "archive"       =>  ["label" => "Archive", "icon" => "archive"],
            "release"       =>  ["label" => "Release", "icon" => "release"],

        ];

        $this->labelSection2 = [
            "reminder"      =>  ["label" => "Reminder", "icon" => "time"],
            "spam"          =>  ["label" => "Spam", "icon" => "spam"],
            "trash"         =>  ["label" => "Trashed", "icon" => "trash"],
            "outbox"        =>  ["label" => "Outbox", "icon" => "outbox"],
        ];

        $this->icons = new iconController();
        $this->user = Auth::user();
    }

    public static function settingModules()
    {
        return [
            "general" => [
                "label" => "General",
                "icon" => "cog",
                "accessLabel" => ["Super Admin", "Admin", "Merchandiser"],
            ],
            "ai" => [
                "label" => "AI Settings",
                "icon" => "ai",
                "accessLabel" => ["Super Admin", "Admin"],
            ],
            "department" => [
                "label" => "Department",
                "icon" => "grid",
                "accessLabel" => ["Super Admin", "Admin"],
            ],
            "view-filter" => [
                "label" => "View Filter",
                "icon" => "filter",
                "accessLabel" => ["Super Admin", "Admin"],
            ],
            "user" => [
                "label" => "Users",
                "icon" => "users",
                "accessLabel" => ["Super Admin"],
            ],
            "customer" => [
                "label" => "Customer",
                "icon" => "customer",
                "accessLabel" => ["Super Admin", "Admin"],
            ],
            "permission" => [
                "label" => "Permission",
                "icon" => "shield",
                "accessLabel" => ["Super Admin"],
            ],
        ];
    }

    public function label2Title($label = "")
    {
        if (!empty($label) && array_key_exists($label, $this->labels)) {
            return $this->labels[$label]["label"];
        }
    }

    function composeButton()
    {
        $ComposrICon = $this->icons->getIcon("write");
        $composeRoute = route("composeView", "new");
        return "<div class='compose-area'><a href='$composeRoute' class='btnCompose tooltip' title='Compose New'><i class='bg-icon compose-icon'></i><span class='nameLabel'>Compose</span></a></div>";
    }

    public static function index()
    {
        $sidebar = new SidebarController();

        $htm = "";
        $htm .= $sidebar->composeButton();
        $htm .= "<div class='sidebar-list-wrap'><ul class='list-wrap'>";

        if (request()->is("settings/*")) {
            //Settings Module Links
            $currentUserLabel = userLabel();

            //var_dump($sidebar->user->roles->pluck("name"));
            foreach ($sidebar->settingModules() as $k => $label) {
                //Permission
                $moduleStaticPermission = $label["accessLabel"];
                if (!empty($currentUserLabel)) {
                    if (!in_array($currentUserLabel, $moduleStaticPermission)) {
                        continue; // if user label not exists in module access
                    }
                } else {
                    continue; //if User label empty or not set
                }

                $icon = "--";
                if ($sidebar->icons->getIcon($label["icon"])) {
                    $icon = $sidebar->icons->getIcon($label["icon"]);
                }
                $url = URL::to("/settings/$k");
                $act = "";
                if (request()->is("settings/$k*")) {
                    $act = "class='active'";
                }
                if (access(["settings", $k, "view"])) {
                    $htm .= "<li $act><a href='$url' title='$label[label]'>$icon <span class='nameLabel'>$label[label]</span></a></li>";
                }
            }
        } else {
            foreach ($sidebar->labels as $k => $label) {
                $icon = "--";
                if ($sidebar->icons->getIcon($label["icon"])) {
                    $icon = $sidebar->icons->getIcon($label["icon"]);
                }
                $url = URL::to("/list/$k");
                $act = "";
                if (request()->is("list/$k*")) {
                    $act = "class='active'";
                }
                if (access(["box", $k])) {
                    $htm .= "<li $act><a href='$url' title='$label[label]' class='tooltip'>$icon <span class='nameLabel'> $label[label]</span></a></li>";
                }
            }
        }

        $htm .= "</ul>";
        if (!request()->is("settings/*")) {
            $htm .= "<ul class='bottom-nav'>";
            $chatIcon = $sidebar->icons->getIcon('chat');
            $htm .= "<li><a href='#' class='tooltip' id='chatTriger' title='Chat with other user'>$chatIcon</a></li>";

            foreach ($sidebar->labelSection2 as $k => $label) {
                $icon = "--";
                if ($sidebar->icons->getIcon($label["icon"])) {
                    $icon = $sidebar->icons->getIcon($label["icon"]);
                }
                $url = URL::to("/list/$k");
                $act = "";
                if (request()->is("list/$k*")) {
                    $act = "class='active'";
                }
                if (access(["box", $k])) {
                    $htm .= "<li $act><a href='$url' title='$label[label]' class='tooltip' data-bg='#000'>$icon</span></a></li>";
                }
            }
            $htm .= "</ul>";
        }
        $htm .= "</div>";
        echo $htm;
    }
}
