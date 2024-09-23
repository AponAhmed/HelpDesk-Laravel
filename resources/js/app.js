import './bootstrap';

import Alpine from 'alpinejs';

import $ from "jquery";
import BalloonEditor from "@aponahmed/ckeditor5-ballon";// "@ckeditor/ckeditor5-build-balloon";
//import { mailview, dragElement } from "./mailView";//pagination//popup
import { popup, pagination, Toggler, DataBuilder, ConfirmBox, Notification, DialogBox, Tab, tooltip } from "./elements";
import fileUploader from "./fileUpload";

import MailList from "./MailList";
import Resizer from "./Resizer";
import AiWindow from "./AiWindow";
import SysNotify from './SysNotify';


window.Alpine = Alpine;

Alpine.start();


// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
// // Initialize Echo
// window.Pusher = Pusher;

window.$ = $;
window.popup = popup;
window.AiWindow = AiWindow;
window.pagination = pagination;
window.DataBuilder = DataBuilder;
window.ConfirmBox = ConfirmBox;
window.DialogBox = DialogBox;
window.Tab = Tab;
window.FileUploader = fileUploader;
window.Toggler = Toggler;
window.MailList = MailList;

// Or using the CommonJS version:
if ($("#editor").length > 0) {
    BalloonEditor.create(document.querySelector("#editor"), {
        dataAllowedContent: 'style[type]',
        allowedContent: ['p', 'style'],
        htmlSupport: {
            allow: ['style'],
            disallow: [ /* HTML features to disallow */]
        }
    })
        .then((editor) => {
            window.editor = editor;
        })
        .catch((error) => {
            console.error("There was a problem initializing the editor.", error);
        });
}

// // // Create `axios-cache-adapter` instance
// const cache = setupCache({
//     maxAge: 15 * 60 * 1000,
// });

// // // Create `axios` instance passing the newly created `cache.adapter`
// const api = axios.create({
//     adapter: cache.adapter,
// });

//global scop
//window.Notiflix = Notiflix;
window.popup = popup;
window.tooltip = tooltip;

new popup();
window.pagination = pagination;

new tooltip({
    selector: '.tooltip'
});



window.dropdowninit = function () {
    $(".dropdown .dropdown-tolggler").off("click");
    $(".dropdown .dropdown-tolggler").on("click", function () {
        let el = $(this).parent();
        let el2 = $(".dropdown.open");
        $(".dropdown.open").removeClass("open");
        if (el[0] != el2[0]) {
            $(this).parent().toggleClass("open");
        }
    });
};
window.addEventListener("click", (e) => {
    let target = $(e.target);
    let d = target.closest('.dropdown');
    if (d.length == 0) {
        $(".dropdown.open").removeClass("open");
    }

});


window.customSelect = function (e, _this) {
    $(_this).closest(".customSelect").find(".optionItems").slideToggle("fast");
};

window.getPermission = function (e, _this) {
    e.preventDefault();
    //Notiflix.Loading.pulse();
    $(".toggle").prop("checked", false);
    $(_this).closest(".optionItems").slideToggle();
    $(_this)
        .closest(".customSelect")
        .find(".customSelectTog")
        .html($(_this).html());
    $(".custom-select-option").removeClass("selected");
    $(_this).addClass("selected");
    let id = $(_this).attr("data-id");
    let ModelType = $(_this).attr("data-type");
    //console.log(id, ModelType);
    axios
        .post("permission/get", { id: id, model: ModelType })
        .then((response) => {
            let permissionData = response.data;
            if (permissionData.permission) {
                let permissionObj = JSON.parse(permissionData.permission);
                $.each(permissionObj.permission, function (i, data) {
                    if (data.constructor === {}.constructor) {
                        $.each(data, function (k, v) {
                            if (v.constructor === {}.constructor) {
                                //console.log(v);
                                $.each(v, function (j, sett) {
                                    let idS = i + "_" + k + "_" + j;
                                    $("#" + idS).prop("checked", true);
                                });
                            } else {
                                let id = i + "_" + k;
                                $("#" + id).prop("checked", true);
                                //console.log(id);
                            }
                        });
                    } else {
                        //if not object
                        //console.log(i);
                    }
                });
            }
            //Notiflix.Loading.remove();
        })
        .catch((error) => {
            console.log(error);
        });
};
//Ready Event execution
$(function () {
    dropdowninit();
    $(".sidebarCollapseTrig").on("click", function () {
        $(".sidebar").toggleClass("clps");
        //Set Cookies with Sidebar Toggle
        if ($(".clps").length > 0) {
            localStorage.setItem("sidebarCollapse", true);
            //setCookie("sidebarCollapse", true, 1);
        } else {
            localStorage.setItem("sidebarCollapse", false);
            //setCookie("sidebarCollapse", false, 1);
        }
    });
});

