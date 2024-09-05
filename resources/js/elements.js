import axios from "axios";
import { Dombuilder as el } from "@aponahmed/dombuilder";
//POPUP
class popup {
    constructor(ApiResponse, r) {
        this.selectorClass = "popup";
        this.appendSelector = "body";
        $("." + this.selectorClass).off("click");
        this.dom = null;
        this.domExistingHtml = null;

        this.init();
    }
    init() {
        let popUpTog = document.querySelectorAll("." + this.selectorClass);
        var _this = this;
        popUpTog.forEach(function (el) {
            $(el).on("click", function (e) {
                _this.dom = $(this);
                _this.domExistingHtml = _this.dom.html();
                _this.dom.html("<span class='working'></span>");//<span class='data-loading'></span>
                e.preventDefault();
                let url = el.getAttribute("href");
                let w = el.getAttribute("data-w");
                let cls = el.getAttribute("data-class");
                if (!cls) {
                    cls = "";
                }
                let ccs = "";
                if (w) {
                    ccs = "width:" + w + "px";
                }
                axios
                    .get(url + "?ajx")
                    .then(function (response) {
                        // handle success
                        _this.dom.html(_this.domExistingHtml);
                        var uID = Date.now();
                        $(_this.appendSelector).append(
                            "<div class='popup-wrap " +
                            uID +
                            "'><div class='popup-body " + cls + "' style='" +
                            ccs +
                            "'><span class='closePopup'></span><div class='popup-inner'>" +
                            response.data +
                            "</div></div></div>"
                        );
                        let popUpForm = $("." + uID).find("form.ajx");
                        $(popUpForm).on("submit", (e) => {
                            let btn = e.target.querySelector('button[type="submit"]');
                            let exHtml = btn.innerHTML;
                            btn.innerHTML = "<span class='working'></span>";

                            //Form Submit by Ajax
                            e.preventDefault();
                            let submitRoute = popUpForm.attr("action");
                            postData(submitRoute, $(popUpForm).serialize(), function (res) {
                                LoadData();
                                $("." + uID).remove();
                                btn.innerHTML = exHtml;
                            }, function (res) {
                                btn.innerHTML = exHtml;
                            }); //Post Data to server
                        });
                        $(".closePopup").on("click", function () {
                            $(this).closest(".popup-wrap").remove();
                        });

                    })
                    .catch(function (error) {
                        console.log(error);
                        ntf(error, "error"); //error.response.headers);
                    });
            });
        });
    }
}



export class tooltip {
    constructor({ ...args }) {
        this.selector = args.selector || '.tooltip';
        this.position = args.position || 'right';
        this.bg = args.bg || "#1a1f30";
        this.color = args.color || "#fff";
        this.init();
        this.exrect = {};
        this.extraMargin = 10;
    }

    init() {
        this.tooltip = new el('div').class('tooltip-container');
        this.tooltipTitle = new el('div').class('tooltip-title');
        this.tooltipArrow = new el('span').class('tooltip-arrow');
        this.tooltip.append(this.tooltipArrow.element);
        this.tooltip.append(this.tooltipTitle.element);

        this.items = document.querySelectorAll(this.selector);
        this.items.forEach((item) => {
            this.item = item;
            this.item.addEventListener('mouseover', (e) => {
                let citem = item;
                this.showTooltip(citem, e);
            });
            this.item.addEventListener('mouseout', () => {
                this.removeTooltip();
            });
        })
    }

    removeTooltip() {
        let tooltip=document.querySelector('.tooltip-container');
        if(tooltip){
            tooltip.remove();
        }
    }

    positionSet(item) {
        if (item.hasAttribute('data-position')) {
            // data attribute doesn't exist
            this.position = item.dataset.position;
        } else {
            this.position = 'right';
        }

        this.tooltipArrow.element.removeAttribute('class');
        this.tooltipArrow.element.removeAttribute('style');
        this.tooltipArrow.class('tooltip-arrow');


        if (this.position === 'right') {
            this.tooltipArrow.element.style.borderRightColor = this.bg;
            this.tooltip.element.style.left = this.exrect.left + this.exrect.width + this.extraMargin + 'px';
            this.tooltipArrow.class('arrow-left');
            let cntrT = (this.exrect.height - this.tooltipRect.height) / 2;
            this.tooltip.element.style.top = this.exrect.top + cntrT + 'px';
        } else if (this.position === 'left') {
            this.tooltipArrow.element.style.borderLeftColor = this.bg;
            this.tooltip.element.style.left = this.exrect.left - (this.tooltipRect.width + this.extraMargin) + 'px';
            this.tooltipArrow.class('arrow-right');
            let cntrT = (this.exrect.height - this.tooltipRect.height) / 2;
            this.tooltip.element.style.top = this.exrect.top + cntrT + 'px';

        } else if (this.position === 'top') {
            this.tooltipArrow.element.style.borderTopColor = this.bg;
            this.tooltipArrow.class('arrow-bottom');
            let top = this.exrect.top - (this.exrect.height + this.extraMargin);
            let left = this.exrect.left + ((this.exrect.width - this.tooltipRect.width) / 2);
            this.tooltip.element.style.top = top + 'px';
            this.tooltip.element.style.left = left + 'px';
        } else {
            this.tooltipArrow.element.style.borderBottomColor = this.bg;
            this.tooltipArrow.class('arrow-top');
            let top = this.exrect.top + (this.exrect.height + this.extraMargin);
            let left = this.exrect.left + ((this.exrect.width - this.tooltipRect.width) / 2);
            this.tooltip.element.style.top = top + 'px';
            this.tooltip.element.style.left = left + 'px';
        }
    }

