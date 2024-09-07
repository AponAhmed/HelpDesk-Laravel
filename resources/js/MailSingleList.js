import { Dombuilder as el } from "@aponahmed/dombuilder";
import ReadMail from "./ReadMail";
import axios from "axios";
import { Attachments } from "./MailBody";
import { Notification } from "./elements";

class SingleItem {
    constructor(list, { ...options }) {
        this.list = list;
        this.item = options.item;
        this.box = options.box;
        this.setAttachments();

        this.detailsActions = options.detailsActions;
        this.actions = {};
        this.labels = [];
        Object.assign(this.actions, options.actions);
        this.setLabels();

        this.composeWindow = false;
        this.detailsData = null;
        this.attachments = null;
        this.inlineAttachments = null;

        this.setAttachments();
    }

    setAttachments() {
        if (this.item.attachments.hasOwnProperty('attachments') && this.item.attachments.attachments.length > 0) {
            this.attachments = this.item.attachments.attachments;
        }
        if (this.item.attachments.hasOwnProperty('inlineAttachments') && this.item.attachments.inlineAttachments.length > 0) {
            this.inlineAttachments = this.item.attachments.inlineAttachments;
        }
    }

    setLabels() {
        this.labels = this.item.labels.split(',');

        //Conditionally Read And Unread Action
        if (this.isLabel("UNREAD")) {
            if (this.actions.hasOwnProperty('markUnread')) {
                delete this.actions.markUnread
            }
        } else {
            if (this.actions.hasOwnProperty('markRead')) {
                delete this.actions.markRead
            }
        }
    }

    updateLabels(_this, refresh = false) {
        axios.post(APP_URL + "/action/update-labels", { id: _this.item.id, labels: this.labels })
            .then(function (response) {
                if (!response.error) {
                    if (refresh) {
                        if (refresh == 'reload') {
                            _this.list.loadData();//'refresh'
                        } else {
                            _this.list.loadData('refresh');//'refresh'
                        }
                    }
                } else {
                    console.log(response.message);
                }
            }).catch((error) => {
                console.log(error);
            });
    }

    setReminder(_this, refresh = true, date) {
        // console.log(_this, refresh, date);
        //return;
        axios.post(APP_URL + "/action/set-reminder", { id: _this.item.id, date: date })
            .then(function (response) {
                if (!response.error) {
                    new Notification({ message: "Reminder Set Successfully", type: "success" });
                    console.log(response);
                    if (refresh) {
                        if (refresh == 'reload') {
                            _this.list.loadData();//'refresh'
                        } else {
                            _this.list.loadData('refresh');//'refresh'
                        }
                    }
                } else {
                    new Notification({ message: response.message, type: "error" });
                    console.log(response.message);
                }
            }).catch((error) => {
                console.log(error);
            });
    }

    addLabels(label) {
        if (!this.labels.includes(label)) {
            this.labels.push(label);
        }
    }

    removeLabels(label) {
        this.labels.splice(this.labels.indexOf(label), 1);
    }

    isLabel(label) {
        return this.labels.includes(label) !== false ? true : false;
    }

    singleMailAction() {
        let actWrap = new el('div').class('single-actions');

        for (const name in this.actions) {
            let action = this.actions[name];
            //----------------------------------------------------------------
            let actDom = new el('div').class('action-wrap');

            //When the action is for incomming mail -
            //then outgoing mail actions are not building
            if (typeof action.Mailflow !== 'undefined') {
                if (this.item.rs === 0 && !action.Mailflow.includes('in')) {
                    continue;
                } else if (this.item.rs === 1 && !action.Mailflow.includes('out')) {
                    continue;
                }
            }

            if (action.label == 'Reminder') {
                actDom.class('set-reminder');
                actDom.append(new el('input').class('date-input').class('hide').attr('type', 'date').element);
            }

            actDom.append(new el('span')
                .class('single-action').attr('title', action.label)

                .class(action.icon)
                .event('click', (e) => {
                    if (action.hasOwnProperty('singleCallback')) {
                        action.singleCallback(this, e);
                    }
                }).element);
            actDom.append(new el('div').class('action-label').html(action.label).element);
            //----------------------------------------------------------------
            actWrap.append(actDom.element);
        }
        return actWrap.element;
    }

