import axios from "axios";
import { ConfirmBox, Notification, pagination } from "./elements";
import { Dombuilder as el } from "@aponahmed/dombuilder";
import SingleItem from "./MailSingleList";
import Actions from "./Actions";
import SysNotify from "./SysNotify";

let mailRefreshIntVal;


class MailList {
    constructor(dom, { ...options }) {
        this.dom = dom;
        this.box = options.box;
        this.apiRoute = options.apiRoute || false;
        this.apiRouteRoot = options.apiRoute || false;
        this.actions = options.actions || {};
        this.startProcess = options.startProcess || function () { };
        this.endProcess = options.endProcess || function () { };
        this.rowClassFilter = options.rowClassFilter || function () { return "mail-list-item" };
        this.currentPage = options.currentPage || 1;
        this.model = options.model;
        //Auto Refresh Data
        this.autoRefress = options.autoRefress || false;//accept int as seccond
        //Bulk Actions
        this.isBulkAction = options.isBulkAction === false ? false : true;
        this.bulkActions = options.bulkActions || {};
        //this.actionSet();
        //Merge Bulk Options
        this.bulkActions = { ...this.bulkActions };
        this.init();
        this.refreshSet();
        this.ExistingData = false;
    }

    actionSet(actions) {

        this.actionController = new Actions({
            box: this.box,
            type: 'list',
            actions: actions
        });
        //console.log(this.actionController);
        this.bulkActions = this.actionController.getActions('bulk');
        this.actions = this.actionController.getActions('list');
        this.detailsActions = this.actionController.getActions('details');
    }
    /**
     * Execute Of Bulk Action
     * @param {*} MailList Instance
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

    /**
     * Initialize the Data list
     */
    init() {
        this.checkedItems = [];
        this.startProcess();
        this.loadData();
    }

    /**
     * Event Set to Refresh Data automatically
     *  */
    refreshSet() {
        clearInterval(mailRefreshIntVal);
        if (this.autoRefress) {
            mailRefreshIntVal = setInterval(() => {
                this.loadData('refresh');
                //this.eventRefresh();
                //console.log(this.checkedItems);
            }, this.autoRefress * 1000);
        }
    }

    /**this Function is for experement */
    eventRefresh() {
        let _this = this;
        //console.log(_this.apiRoute);
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
        let wsAppUrl = APP_URL.replace('http', 'ws');
        let WebSocket = new WebSocket(wsAppUrl + "mail-stream?box=" + this.box + "&page=" + this.currentPage);
        // var source = new EventSource(APP_URL + "mail-stream?box=" + this.box + "&page=" + this.currentPage);
    }