    colorSet(item) {
        if (item.hasAttribute('data-bg')) {
            this.bg = item.dataset.bg;
        } else {
            this.bg = '#1a1f30';
        }
        this.tooltip.element.removeAttribute('style');

        this.tooltip.element.style.backgroundColor = this.bg;
    }

    showTooltip(item, e) {
        this.tooltipTitle.html(item.title);
        this.exrect = item.getBoundingClientRect();
        document.querySelector('body').appendChild(this.tooltip.element);
        this.colorSet(item);
        this.tooltipRect = this.tooltip.element.getBoundingClientRect();
        this.positionSet(item);
    }
}

//Pagination Builder
class pagination {
    constructor(ApiResponse, r, objDataTable) {
        this.current = ApiResponse.current_page;
        this.links = ApiResponse.links;
        this.DomRoot = r;
        this.data = ApiResponse.data;
        this.apiRes = ApiResponse;
    }

    linksHtm() {
        var htm = "";
        var _this = this;

        if (_this.data && _this.data.length > 0) {
            if (_this.current > 1) {
                htm += `<a href="javascript:void(0)" class='page-link-first page-link-item' onclick="LoadData('${_this.apiRes.first_page_url}')"><svg xmlns="http://www.w3.org/2000/svg" class="tooltip" title="First Page" viewBox="0 0 512 512"><title>First Page</title><path d="M400 111v290c0 17.44-17 28.52-31 20.16L121.09 272.79c-12.12-7.25-12.12-26.33 0-33.58L369 90.84c14-8.36 31 2.72 31 20.16z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M112 80v352"/></svg></a>`;
            }
            let Links = _this.links;
            Links.forEach((el, index) => {
                if (el.url == null) {
                    Links.splice(index, 1);
                }
            });
            if (Links.length < 2) {
                return "";
            }
            Links.forEach(function (el) {
                //if (el.url !== null) {
                let act = "";
                if (el.active) {
                    act = "active";
                }
                let label = el.label;
                let cls = 'link-item';
                //Filter Label
                if (label == '&laquo; Previous') {
                    label = '<svg xmlns="http://www.w3.org/2000/svg" class="tooltip" title="Previous Page" viewBox="0 0 512 512"><title>Previous Page</title><path d="M480 145.52v221c0 13.28-13 21.72-23.63 15.35L267.5 268.8c-9.24-5.53-9.24-20.07 0-25.6l188.87-113C467 123.8 480 132.24 480 145.52zM251.43 145.52v221c0 13.28-13 21.72-23.63 15.35L38.93 268.8c-9.24-5.53-9.24-20.07 0-25.6l188.87-113c10.64-6.4 23.63 2.04 23.63 15.32z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>';
                    cls = '';
                } else if (label == 'Next &raquo;') {
                    label = '<svg xmlns="http://www.w3.org/2000/svg" class="tooltip" title="Next Page" viewBox="0 0 512 512"><title>Next Page</title><path d="M32 145.52v221c0 13.28 13 21.72 23.63 15.35l188.87-113c9.24-5.53 9.24-20.07 0-25.6l-188.87-113C45 123.8 32 132.24 32 145.52zM260.57 145.52v221c0 13.28 13 21.72 23.63 15.35l188.87-113c9.24-5.53 9.24-20.07 0-25.6l-188.87-113c-10.64-6.47-23.63 1.97-23.63 15.25z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>';
                    cls = '';
                }

                htm += `<a href="javascript:void(0)" class="${act} ${cls} page-link-item" onclick="LoadData('${el.url}')">${label}</a>`;
                //}
            });
            if (_this.current < _this.apiRes.last_page) {
                htm += `<a href="javascript:void(0)" class='page-link-last  page-link-item' onclick="LoadData('${_this.apiRes.last_page_url}')"><svg xmlns="http://www.w3.org/2000/svg" class="tooltip" title="Last Page" viewBox="0 0 512 512"><title>Last Page</title><path d="M112 111v290c0 17.44 17 28.52 31 20.16l247.9-148.37c12.12-7.25 12.12-26.33 0-33.58L143 90.84c-14-8.36-31 2.72-31 20.16z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M400 80v352"/></svg></a>`;
            }
        }
        return htm;
    }
}
//Pagination Builder End
//Custom Toggler
class Toggler {
    constructor(dom, { ...options }) {
        this.dom = $(dom);
        this.target = $(this.dom.attr("data-target"));
        this.init();
        this.defaultString = options.defaultString || this.dom.html();
        this.openString = options.openString || 'Less Option';
        return this;
    }
    init() {
        this.target.hide();
        this.dom.on("click", (e) => {
            e.preventDefault();
            if (this.dom.html() == this.defaultString) {
                this.dom.html(this.openString);
            } else {
                this.dom.html(this.defaultString);
            }
            this.target.slideToggle(100);
        });
    }
}

