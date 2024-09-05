import axios from "axios";
import { ConfirmBox, DialogBox, Notification } from "./elements";
import { Dombuilder as el } from "@aponahmed/dombuilder";
import { Replay, ReplayAll, Forward } from "./Compose";

const loaderCircle = new el('div').class('load').class('load03').append(new el('div').element).element;
const assignUserSelect = async (target, callback, pos = 'bottom') => {
    target.parentNode.appendChild(loaderCircle);
    await axios.post(APP_URL + '/action/get', { 'type': 'user' }).then((response) => {
        loaderCircle.remove();
        let targetPos = target.getBoundingClientRect();
        let bodyWrapper = document.querySelector('.hdesk-wrap');

        let userSekect = new el('div').class('user-select').append(new el('span').class('close-assign').event('click', () => {
            userSekect.element.remove();
        }).html('&times;').element);

        response.data.map(user => {
            userSekect.append(new el('div').class('user').attr('data-id', user.id).event('click', (e) => {
                callback(e, target);
                userSekect.element.remove();
            }).html(user.name).element);
        });
        if (pos == 'bottom') {
            userSekect.element.style.top = targetPos.y + "px";
            userSekect.element.style.left = targetPos.x + "px";
        } else {
            console.log(bodyWrapper.offsetHeight - targetPos.y);
            userSekect.element.style.bottom = (bodyWrapper.offsetHeight - (targetPos.y - targetPos.height)) + "px";
            userSekect.element.style.left = targetPos.x + "px";
        }
        bodyWrapper.appendChild(userSekect.element);
    });
};