    async loadData(render = 'full') {
        let _this = this;
        //console.log(_this.apiRoute);
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
            _this.actionSet(_this.apiResponse.actions);
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
                } else {
                    console.log('same data');
                }
            }
        }).catch(function (error) {
            console.log(error);
        });
    }

    refresh() {
        this.listBuilder();
        this.setEvent();
        this.endProcess();
    }

    fullRender() {
        this.finalRender();//_this.buildTable();
        this.endProcess();
    }


    finalRender() {
        let dataControllerWrap = new el('div').class('data-controller-wrap').class('maillist-controller-wrap').element
        let checkbox = new el('input').attr('type', 'checkbox').attr('id', 'allSelect')
        let allSelWrap = new el('div').class('sel-all-wrap').append(
            checkbox.element
        ).element;

        dataControllerWrap.appendChild(allSelWrap);
        dataControllerWrap.appendChild(this.bulkActionDom());

        let paginationWrap = new el("div").class('pagination-wrap').class('pagination').element;
        this.pagination = new pagination(this.apiResponse);
        paginationWrap.innerHTML = this.pagination.linksHtm();
        dataControllerWrap.appendChild(this.itemPerPage());
        dataControllerWrap.appendChild(paginationWrap);

        this.dataWrap = document.createElement("div");
        this.dataWrap.classList.add("data-wrap");

        this.listBuilder();
        this.broadcastReceive();
        //dataWrap.innerHTML = this.buildLists();

        let dataWraper = document.createElement("div");
        dataWraper.classList.add("data-wraper");
        dataWraper.appendChild(this.dataWrap);
        dataWraper.appendChild(dataControllerWrap);

        this.dom.innerHTML = "";
        this.dom.appendChild(dataWraper);
        this.setEvent();
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

    listBuilder() {
        this.dataWrap.innerHTML = "";
        this.lists = [];
        this.data.forEach(item => {
            this.item = item;
            let listitem = new SingleItem(this, {
                item: this.item,
                box: this.box,
                actions: this.actions,
                detailsActions: this.detailsActions
            });
            this.lists[this.item.id] = listitem;
            this.dataWrap.appendChild(listitem.singleListItem());
            //this.setLabels();
            //wrap.appendChild(this.singleListItem());
        });
    }


    broadcastReceive() {

        window.Echo.private('mail.' + USER_ID).listen('MailArrived', (e) => {
            this.item = e;
            //console.log(this.item);

            let listitem = new SingleItem(this, {
                item: this.item,
                box: this.box,
                actions: this.actions,
                detailsActions: this.detailsActions
            });
            this.lists[this.item.id] = listitem;
            //this.dataWrap.appendChild(listitem.singleListItem());
            this.dataWrap.insertBefore(listitem.singleListItem(), this.dataWrap.firstChild);


            const notification = new SysNotify({
                title: 'New Mail Arrived',
                body: 'You have received a new email from John Doe.',
                icon: 'path/to/icon.png',
                link: 'https://mailapp.example.com/inbox',
                requireInteraction: true,
                silent: false
            });

            // Show the notification
            notification.showNotification();
        });
    }




    setLabels() {
        this.labels = this.item.labels.split(',');
        //console.log(this.labels);
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

        let actionSupport = new el('div').class('bulk-action-support');

        if (_this.bulkActions.hasOwnProperty('reminder')) {
            actionSupport.class('set-reminder');
            actionSupport.append(new el('input').class('reminder-date-input-bulk').attr('type', 'date').element);
        }

        bulkActWrapper.append(actionSupport.element);

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
        bulkActBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><title>Checkmark</title><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96"/></svg><span>Apply</span>`;
        bulkActBtn.addEventListener("click", function () {
            let bulkAct = $("#bulk_action").val();
            let currentAction = _this.bulkActions[bulkAct];
            this.currentAction = currentAction;
            if (this.checkedItems.length > 0) {
                if (currentAction.confirm && currentAction.confirmCallback) {
                    //Confirm Callback
                    if (this.currentAction.callback) { //assigned callback with action
                        this.currentAction.confirmCallback(this.currentAction.callback, this);
                    } else {//Post Callback default
                        this.currentAction.confirmCallback(this.PostBulkAction, this);
                    }
                    //this.currentAction.confirmCallback(this.PostBulkAction, this);
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

    updateLabels({ ...arg }) {
        let _this = this;
        let refresh = arg.refresh;
        axios.post(APP_URL + "/action/update-labels", {
            ids: this.checkedItems,
            labels: arg.label,
            action: arg.action || 'add',
        })
            .then(function (response) {
                response = response.data;
                if (arg.callback) {
                    arg.callback(response)
                }
                if (!response.error) {
                    if (refresh) {
                        if (refresh == 'reload') {
                            _this.loadData();//'refresh'
                        } else {
                            _this.loadData('refresh');//'refresh'
                        }
                    }
                } else {
                    console.log(response.message);
                }
            }).catch((error) => {
                console.log(error);
            });
    }

    setReminder(date) {
        // console.log(_this, refresh, date);
        //return;
        axios.post(APP_URL + "/action/set-reminder", { ids: this.checkedItems, date: date })
            .then((response) => {
                if (!response.error) {
                    this.loadData('refresh');//'refresh'
                    this.notify("Reminder Set Successfully", "success");
                } else {
                    console.log(response.message);
                    this.notify(response.message, "alert");
                }
            }).catch((error) => {
                console.log(error);
            });
    }
}

export default MailList;