class Notification {
    constructor({ ...options }) {
        this.type = options.type || "success";
        this.message = options.message || "";
        this.timeout = options.timeout || 6000;
        //if not success then error and timeout should increse
        if (this.type !== "success") {
            this.timeout = this.timeout * 2;
        }
        this.bind();
    }
    build() {
        //element
        let singleElement = document.createElement("div");
        singleElement.classList.add("notification");
        singleElement.classList.add(this.type);
        if (this.type == 'alert' || this.type == 'warning') {
            singleElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="notification-icon" viewBox="0 0 512 512"><title>Warning</title><path d="M85.57 446.25h340.86a32 32 0 0028.17-47.17L284.18 82.58c-12.09-22.44-44.27-22.44-56.36 0L57.4 399.08a32 32 0 0028.17 47.17z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><path d="M250.26 195.39l5.74 122 5.73-121.95a5.74 5.74 0 00-5.79-6h0a5.74 5.74 0 00-5.68 5.95z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><path d="M256 397.25a20 20 0 1120-20 20 20 0 01-20 20z"/></svg>';
        } else if(this.type == 'info'){
            singleElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="notification-icon" viewBox="0 0 512 512"><path d="M248 64C146.39 64 64 146.39 64 248s82.39 184 184 184 184-82.39 184-184S349.61 64 248 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M220 220h32v116"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M208 340h88"/><path d="M248 130a26 26 0 1026 26 26 26 0 00-26-26z"/></svg>';
        }else if (this.type == 'success') {
            singleElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="notification-icon" viewBox="0 0 512 512"><title>Checkmark</title><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96"/></svg>';
        } else {
            singleElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="notification-icon" viewBox="0 0 512 512"><title>Close Circle</title><path d="M448 256c0-106-86-192-192-192S64 150 64 256s86 192 192 192 192-86 192-192z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M320 320L192 192M192 320l128-128"/></svg>';
        }
        //message
        let messageElement = document.createElement("div");
        messageElement.classList.add("message");
        messageElement.innerHTML = this.message;
        singleElement.appendChild(messageElement);
        //close
        let closeElement = document.createElement("div");
        closeElement.classList.add("close");
        closeElement.innerHTML = "&times;";
        closeElement.addEventListener("click", () => {
            singleElement.remove();
        });
        singleElement.appendChild(closeElement);
        singleElement.classList.add('slide-righr');
        return singleElement;
    }

    bind() {
        //check existance of notifications wraper
        let notificationsWrapper = document.querySelector(".notifications");
        if (!notificationsWrapper) {
            notificationsWrapper = document.createElement("div");
            notificationsWrapper.classList.add("notifications");
            document.body.appendChild(notificationsWrapper);
        }
        //append notification
        notificationsWrapper.appendChild(this.build());
        //remove notification after timeout
        setTimeout(() => {
            if (notificationsWrapper.firstChild) {
                notificationsWrapper.removeChild(notificationsWrapper.firstChild);
            }
        }, this.timeout);
    }

}