const actions = {
    assign: {
        name: 'assign',
        label: 'Assign',
        icon: 'assign-icon',
        route: 'assign',
        Mailflow: ['in'],//Action Filtering with mail in or out
        singleCallback: (SingleItem, event) => {
            assignUserSelect(event.target, (e, target) => {
                target.parentNode.appendChild(loaderCircle);
                let data = {
                    user: e.target.getAttribute('data-id'),
                    id: SingleItem.item.id
                };
                axios.post(APP_URL + '/action/assign', data)
                    .then((response) => {
                        loaderCircle.remove();
                        response = response.data;
                        if (!response.error && SingleItem.box == 'unassigned') {
                            SingleItem.dom.remove();
                        } else {
                            SingleItem.list.loadData('refresh');
                        }
                    }).catch(error => {
                        console.log(error.message);
                    });
            });
        },
        visibleIn: ['all'],//all,bulk,list,details

        callback: (object) => {
            let list = object._this;
            let target = list.bulkActBtn;

            assignUserSelect(target, (e, target) => {
                target.parentNode.appendChild(loaderCircle);
                let data = {
                    user: e.target.getAttribute('data-id'),
                    ids: list.checkedItems
                };
                axios.post(APP_URL + '/action/assign', data)
                    .then((response) => {
                        loaderCircle.remove();
                        response = response.data;
                        if (!response.error) {
                            list.loadData('refresh');
                            new Notification({ message: response.message, type: 'success' });
                        } else {
                            list.loadData('refresh');
                            new Notification({ message: response.message, type: 'error' });
                        }
                    }).catch(error => {
                        console.log(error.message);
                    });
            }, 'top');
        }
    },

    reminder: {
        name: "reminder",
        label: 'Reminder',
        icon: 'reminder-icon',
        route: 'reminder',
        Mailflow: ['in'],//Action Filtering with mail in or out
        visibleIn: ['all'],
        singleCallback: (SingleItem, event) => {
            let dateInput = event.target.parentNode.querySelector('.date-input');
            if (dateInput) {
                // Optionally, you can also trigger a click to open the calendar
                dateInput.showPicker();
                // Add a change event listener to the date input
                dateInput.addEventListener('change', function () {
                    SingleItem.setReminder(SingleItem,true,dateInput.value);
                    // Handle the date change event
                   // alert('Selected Date: ' + dateInput.value);
                });
            } else {
                console.error('Date input element not found.');
            }
        },
        callback: (object) => {//MailList Object
            let list=object._this;
            let dateInput = document.querySelector('.reminder-date-input-bulk');
            dateInput.showPicker();
            dateInput.addEventListener('change', function () {
                list.setReminder(dateInput.value);
                // Handle the date change event
               // alert('Selected Date: ' + dateInput.value);
            });
        }
    },

    markSpam: {
        name: "markSpam",
        label: 'Mark Spam',
        icon: 'spam-icon',
        route: 'spam',
        visibleIn: ['all'],
        Mailflow: ['in'],//Action Filtering with mail in or out
        singleCallback: (SingleItem, event) => {
            let Conf = new ConfirmBox({
                param: { SingleItem: SingleItem, target: event.target },
                title: "Spam Confirmations",
                message: `You sure to mark '${SingleItem.item.customerName}' as Spamer ?`,
                yesCallback: (param) => {
                    param.target.parentNode.appendChild(loaderCircle);
                    SingleItem = param.SingleItem;
                    SingleItem.addLabels('SPAM');
                    SingleItem.dom.remove();
                    SingleItem.updateLabels(SingleItem, false);
                },//this.bulkActions.inactive.callback,
            });
        },
        confirm: true,
        confirmCallback: (callback, _this) => {
            let Conf = new ConfirmBox({
                param: { _this: _this },
                title: "Spam Confirmations",
                message: `Are you sure to Spam Selected Mails ?`,
                yesCallback: callback,//this.bulkActions.inactive.callback,
            });
        },
        callback: (object) => {//MailList Object
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'SPAM',
                    action: 'add',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },

    markNotSpam: {
        name: "markNotSpam",
        label: 'Not Spam',
        icon: 'notspam-icon',
        route: 'spam',
        Mailflow: ['in'],//Action Filtering with mail in or out
        visibleIn: ['all'],
        singleCallback: (SingleItem, event) => {
            event.target.parentNode.appendChild(loaderCircle);
            SingleItem.removeLabels('SPAM');
            SingleItem.dom.remove();
            SingleItem.updateLabels(SingleItem, false);
        },
        callback: (object) => {//MailList Object
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'SPAM',
                    action: 'remove',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },

    hold: {
        name: "hold",
        label: 'Hold',
        icon: 'hold-icon',
        route: 'hold',
        Mailflow: ['in'],//Action Filtering with mail in or out
        visibleIn: ['list', 'bulk', 'details'],
        singleCallback: (SingleItem, event) => {
            let Conf = new ConfirmBox({
                param: { SingleItem: SingleItem, target: event.target },
                title: "Hold Mail",
                message: `You sure, You want to Hold ?`,
                yesCallback: (param) => {
                    param.target.parentNode.appendChild(loaderCircle);
                    SingleItem = param.SingleItem;
                    SingleItem.addLabels('HOLD');
                    SingleItem.dom.remove();
                    SingleItem.updateLabels(SingleItem, false);
                },//this.bulkActions.inactive.callback,
            });
        },
        callback: (object) => {//MailList Object
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'HOLD',
                    action: 'add',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },

    unHold: {
        name: "unHold",
        label: 'Un-Hold',
        icon: 'notspam-icon',
        route: 'un-hold',
        Mailflow: ['in'],//Action Filtering with mail in or out
        visibleIn: ['list', 'bulk', 'details'],
        singleCallback: (SingleItem, event) => {
            event.target.parentNode.appendChild(loaderCircle);
            SingleItem.removeLabels('HOLD');
            SingleItem.dom.remove();
            SingleItem.updateLabels(SingleItem, false);
        },
        callback: (object) => {//MailList Object
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'HOLD',
                    action: 'remove',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },

    markUnread: {
        name: 'markUnread',
        label: 'Mark Unread',
        icon: 'unread-icon',
        route: 'mark-unread',
        Mailflow: ['in'],//Action Filtering with mail in or out
        singleCallback: (SingleItem, event) => {
            event.target.parentNode.appendChild(loaderCircle);
            SingleItem.addLabels('UNREAD');
            SingleItem.dom.classList.add('unreaded');
            SingleItem.updateLabels(SingleItem, true);
        },
        visibleIn: ['list', 'bulk'],
        callback: (object) => {
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'UNREAD',
                    action: 'add',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },

    markRead: {
        name: 'markRead',
        label: 'Mark Read',
        icon: 'read-icon',
        route: 'mark-read',
        visibleIn: ['list', 'bulk'],
        Mailflow: ['in'],//Action Filtering with mail in or out
        singleCallback: (SingleItem, event) => {
            event.target.parentNode.appendChild(loaderCircle);
            SingleItem.removeLabels('UNREAD');
            SingleItem.dom.classList.remove('unreaded');
            SingleItem.updateLabels(SingleItem, true);
        },
        callback: (object) => {
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'UNREAD',
                    action: 'remove',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },

    resend: {
        name: 'resend',
        label: 'Re-Send',
        icon: 'resend-icon',
        route: 'resend',
        Mailflow: ['out'],//Action Filtering with mail in or out
        visibleIn: ['list', 'bulk', 'details'],
        singleCallback: (SingleItem, event) => {
            event.target.parentNode.appendChild(loaderCircle);
            SingleItem.removeLabels('SENT');
            SingleItem.addLabels('OUT');
            SingleItem.dom.remove();
            SingleItem.updateLabels(SingleItem, true);
        },
        callback: (object) => {
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'SENT',
                    action: 'remove',
                    refresh: false,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
            list.updateLabels(
                {
                    label: 'OUT',
                    action: 'add',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },

    trash: {
        name: 'trash',
        label: 'Trash',
        route: 'trash',
        icon: 'trash-icon',
        visibleIn: ['all'],
        singleCallback: (SingleItem, event) => {
            event.target.parentNode.appendChild(loaderCircle);

            let Conf = new ConfirmBox({
                param: { SingleItem: SingleItem },
                title: "Trash Confirmations",
                message: `Are you sure to Trash this Mails from '${SingleItem.item.customerName}' ?`,
                yesCallback: (param) => {
                    SingleItem = param.SingleItem;
                    SingleItem.addLabels('TRASH');
                    SingleItem.dom.remove();
                    SingleItem.updateLabels(SingleItem, false);
                },//this.bulkActions.inactive.callback,
            });
        },
        confirm: true,
        confirmCallback: (callback, _this) => {
            let Conf = new ConfirmBox({
                param: { _this: _this },
                title: "Trash Confirmations",
                message: `Are you sure to Trash Selected Mails ?`,
                yesCallback: callback,//this.bulkActions.inactive.callback,
            });
        },
        callback: (object) => {
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'TRASH',
                    action: 'add',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },
    unTrash: {
        name: 'unTrash',
        label: 'Untrash',
        route: 'un-trash',
        icon: 'untrash-icon',
        singleCallback: (SingleItem, event) => {
            event.target.parentNode.appendChild(loaderCircle);
            SingleItem.removeLabels('TRASH');
            SingleItem.dom.classList.remove('unreaded');
            SingleItem.updateLabels(SingleItem, true);
        },
        visibleIn: ['all'],
        callback: (object) => {
            let list = object._this;
            let target = list.bulkActBtn;
            target.parentNode.appendChild(loaderCircle);
            list.updateLabels(
                {
                    label: 'TRASH',
                    action: 'remove',
                    refresh: true,
                    callback: () => {
                        loaderCircle.remove();
                    }
                }
            );
        }
    },
    delete: {
        name: 'delete',
        label: 'Delete',
        icon: 'delete-icon',
        route: 'delete',
        singleCallback: (SingleItem, event) => {
            let Conf = new ConfirmBox({
                param: { SingleItem: SingleItem, event: event },
                title: "Delete Confirmations",//
                message: `Permanently delete this conversation ${SingleItem.item.rs == 1 ? 'to' : 'from'} '${SingleItem.item.customerName}' ?`,
                yesCallback: (param) => {
                    param.event.target.parentNode.appendChild(loaderCircle);
                    SingleItem = param.SingleItem;
                    let data = {
                        theread: SingleItem.item.msg_theread,
                        box: SingleItem.box
                    };
                    // console.log(SingleItem);
                    axios.post(APP_URL + '/action/delete', data)
                        .then((response) => {
                            loaderCircle.remove();
                            response = response.data;
                            if (!response.error) {
                                new Notification({ message: response.message, type: 'success' });
                                SingleItem.dom.remove();
                            } else {
                                new Notification({ message: response.message, type: 'error' });
                                //SingleItem.list.loadData('refresh');
                            }
                        }).catch(error => {
                            loaderCircle.remove();
                            new Notification({ message: "! Error", type: 'error' });
                            console.log(error.message);
                        });
                },
            });
        },
        visibleIn: ['all'],
    },

    edit: {
        name: 'edit',
        label: 'Edit',
        icon: 'edit-icon',
        route: 'edit',
        visibleIn: ['details', 'list'],
        singleCallback: (id, event) => {

        },
    },
    forward: {
        name: 'forward',
        label: 'Forward',
        icon: 'forward-icon',
        route: 'forward',
        visibleIn: ['details'],
        singleCallback: (SingleItem, event) => {
            let renderTo = document.querySelector('.mail-view .details-wrapper');
            let fw = new Forward({ SingleItem, renderTo });

            fw.init().then(() => {
                fw.afterRender();
                SingleItem.composeWindow = 'Forward';

            });
            // viewer.appendChild(new el('div').class("composer-wrap").append(reply.dom.element).element);

        }
    },
    reply: {
        name: 'reply',
        label: 'Reply',
        icon: 'reply-icon',
        route: 'reply',
        visibleIn: ['details'],
        singleCallback: (SingleItem, event) => {
            let renderTo = document.querySelector('.mail-view .details-wrapper');
            let reply = new Replay({ SingleItem, renderTo });

            reply.init().then(() => {
                reply.afterRender();
                SingleItem.composeWindow = 'Reply';

            });
            // viewer.appendChild(new el('div').class("composer-wrap").append(reply.dom.element).element);

        }
    },
    replyAll: {
        name: 'replyAll',
        label: 'Reply All',
        icon: 'replyall-icon',
        route: 'replyall',
        visibleIn: ['details'],
        singleCallback: (SingleItem, event) => {
            let renderTo = document.querySelector('.mail-view .details-wrapper');
            let replyAll = new ReplayAll({ SingleItem, renderTo });

            replyAll.init().then(() => {
                replyAll.afterRender();
                SingleItem.composeWindow = 'Reply All';
            });
            // viewer.appendChild(new el('div').class("composer-wrap").append(reply.dom.element).element);
        }
    },
    print: {
        name: 'print',
        label: 'Print',
        icon: 'print-icon',
        route: 'print',
        visibleIn: ['details'],
        singleCallback: (id, event) => {

        }
    }
};


//actions.reply.visibleIn.push('list');
//actions.forward.visibleIn.push('list');
//actions.print.visibleIn.push('list')

const boxActions = {
    unassigned: [
        actions.assign,
        actions.reply,
        actions.replyAll,
        actions.markRead,
        actions.markUnread,
        actions.forward,
        actions.reminder,
        actions.hold,
        actions.markSpam,
        actions.trash,
        //actions.edit,
        actions.print,
        //actions.delete,
    ],
    new: [
        actions.assign,
        actions.reply,
        actions.replyAll,
        actions.forward,
        actions.reminder,
        actions.trash,
        actions.markUnread,
        actions.markRead,
        actions.markSpam,
        actions.hold,
        actions.print
    ],
    sent: [
        actions.assign,
        actions.reply,
        actions.replyAll,
        actions.forward,
        actions.trash,
        actions.markUnread,
        actions.markRead,
        actions.markSpam,
        actions.hold,
        actions.print,
        actions.resend

    ],
    important: [
        actions.assign,
        actions.reply,
        actions.replyAll,
        actions.forward,
        actions.trash,
        actions.print,
        actions.markUnread,
        actions.markRead,
        actions.markSpam
    ],
    draft: [
        actions.edit,
        actions.trash,
        actions.delete
    ],
    trash: [
        actions.unTrash,
        actions.delete,
    ],
    spam: [
        actions.markNotSpam,
        actions.delete,
    ],
    hold: [
        actions.unHold,
        actions.delete,
    ],
    outbox: [
        actions.assign,
        actions.edit,
        actions.trash,
        //actions.delete,
    ]
};

class Actions {
    constructor({ ...options }) {

        this.options = options
        this.type = options.type || 'list';//list,bulk,details
        this.box = options.box || 'unassigned';
        this.providedActions = options.actions;//Action Provided from server

        if (boxActions[this.box]) {
            this.actions = boxActions[this.box];
        } else {
            this.actions = [];
        }
    }

    getActions(type = false) {
        if (type) {
            this.type = type;
        }

        let currActions = this.actions.filter((action) => {
            if (action.hasOwnProperty('visibleIn') && (action.visibleIn.includes('all') || action.visibleIn.includes(this.type))) {
                return action
            }
        });

        let currentActionObject = {};
        currActions.forEach(element => {
            if (this.providedActions.includes(element.name) !== false) {
                currentActionObject[element.name] = element;
            }
        });
        return currentActionObject;
    }
}

export default Actions;