    MailDetails(item, e) {
        //Draft Re Composer
        if (item.labels.includes('DRAFT') !== false) {
            location.href = APP_URL + '/compose/draft/' + item.id;
        } else {
            let dm = document.querySelector('.viewWrap');
            dm.innerHTML = ``;
            let read = new ReadMail(this);
            read.render2(dm);
        }
    }

    importantToggle(e, _this) {
        let impEl = e.target;
        let rq4 = true;
        if (impEl.classList.contains('imp')) {
            rq4 = false;
            impEl.classList.remove('imp');
            this.removeLabels('IMPORTANT');
        } else {
            rq4 = true;
            impEl.classList.add('imp');
            this.addLabels('IMPORTANT');
        }
        //Request to server APi to handle Important
        this.updateLabels(_this);
    }

    singleListItem() { //Virtual Dom Element
        let listItem = new el('div').class('mail-list-item').attr('data-msg_theread', this.item.msg_theread).attr('data-msg_id', this.item.msg_id).attr('data-id', this.item.id);
        //UnRead
        if (this.labels.includes('UNREAD') !== false) {
            listItem.class('unreaded');
        }
        //EventHandler
        let currentItem = this.item;
        //Checkbox
        let listControl = new el('div').class('listControll').append(
            new el('div').class('list-checkBox').append(
                new el('input').attr('type', 'checkbox').class('data-check').attr('value', this.item.id).element
            ).element
        );
        //Important Controll
        let impController = new el('div').class('important-controll').html(
            `<svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><title>Important</title><path d="M480 208H308L256 48l-52 160H32l140 96-54 160 138-100 138 100-54-160z" fill="none"stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path></svg>`
        ).event('click', (event, d) => {
            this.importantToggle(event, this);
        });
        if (this.isLabel('IMPORTANT')) {
            impController.class('imp');
        }
        //.class('imp')
        listControl.append(impController.element);
        listItem.append(listControl.element);//Controll Elements
        let mailinfo = new el('div').class('mailInfo');
        //History Count
        let countDom = new el('div').class('history-count').html(this.item.historyCount > 1 ? this.item.historyCount : "");
        //Customer Name
        mailinfo.append(
            new el('div').class('senderInfo').class('singleLine').event('click', (e) => {
                this.MailDetails(currentItem, e);
            }).attr('title', this.item.customerName).html(this.item.customerName).append(countDom.element).element
        );
        //Snipet
        let snippet = new el('div').class("snippet");

        //List Controllers
        let singleController = new el('div').class('singleController').append(
            new el('div').class('dateTime').html(this.item.date).element
        );

        if (this.attachments) {//----
            //attachment signatures in mail lis
            singleController.append(new el('span').event('click', () => {
                //console.log('Attachments PopUp willbe triggered');
                let att = new Attachments({ attachments: this.attachments, inlineAttachments: this.inlineAttachments });
                att.popup();

            }).class('attachment-icon').attr('title', 'Has Attachments').element);
        }

        singleController.append(
            new el('div').class('mail-user').append(
                new el('div').class('bg-icon').class('user-icon').attr('title', this.item.userName).class(this.item.userName == 'Unassigned' ? 'unassigned' : 'assigned').element
            ).append(
                new el('div').class('user-name').html(this.item.userName).element
            ).element
        )
        singleController.append(this.singleMailAction());
        //List Controllers

        snippet.append(//Top Part
            new el('div').class('snipetTop').append(
                new el('div').class('subject-line').class('singleLine').attr('title', this.item.subject).event('click', (e) => {
                    this.MailDetails(currentItem, e);
                }).html(this.item.subject).element
            ).append(singleController.element).element
        );
        snippet.append(
            new el('div').class('body-snippet').class('singleLine').attr('title', this.item.snippet).html(this.item.snippet).event('click', (e) => {
                this.MailDetails(currentItem, e);
            }).element
        );

        // snippet.event('click', (e) => {
        //     this.MailDetails(currentItem, e);
        // });

        mailinfo.append(snippet.element);
        listItem.append(mailinfo.element);
        this.dom = listItem.element;
        return listItem.element;
    }

    remove() {
        this.dom.remove();
    }

}

export default SingleItem;