//Custom Dialog Popup
class DialogBox {
    constructor({ ...options }) {
        this.title = options.title || "Title Here";
        this.body = options.body || "Dialog Body Here";
        this.position = options.position || "center";
        this.actions = options.actions || [
            {
                label: "Ok",
                class: "btn-primary",
                callback: function (_this) {
                    _this.close();
                }
            }
        ];
        this.bind();
        return this;
    }
    build() {
        //build element
        let dialogBox = document.createElement("div");
        dialogBox.classList.add("dialog-box");
        //build header
        let header = document.createElement("div");
        header.classList.add("header");
        //Title wrap
        let titleWrap = document.createElement("div");
        titleWrap.classList.add("title-wrap");
        titleWrap.innerHTML = this.title;
        header.appendChild(titleWrap);
        //close button
        let closeButton = document.createElement("div");
        closeButton.classList.add("close-button");
        closeButton.innerHTML = "&times;";
        closeButton.addEventListener("click", () => {
            dialogBox.remove();
        });
        header.appendChild(closeButton);
        dialogBox.appendChild(header);
        //build body
        let body = document.createElement("div");
        body.classList.add("body");
        body.innerHTML = this.body;
        dialogBox.appendChild(body);
        //build actions
        if (this.actions.length > 0) {
            let actions = document.createElement("div");
            actions.classList.add("actions");
            this.actions.forEach((el) => {
                let action = document.createElement("div");
                action.classList.add("action");
                action.classList.add(el.className);
                action.innerHTML = el.label;
                action.addEventListener("click", () => {
                    el.callback(this);
                });
                actions.appendChild(action);
            });
            dialogBox.appendChild(actions);
        }
        this.dialogBox = dialogBox;
        return dialogBox;
    }
    bind() {
        //append dialog
        document.body.appendChild(this.build());
        //position dialog
        if (this.position == "center") {
            this.dialogBox.style.top = "50%";
            this.dialogBox.style.left = "50%";
            this.dialogBox.style.transform = "translate(-50%, -50%)";
        } else {
            //check position object or not
            //console.log(this.dialogBox.clientHeight);
            if (typeof this.position == "object") {
                this.dialogBox.style.top = (this.position.top - this.dialogBox.clientHeight) + "px";
                this.dialogBox.style.left = this.position.left + "px";
            }
        }

    }

    close() {
        this.dialogBox.remove();
    }
}

//Custom Confirm Class
/**
 * @param Object {title,Message,yes,no,yesCallback,noCallback}
 */
class ConfirmBox {
    constructor({ ...option }) {
        this.param = option.param || {};
        this.title = option.title || "Confirm";
        this.message = option.message || "Are you sure?";
        this.yes = option.yes || "Yes";
        this.no = option.no || "No";
        this.yesCallback = option.yesCallback || function () { };
        this.noCallback = option.noCallback || function () { };
        this.confirm();
    }

    confirm() {
        this.Ui();
        this.eventHandler();
    }

    Ui() {
        //Create Element
        let modal = document.createElement("div");
        modal.classList.add("confirm-modal");

        let modalBody = document.createElement("div");
        modalBody.classList.add("confirm-modal-body");

        let modalHeader = document.createElement("div");
        modalHeader.classList.add("confirm-modal-header");

        let modalTitle = document.createElement("div");
        modalTitle.classList.add("confirm-modal-title");
        modalTitle.innerHTML = this.title;

        let modalMessage = document.createElement("div");
        modalMessage.classList.add("confirm-modal-message");
        modalMessage.innerHTML = this.message;

        let modalFooter = document.createElement("div");
        modalFooter.classList.add("confirm-modal-footer");

        let modalYes = document.createElement("div");
        modalYes.classList.add("confirm-modal-yes");
        modalYes.innerHTML = this.yes;

        let modalNo = document.createElement("div");
        modalNo.classList.add("confirm-modal-no");
        modalNo.innerHTML = this.no;

        let modalClose = document.createElement("div");
        modalClose.classList.add("confirm-modal-close");
        modalClose.innerHTML = "&times;";
        //Append Element to Modal
        modal.appendChild(modalBody);
        modalBody.appendChild(modalHeader);
        modalHeader.appendChild(modalTitle);
        modalHeader.appendChild(modalClose);

        modalBody.appendChild(modalMessage);
        modalBody.appendChild(modalFooter);
        modalFooter.appendChild(modalYes);
        modalFooter.appendChild(modalNo);
        //Append Modal to Body
        document.body.appendChild(modal);
        //Append Event Listener to Close Button
        this.modalClose = modalClose;
        this.modalYes = modalYes;
        this.modalNo = modalNo;
        this.modal = modal;
    }

    //Event And Callback Handler
    eventHandler() {
        this.modalClose.addEventListener("click", () => {
            this.modal.remove();
        });
        //Append Event Listener to Yes Button
        this.modalYes.addEventListener("click", () => {
            this.yesCallback(this.param);
            this.modal.remove();
        });
        //Append Event Listener to No Button
        this.modalNo.addEventListener("click", () => {
            this.noCallback(this.param);
            this.modal.remove();
        });
    }
}
/**
 * @param DOM of Tab Wraper
 * -Structure
 * div.tab-wrap
 * ->ul > li[data-id=id]
 * ->div.tab-contents-wrap
 *   ->div#id.tab-pan
 */
