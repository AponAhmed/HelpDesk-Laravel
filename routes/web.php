<?php

use App\Events\TestEvent;
use App\Http\Controllers\AiService;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\PermissionAccess;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\BulkActionController;
use App\Http\Controllers\ComposeController;
use App\Http\Controllers\MailListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController as Depertment;
use App\Http\Controllers\CustomerController as Customer;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\GeneralSettings;
use App\Http\Controllers\MailActionController;
use App\Http\Controllers\MailReadController;
use App\Http\Controllers\ViewFilterController as ViewFilter;
use App\Http\Middleware\CheckAiAccess;
use App\Jobs\ProcessAttachment;
use App\Models\MailList;


Route::middleware('auth')->group(function () {
    Route::post('/ai', [AiService::class, 'generate'])->middleware(CheckAiAccess::class);


    Route::prefix("action")->controller(MailActionController::class)->group(function () {
        Route::get("/", "actions")->name("actions");
        Route::post("/assign", "assign")->name("assign");
        Route::post("/get", 'get')->name("get-helperData");
        Route::post("/delete", "delete")->name("delete");
        Route::post("/update-labels", "updateLabels")->name("updateLabels");
        Route::post("/set-reminder", "setReminder")->name("setReminder");
    });


    //Mail list and View Route
    Route::middleware(PermissionAccess::class . ":box")->group(function () {
        Route::get("/", [MailListController::class, "index"])->name("home");
        Route::get("/list/{label}", [MailListController::class, "index"])->name('mailListIndex');
        Route::get("/list/{label}/data", [MailListController::class, "data"]);
        Route::get("/data", [MailListController::class, "data"])->name('mailList');
        Route::get("/mail-stream", [MailListController::class, "mailStream"])->name('mailStream');
    });

    Route::get("/compose/{type}/{id?}", [ComposeController::class, "composeView"])->name("composeView");
    Route::post('/upload-attachment', [ComposeController::class, 'uploadAttachment'])->name('attachment.upload');
    Route::post("/compose", [ComposeController::class, "compose"])->name("compose");

    Route::post("/compose-inline", [ComposeController::class, "compose_inline"])->name("composeInline");
    //Read Mail
    Route::post("/read-mail", [MailReadController::class, "details"]);
    Route::get("/get-body/{id}", [MailReadController::class, "GetBody"]);

    Route::post("/load-more-mail", [MailReadController::class, "loadMoreMail"]);
    Route::get("/read-mail/{id}", [MailReadController::class, "read"]);


    //Common Routes for Multiple Action And item per page
    /**
     * Bulk group Action Routes
     */
    Route::prefix("bulk")->controller(BulkActionController::class)->group(function () {
        Route::post("/delete", "delete")->name("bulkDelete");
        //ActionController::
        Route::post("/active", "active")->name("bulkActive");
        Route::post("/inactive", "inactive")->name("bulkInactive");
        /**Move Contact to another List or another status */
    });
    Route::get("/item-per-page/{number}", [BulkActionController::class, "itemPerPage"])->name("itemperpage");


    Route::prefix("settings")->group(function () {

        //Settings Route

        Route::prefix("/user")
            ->middleware(PermissionAccess::class . ":settings,user,view")
            ->controller(UserController::class)->group(function () {
                Route::get("/", "index")->name("user");
                Route::get("/list-data", "listData")->name("listData");
                Route::get("/new", "create")->name("userCreate");
                Route::post("/new", "store")->name("userstore");
                Route::get("/update/{id}", "create")->name("UpdateUser"); //Update user form
                Route::post("/update/{id}", "update")->name("userUpdate");
                Route::get("/update/{id}", "create")->name("UpdateUser"); //Update user form
                Route::get("/delete/{id}", "destroy")->name("UsserDelete");
            }); // $this->middleware('access:settings,user,view');



        Route::prefix("/view-filter")
            ->middleware(PermissionAccess::class . ":settings,view-filter,view")
            ->controller(ViewFilter::class)->group(function () {
                Route::get("/", "index")->name("viewFilter");
                Route::get("/list-data", "listData")->name("viewFilterData");
                Route::get("/new", "create")->name("ViewFilterCreate");
                Route::post("/new", "store")->name("ViewFilterStore");
                Route::get("/update/{id}",  "create")->name("ViewFilterUpdateView");
                Route::post("/update/{id}",  "update")->name("ViewFilterUpdate");
                Route::post("/delete/{id}", "destroy")->name("ViewFilterDelete");
            });

        //Customer Route Start
        Route::prefix("/customer")
            ->middleware(PermissionAccess::class . ":settings,customer,view")
            ->controller(Customer::class)->group(function () {
                Route::get("/", "index")->name("customer");
                Route::get("/list-data", "listData")->name("CustomeristData");
                Route::get("/new", "create")->name("customerCreate");
                Route::post("/new", "store")->name("customerStore");
                Route::get("/update/{id}", "create")->name("customerUpdateView");
                Route::post("/update/{id}", "update")->name("customerUpdate");
                Route::post("/delete/{id}", "destroy")->name("CustomerDelete");
            }); //->middleware("access:settings,customer,view");

        //Department Route Start
        Route::prefix("/department")
            ->middleware(PermissionAccess::class . ":settings,department,view")
            ->controller(Depertment::class)->group(function () {
                Route::get("/", "index")->name("department");
                Route::get("/list-data",  "listData")->name("departmentListData");
                Route::get("/new",  "create")->name("departmentCreate");
                Route::post("/new",  "store")->name("depertmentStore");
                Route::get("/update/{id}",  "create")->name("departmentUpdateView"); //Update Department form
                Route::post("/update/{id}",  "update")->name("departmentUpdateData");
                Route::get("/oauth/{id}",  "OAuthVal")->name("OAuthVal");
                Route::post("/delete/{id}",  "destroy")->name("DepartmentDelete");
            }); //End Department Route


        //User Permission Route
        Route::prefix("/permission")
            ->middleware(PermissionAccess::class . ":settings,permission")
            ->group(function () {
                Route::get("/", [PermissionController::class, "index"])->name("permissionView");
                Route::view("/create-role", "SettingModules.permission.create-role")->name("role-create-view");
                Route::post("/create-role", [PermissionController::class, "createRole"])->name("createRoleStore");
                Route::post("/role/delete/{id}", [PermissionController::class, "deleteRole"])->name("deleteRole");
                Route::post("/put", [PermissionController::class, "PutPermission"])->name("permissionPut");
                Route::post("/get", [PermissionController::class, "getPermission"]);
            });

        Route::prefix("/general")
            ->middleware(PermissionAccess::class . ":settings,general,view")
            ->group(function () {
                Route::get("/", [GeneralSettings::class, "index"])->name("generalSettings");
                Route::post("/", [GeneralSettings::class, "store"])->name("generalSettingsStore");
            });
        Route::prefix("/ai")
            ->middleware(PermissionAccess::class . ":settings,ai,view")
            ->group(function () {
                Route::get("/", [GeneralSettings::class, "aiSettings"])->name("aiSettings");
            });
    });


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Attachment

    Route::get('attachments/{filename}', [AttachmentController::class, 'showAttachment']);
    Route::get('inline-attachments/{filename}', [AttachmentController::class, 'showInlineAttachment']);
});
//Extra Routes
Route::view("/access-denied", "errors.access-denied")->name("access-denied");
//Test routes
#Route::get("/mail", [GmailController::class, "login"]);
#Route::get("/home", [App\Http\Controllers\HomeController::class, "index"]);




Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', PermissionAccess::class])->name('dashboard');

require __DIR__ . '/auth.php';



//Testing Routes 
//Test routes for event listeners
Route::prefix("/abc")->controller(UserController::class)->group(function () {
    Route::get("/test", function () {
        echo "Testing Middleware";
    });
})->middleware(PermissionAccess::class . ":settings,abc,test");

Route::get("/att-job", function () {
    $id = 22;
    $list = MailList::find($id);
    if (count($list->getAttachments()->attachments) > 0 || count($list->getAttachments()->inlineAttachments) > 0) {
        ProcessAttachment::dispatch($list->id); //Attachment Process Job Request with Queued
    }
});


Route::get('/rev', function () {
    return view('reverb');
});

Route::get('/broadcast', function () {
    //broadcast(new \App\Events\TestEvent('Hello World'));
    broadcast(new \App\Events\MailArrived(MailList::find(2)));
    return 'Event triggered!';
});