//Details Mail call
window.openDetails = function (id, obj) {
    $(".mail-list-item.current").removeClass("current");
    $(".mail-list-item.loading").removeClass("loading");
    $(obj).addClass("loading");
    $("body").addClass("detailsOpend");
    //Notiflix.Loading.pulse();
    //console.log(id);

    // Send a GET request to some REST api
    // api({
    //     url: "/mailDetails/" + id,
    //     method: "get",
    // })
    axios
        .get("/mailDetails/" + id)
        .then(async (response) => {
            // Notiflix.Loading.remove();
            let mlView = new mailview(response);
            mlView.append2();
            dropdowninit();
            $(obj).addClass("current").removeClass("loading");
            //const length = await cache.store.length();
            //console.log("Cache store length:", length);
        });
};
//Close Details
window.closeDetails = function () {
    $("body").removeClass("detailsOpend");
};

window.mailDetailsEvent = function () {
    if ($(".mail-list-item").length > 0) {
        $(".mail-list-item")
            .find(".evnt")
            .on("click", function () {
                let listObj = $(this).closest(".mail-list-item");
                let id = listObj.attr("data-id");
                openDetails(id, listObj);
            });
    }
};

window.ntf = function (txt, cls) {
    new Notification({
        message: txt,
        type: cls
    });
};


// function deleteData(event, _this) {
//     event.preventDefault();
//     Notiflix.Confirm.show(
//         "Delete Confirm",
//         "Are you sure to Delete ?",
//         "Yes",
//         "No",
//         function () {
//             let deleteRoute = $(_this).attr("href");
//             let csrf = $(_this).attr("data-csrf");
//             axios
//                 .post(deleteRoute + "?ajx", { _token: csrf })
//                 .then((response) => {
//                     response = response.data;
//                     if (response.error === false) {
//                         ntf(response.msg, "success");
//                         LoadData();
//                     } else {
//                         ntf(response.msg, "error");
//                     }
//                 })
//                 .catch((error) => {
//                     ntf(error, "error"); //error.response.headers);
//                 });
//         }
//     );
// }


window.deleteData = function (e, _this) {
    e.preventDefault();
    let Conf = new ConfirmBox({
        title: "Delete Confirmations",
        message: "Are you sure to Delete ?",
        yesCallback: function () {
            let route = $(_this).attr("href");
            axios
                .get(route)
                .then((response) => {
                    response = response.data;
                    if (response.error === false) {
                        ntf(response.msg, "success");
                        //LoadData();
                        $(_this).closest('tr').addClass('removed');
                        setTimeout(() => {
                            $(_this).closest('tr').remove();
                        }, 150);
                    } else {
                        ntf(response.msg, "error");
                    }
                })
                .catch((error) => {
                    ntf(error, "error");
                });
        }
    });
}


function postData(route, data, calback) {
    axios
        .post(route, data)
        .then((response) => {
            if (response.data == "1") {
                ntf("Data Stored Succeed", "success");
                //return true;
                calback(response);
            } else {
                ntf(response.data, "error");
            }
        })
        .catch((error) => {
            ntf(error, "error"); //error.response.headers);
        });
}
window.postData = postData;

//Department Auth Login
function AuthLogin(id, exp) {
    axios
        .get("/settings/department/oauth/" + id)
        .then((res) => {
            let responsData = res.data;
            if (responsData.authUrl != "") {
                window.location.href = responsData.authUrl;
                //console.log(responsData.authUrl);
            }
        })
        .catch((error) => {
            //console.log(error);
            ntf("Error: " + error.response.status, "error"); //error.response.headers);
        });
}
window.AuthLogin = AuthLogin;

window.setCookie = function (cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
};
window.getCookie = function (cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(";");
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == " ") {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};


/**Updated New Way */
window.typing = 0;