class Tab {
    constructor(dom) {
        this.dom = dom;
        this.init();
        this.target = false;//Target ID
    }
    init() {
        this.lis = this.dom.querySelectorAll('li');
        this.pans = this.dom.querySelectorAll('.tab-pan');
        this.lis.forEach((node) => {
            node.addEventListener('click', () => {
                this.removeActive();
                this.target = node.getAttribute('data-id');
                this.target = this.dom.querySelector("#" + this.target);
                node.classList.add('active');
                this.target.classList.add('active');
            });
        });

    }
    removeActive() {
        this.lis.forEach((node) => {
            node.classList.remove('active');
        });
        this.pans.forEach((node) => {
            node.classList.remove('active');
        });
    }
}
//Custom Confirm Class End

//Classic Data Loader
//Route For Data
//columns
//Root Route
//Edit Route
//Delete Route
//Notification Dependency
//aagination Dependency
//axios dependency
let refIntval;
class DataBuilder {
    constructor(dom, { ...options }) {
        this.dom = dom;
        this.apiRoute = options.apiRoute;
        this.columns = options.columns;
        this.actions = options.actions || {};
        this.startProcess = options.startProcess || function () { };
        this.endProcess = options.endProcess || function () { };
        this.rowClassFilter = options.rowClassFilter || function () { return "data-row-item" };
        this.numberOfColumn = Object.getOwnPropertyNames(this.columns).length;
        //multipleActions
        this.currentPage = options.currentPage || 1;
        this.model = options.model;
        this.isBulkAction = options.isBulkAction === false ? false : true;
        //Addition For Bulk Action's Checkbox Column
        if (this.isBulkAction) {
            this.numberOfColumn++;
        }
        //Addition For Action Column
        if (Object.getOwnPropertyNames(this.actions).length > 0) {
            this.numberOfColumn++;
        }

        this.autoRefress = options.autoRefress || 5;//accept int as seccond

        this.bulkActiveAction = options.bulkActiveAction == null ? true : options.bulkActiveAction;
        this.bulkActiveRoute = options.bulkActiveRoute || "active";
        this.bulkInactiveAction = options.bulkInactiveAction == null ? true : options.bulkInactiveAction;
        this.bulkInactiveRoute = options.bulkInactiveRoute || "inactive";
        this.bulkDeleteAction = options.bulkDeleteAction || true;
        this.bulkDeleteRoute = options.bulkDeleteRoute || "delete";
        //console.log(options);
        //Bulk Option By Module
        this.bulkActions = options.bulkActions || {};
        //Default Bulk Option
        this.DefaultBulkActions = {};
        //if Bulk Delete is true then add bulk delete option to default actions
        if (this.bulkDeleteAction) {
            this.DefaultBulkActions.delete = {
                label: "Delete Selected",
                confirm: true,
                route: this.bulkDeleteRoute,
                confirmCallback: (callback, _this) => {
                    let Conf = new ConfirmBox({
                        param: { _this: _this },
                        title: "Bulk Delete Confirmations",
                        message: `Are you sure to Delete Selected ${this.model} ?`,
                        yesCallback: callback,//this.bulkActions.delete.callback,
                    });
                }
            };
        }
        //if Bulk Active is true then add bulk active option to default actions
        if (this.bulkActiveAction) {
            this.DefaultBulkActions.active = {
                label: "Active",
                confirm: true,
                route: this.bulkActiveRoute,
                confirmCallback: (callback, _this) => {
                    let Conf = new ConfirmBox({
                        param: { _this: _this },
                        title: "Bulk Active Confirmations",
                        message: `Are you sure to Active Selected ${this.model} ?`,
                        yesCallback: callback,//this.bulkActions.active.callback,
                    });
                }
            }
        }
        //if Bulk Inactive is true then add bulk inactive option to default actions
        if (this.bulkInactiveAction) {
            this.DefaultBulkActions.inactive = {
                label: "Inactive",
                confirm: true,
                route: this.bulkInactiveRoute,
                confirmCallback: (callback, _this) => {
                    let Conf = new ConfirmBox({
                        param: { _this: _this },
                        title: "Bulk Inactive Confirmations",
                        message: `Are you sure to Inactive Selected ${this.model} ?`,
                        yesCallback: callback,//this.bulkActions.inactive.callback,
                    });
                }
            }
        }

        this.bulkActions = { ...this.DefaultBulkActions, ...this.bulkActions };
        this.init();
        this.refreshSet();
        this.ExistingData = false;
    }

    /**
     * Execute Of Bulk Action
     * @param {*} DataBuilder Instance
     * @returns
     */
    PostBulkAction({ ...param }) {
        let _this = param._this;
        //Get Checked Items
        let object2Post = {
            ids: _this.checkedItems,
            model: _this.model,
            action: param.action || {},
        };
        let action = _this.currentAction;
        //Post Data
        _this.bulkActBtn.innerHTML = "<span class=\"working\"></span>";
        let route = action.route;
        axios.post("/bulk/" + route, object2Post)
            .then(response => {
                //if response 200
                if (response.status === 200) {
                    response = response.data;
                    _this.notify(response.message, response.success ? "success" : "danger");
                    _this.init();//Refresh Data
                } else {
                    _this.notify(response.message, 'error');
                }
            })
            .catch(error => {
                console.log(error);
                _this.notify(error.message, 'error');
            });
        return;
    }

    /**Bulk Action Function Regular Callback*/
    bulkAction(bulkAct) {
        let bulkActCallback = this.bulkActions[bulkAct].callback;
        if (bulkActCallback) {
            //console.log(this.selecteditems);
            bulkActCallback();
        }
    }

    setChecked(item) {
        if (item.checked) {
            this.checkedItems.push(parseInt(item.value));
        } else {
            this.checkedItems.splice(this.checkedItems.indexOf(parseInt(item.value)), 1);
        }

        this.checkedItems = this.checkedItems.filter(function (value, index, self) {
            return self.indexOf(value) === index;
        });
    }

    notify(message, type, timeout = 3000) {
        new Notification({ message: message, type: type, timeout: timeout });
    }

    selectAll() {
        let selecteditems = document.querySelectorAll(".data-check");
        selecteditems.forEach(item => {
            item.checked = true;
            this.setChecked(item);
        });
    }
    unselectAll() {
        let selecteditems = document.querySelectorAll(".data-check");
        selecteditems.forEach(item => {
            item.checked = false;
            this.setChecked(item);
        });
    }

    setEvent() {
        //Set Eventsa for select all checkbox
        let allSelect = document.querySelector("#allSelect");
        let _this = this;
        allSelect.addEventListener("change", function () {
            if (this.checked) {
                _this.selectAll();
            } else {
                _this.unselectAll();
            }
        });
        //Set Event For Checkbox
        let selecteditems = document.querySelectorAll(".data-check");
        selecteditems.forEach(item => {
            item.addEventListener("change", () => {
                this.setChecked(item);
            });
        });
    }

    init() {
        this.checkedItems = [];
        this.startProcess();
        this.loadData();
    }


    finalRender() {
        let dataControllerWrap = document.createElement("div");
        dataControllerWrap.classList.add("data-controller-wrap");

        dataControllerWrap.appendChild(this.bulkActionDom());

        let paginationWrap = document.createElement("div");
        paginationWrap.classList.add("pagination-wrap");
        paginationWrap.classList.add("pagination");
        this.pagination = new pagination(this.apiResponse);
        paginationWrap.innerHTML = this.pagination.linksHtm();

        dataControllerWrap.appendChild(this.itemPerPage());

        dataControllerWrap.appendChild(paginationWrap);

        let dataWrap = document.createElement("div");
        dataWrap.classList.add("data-wrap");
        dataWrap.innerHTML = this.buildTable();

        let dataWraper = document.createElement("div");
        dataWraper.classList.add("data-wraper");
        dataWraper.appendChild(dataWrap);
        dataWraper.appendChild(dataControllerWrap);

        this.dom.innerHTML = "";
        this.dom.appendChild(dataWraper);
        this.setEvent();
    }

    itemPerPage() {
        let ippWrap = document.createElement('div');
        ippWrap.classList.add('item-per-page');

        let toggler = document.createElement('div');
        toggler.classList.add('item-per-page-toggle');

        let input = document.createElement('input');
        input.type = 'number';
        input.id = 'ippIn';
        input.setAttribute('min', 1);
        input.value = this.pagination.apiRes.per_page;
        window.ippChange = 0;
        input.addEventListener('change', () => {
            clearTimeout(window.ippChange);
            window.ippChange = setTimeout(() => {
                this.setPerPage(input.value);
            }, 300);
        });
        let arrow = document.createElement("span");
        arrow.addEventListener('click', () => {
            ipp.classList.toggle('list-open');
        });

        let lbl = document.createElement('label');
        lbl.innerHTML = 'Per Page '
        toggler.appendChild(lbl);
        toggler.appendChild(input);
        toggler.appendChild(arrow);
        ippWrap.appendChild(toggler);
        let ipp = document.createElement('ul');
        ipp.id = "ippList";
        let ippData = [22, 50, 100, 500, 1000, 5000];
        ippData.forEach((i) => {
            let li = document.createElement('li');
            li.innerHTML = i;
            li.addEventListener('click', () => {
                this.setPerPage(i);
                ipp.classList.toggle('list-open');
            });
            ipp.appendChild(li);
        });
        ippWrap.appendChild(ipp);
        return ippWrap;
    }