window.searchData = function (e) {
    let closeBtn = document.querySelector('.searchCancel');
    let searchBox = e.target;
    clearTimeout(typing);
    window.typing = setTimeout(() => {
        if (searchBox.value != "") {
            closeBtn.style.display = "block";
            LoadData(false, "?q=" + searchBox.value);
        } else {
            closeBtn.style.display = "none";
            LoadData(false, "");
        }
    }, 300);

}

window.searchCancel = function (e) {
    e.target.style.display = "none";
    let searchBox = document.querySelector('.searchInput');
    searchBox.value = "";
    LoadData(false, "");
}

window.inProcess = function () {
    let loader = document.querySelector(".loader");
    loader.classList.add("loading");
    loader = document.querySelector(".page-loader");
    loader.classList.add("loading");
}
window.completaProcess = function () {
    let loader = document.querySelector(".loader");
    loader.classList.remove("loading");
    loader = document.querySelector(".page-loader");
    loader.classList.remove("loading");
    dropdowninit();
}

window.sysnData = function () {
    let route = window.DataPath;
    LoadData(route);
}

window.filterData = function (e, col, val) {
    let filter = "";
    if (val !== "") {
        filter = "?filter=" + col + "&val=" + val;
    }
    //$(e.target).closest(".data-filter-dropdown").find(".currentFilter").html("sdfdsf");
    LoadData(false, filter);
}


let resizableMainWrap = document.querySelector('.main-wrap');//horizontal

if (resizableMainWrap) {
    new Resizer(resizableMainWrap, {
        type: 'horizontal',
        callback: (dimension) => {
            localStorage.setItem("mainwraper-width", dimension.width);
        }
    });
    let mainWrapWidth = localStorage.getItem("mainwraper-width"); // getCookie("sidebarCollapse");

    if (mainWrapWidth) {
        resizableMainWrap.style.width = mainWrapWidth + "px";
    }
}

// document.addEventListener("DOMContentLoaded", function () {
//     const notification = new SysNotify({
//         title: 'Permission Granted',
//         body: 'Thanks for your permission granted',
//         icon: 'path/to/icon.png',
//         link: '',
//         requireInteraction: false,
//         silent: true
//     });
//     // Show the notification
//     notification.showNotification();
// });


//Ai  Settings 

document.addEventListener('DOMContentLoaded', function () {
    //Ai Settings 
    let aiPorivider = document.getElementById("aiProvider");
    if (aiPorivider) {
        aiSettingsFieldManage(aiPorivider.value);
        aiPorivider.addEventListener("change", function (e) {
            aiSettingsFieldManage(aiPorivider.value);
        });
    }
});

function aiSettingsFieldManage(prov) {
    // Get all elements with class "freebox-settings"
    var freeboxSettings = document.querySelectorAll('.freebox-settings');
    var openAiSettings = document.querySelectorAll('.openai-settings');

    // Get all elements with class "gemini-settings"
    var geminiSettings = document.querySelectorAll('.gemini-settings');

    switch (prov) {
        case "freebox":
            // Loop through all elements with class "gemini-settings" and add class "hidden"
            geminiSettings.forEach(function (element) {
                element.classList.add('hidden');
            });

            openAiSettings.forEach(function (element) {
                element.classList.add('hidden');
            });

            // Loop through all elements with class "freebox-settings" and remove class "hidden"
            freeboxSettings.forEach(function (element) {
                element.classList.remove('hidden');
            });
            break;
        case "gemini":
            // Loop through all elements with class "freebox-settings" and add class "hidden"
            freeboxSettings.forEach(function (element) {
                element.classList.add('hidden');
            });
            openAiSettings.forEach(function (element) {
                element.classList.add('hidden');
            });
            // Loop through all elements with class "gemini-settings" and remove class "hidden"
            geminiSettings.forEach(function (element) {
                element.classList.remove('hidden');
            });
            break;
        case "openai":
            // Loop through all elements with class "freebox-settings" and add class "hidden"
            freeboxSettings.forEach(function (element) {
                element.classList.add('hidden');
            });
            // Loop through all elements with class "gemini-settings" and remove class "hidden"
            geminiSettings.forEach(function (element) {
                element.classList.add('hidden');
            });
            openAiSettings.forEach(function (element) {
                element.classList.remove('hidden');
            });
            break;
    }
}