    setPerPage(n) {
        axios.get('/item-per-page/' + n).then((res) => {
            this.init();
        });
    }

    /**Bulk Action Dom Element Builder */
    bulkActionDom() {
        let _this = this;
        let bulkActWrapper = document.createElement("div");
        bulkActWrapper.classList.add("bulk-action-wrapper");

        if (!this.isBulkAction) {
            return bulkActWrapper;
        }
        let bulkAct = document.createElement("select");
        bulkAct.classList.add("bulk-action");
        bulkAct.classList.add("custom-select");
        bulkAct.classList.add("custom-select-sm");
        bulkAct.setAttribute("id", "bulk_action");
        Object.keys(_this.bulkActions).forEach(function (el) {
            let option = document.createElement("option");
            option.setAttribute("value", el);
            option.innerHTML = _this.bulkActions[el].label;
            bulkAct.appendChild(option);
        }.bind(this));
        bulkActWrapper.appendChild(bulkAct);

        let bulkActBtn = document.createElement("button");
        bulkActBtn.classList.add("bulk-action-btn");
        bulkActBtn.setAttribute("id", "bulk_action_btn");
        bulkActBtn.innerHTML = "Apply";
        bulkActBtn.addEventListener("click", function () {
            let bulkAct = $("#bulk_action").val();
            let currentAction = _this.bulkActions[bulkAct];
            this.currentAction = currentAction;
            if (this.checkedItems.length > 0) {
                if (currentAction.confirm && currentAction.confirmCallback) {
                    //Confirm Callback
                    this.currentAction.confirmCallback(this.PostBulkAction, this);
                    //console.log(this.checkedItems);
                } else {
                    if (this.currentAction.callback) { //assigned callback with action
                        this.currentAction.callback({ _this: this });
                    } else {//Post Callback default
                        this.PostBulkAction({ _this: this });
                    }
                }
            } else {
                this.notify("Please Select Atleast One Item", "alert");
            }
        }.bind(this));
        bulkActWrapper.appendChild(bulkActBtn);
        _this.bulkActBtn = bulkActBtn;
        return bulkActWrapper;
    }

    refreshSet() {
        clearInterval(refIntval);
        if (this.autoRefress) {
            refIntval = setInterval(() => {
                this.loadData('refresh');
                //console.log(this.checkedItems);
            }, this.autoRefress * 1000);
        }
    }

    async loadData(render = 'full') {
        let _this = this;
        //console.log(_this.apiRoute);
        //indexof in ch
        if (_this.apiRoute.indexOf('page=') === -1) {
            if (_this.apiRoute.indexOf('q=') !== -1) {
                _this.currentPage = 1;
                _this.apiRoute += '&page=' + _this.currentPage;
            } else {
                if (_this.apiRoute.indexOf('?') !== -1) {
                    _this.apiRoute += '&page=' + _this.currentPage;
                } else {
                    _this.apiRoute += '?page=' + _this.currentPage;
                }
            }
        } else {
            const regex = /page=(\d+)/gm;
            let m;
            m = regex.exec(this.apiRoute);
            if (m) {
                this.currentPage = Number(m[1]);
            }

        }

        await axios.get(this.apiRoute).then(function (response) {
            // handle success
            _this.apiResponse = response.data;
            _this.data = _this.apiResponse.data;
            //callback();
            if (!_this.ExistingData) {
                _this.ExistingData = _this.data;
            }


            if (render == 'full') {
                _this.fullRender();
            } else {
                if (JSON.stringify(_this.ExistingData) !== JSON.stringify(_this.data)) {
                    console.log('Data Refreshed');
                    _this.ExistingData = _this.data;
                    _this.refresh();
                }

            }

        }).catch(function (error) {
            console.log(error);
        });

    }

    refresh() {
        let tbody = document.querySelector("#dataTableBody");
        let tbodyData = this.tbody();
        tbody.innerHTML = tbodyData;
        this.setEvent();
        this.endProcess();
    }

    fullRender() {
        this.finalRender();//_this.buildTable();
        this.endProcess();
    }

    /**
     * Filter Dropdown Builder
     */
    dataFilter(colID, columnItem) {
        if (columnItem.dataFilter) {
            let activeFilterStr = "";
            if (this.apiResponse.filter && this.apiResponse.filter.filter == colID && this.apiResponse.filter.val) {
                activeFilterStr = "(" + columnItem.dataFilter.sections[this.apiResponse.filter.val] + ")";
            }
            let filterDropdown = `<div class="dropdown data-filter-dropdown">
            <button class="dropdown-tolggler" type="button" id="filterID_${colID}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class='currentFilter'>${activeFilterStr}</span>
            </button>
            <div class="dropdown-items" aria-labelledby="filterID_${colID}">`;
            filterDropdown += `<a class="dropdown-item"  href="javascript:void(0)" data-filter="" onclick="${columnItem.dataFilter.callback}(event,'${colID}','')">All</a>`;
            for (const key in columnItem.dataFilter.sections) {
                let active = '';
                if (this.apiResponse.filter && this.apiResponse.filter.filter == colID && this.apiResponse.filter.val == key) {
                    active = 'active';
                }
                let item = columnItem.dataFilter.sections[key];
                filterDropdown += `<a class="dropdown-item ${active}"  href="javascript:void(0)" data-filter="${key}" onclick="${columnItem.dataFilter.callback}(event,'${colID}','${key}')">${item}</a>`;
            }
            filterDropdown += `</div></div>`;
            return filterDropdown;
        }
        return "";
    }

    buildThead() {
        let thead = '';
        if (this.isBulkAction) {
            thead += `<th class="bulk-action-th" width='2%'>
            <input value="" type='checkbox' id="allSelect" class='styled-checkbox' /><label class='checkbox-custom-label' for="allSelect"></label>
            </th>`;
        }

        for (const key in this.columns) {
            let item = this.columns[key];
            if (item.visible) {
                //column width in percentage (%)
                let filterDropdown = this.dataFilter(key, item);
                let width = item.width || "";
                //Create Elemet
                thead += `<th class='col-${key.toLowerCase()}' width="${width}"><div class='table-header-item'><div class='headItem'>${item.title}</div> ${filterDropdown}</div></th>`;
            }
        }
        thead += "<th>Action</th>";
        return thead;
    }

    tbody() {
        let htm = "";
        let _this = this;
        if (this.data && this.data.length > 0) {
            this.data.forEach(function (el, i) {
                htm += "<tr class='" + _this.rowClassFilter(el) + "'>";
                //Data Columns

                let checked = "";
                //console.log(this.checkedItems);
                if (_this.isBulkAction) {
                    if (this.checkedItems.includes(el.id)) {
                        //console.log(el.id, this.checkedItems);
                        checked = "checked";
                    }
                    htm += "<td class='bulk-action-td' width='2%'>";
                    htm += `<input value="${el.id}" type='checkbox' ${checked} class='styled-checkbox data-check' id="dataCheck${el.id}" /><label class='checkbox-custom-label' for="dataCheck${el.id}"></label>`;
                    htm += "</td>";
                }

                for (const key in _this.columns) {
                    let item = _this.columns[key];
                    if (item.visible) {
                        let data = el[key];
                        if (item.filter) {
                            data = item.filter(data, el);
                        }
                        htm += "<td class='col-" + key.toLowerCase() + "'>" + data + "</td>";
                    }
                }
                //End Data Columns loop
                //Action Columns
                htm += "<td>";
                let actHtmlObject = [];
                for (const key in _this.actions) {
                    let act = _this.actions[key];
                    let actionUrl = act.url.replace("{id}", el.id);
                    let attr = "";//Action Attributes
                    if (act.attr && Object.getOwnPropertyNames(act.attr).length > 0) {
                        for (const attKey in act.attr) {
                            let attVal = act.attr[attKey];
                            attr += ` ${attKey}="${attVal}"`;
                        }
                    }
                    actHtmlObject.push("<a " + attr + " href='" + actionUrl + "' class='action-" + key + " " + act.class + "'>" + act.title + "</a>");
                }
                htm += `<div class="data-action">${actHtmlObject.join("")}</div>`;
                htm += "</td>";
                //End Actions

                htm += "</tr>";
            }.bind(this));
        } else {
            htm += "<tr><td colspan='" + _this.numberOfColumn + "'>No Data Found</td></tr>";
        }
        return htm;
    }

    buildTable() {
        let _this = this;
        let htm = "";
        htm += "<table class='data-table databuilder-table table table-bordered table-striped info_table table-" + this.model.toLowerCase() + "'>";
        htm += "<thead>";
        htm += "<tr>";
        htm += this.buildThead();
        htm += "</tr>";
        htm += "</thead>";

        htm += "<tbody id='dataTableBody'>";
        htm += this.tbody();
        htm += "</tbody>";

        htm += "</table>";
        //htm += "<div class='pagination-wrap pagination'>";
        //htm += this.paginationHtml();
        //htm += "</div>";
        return htm;
    }
}

export { popup, pagination, Toggler, DataBuilder, ConfirmBox, Notification, DialogBox, Tab };
