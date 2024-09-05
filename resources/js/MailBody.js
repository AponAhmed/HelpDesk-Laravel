import { Dombuilder as el } from "@aponahmed/dombuilder";
import { Notification } from "./elements";
import axios from "axios";

/**
 * Single Mail Body action
 * body Content
 * include Attachment
 */
class Attachment {
    constructor(attachment) {
        this.attachment = attachment;
        // { filename: [ "SeoSearch.pdf", "642bd2ec472e6-_-SeoSearch.pdf" ], contentID: "f_lfxo417g0", Type: "application/pdf" }
        this.imageMimes = ['image/jpg', 'image/png', 'image/jpeg', 'image/webp', 'image/gif'];
        this.isImage = false;
        this.preViewAble = false;
        //Set Image Flags
        if (this.imageMimes.includes(this.attachment.Type) !== false) {
            this.isImage = true;
        }
        //Previewable Attachment flags set
        this.previewAbleMime = this.imageMimes.concat(['application/pdf']);
        if (this.previewAbleMime.includes(this.attachment.Type) !== false) {
            this.preViewAble = true;
        }

    }

    downloadFile(url, fileName) {
        fetch(url, { method: 'get', mode: 'no-cors', referrerPolicy: 'no-referrer' })
            .then(res => res.blob())
            .then(res => {
                const aElement = document.createElement('a');
                aElement.setAttribute('download', fileName);
                const href = URL.createObjectURL(res);
                aElement.href = href;
                // aElement.setAttribute('href', href);
                aElement.setAttribute('target', '_blank');
                aElement.click();
                URL.revokeObjectURL(href);
            });
    };

    ui4SingleAttachment() {
        this.singleUi = new el('div').class('single-attachment').class('mail-view-attachment');
        //parse the attachments
        this.name = this.attachment.filename[0];
        const re = /(?:\.([^.]+))?$/;
        let ext = re.exec(this.name)[1];
        let name = this.name.replace(re, "");

        //Attachment Action
        this.controll = new el('div').class('attachment-actions');
        //Download
        this.controll.append(new el('div').class('attachment-action').event('click', () => {
            this.downloadFile(ATTACH + this.attachment.filename[1], this.attachment.filename[0]);
        }).class('single-action').class('down-icon').element);
        //View in new Window
        if (this.preViewAble) {
            this.controll.append(new el('div').class('attachment-action').event('click', () => {
                window.open(ATTACH + this.attachment.filename[1]);
            }).class('single-action').class('view-icon').element);
        }

        this.singleUi.append(this.controll.element);
        //End Attachment Action

        if (this.isImage) {
            //Direct Image Preview
            this.icon = new el('img').class('attachment-placeholder').attr('src', ATTACH + this.attachment.filename[1]);
        } else {
            //Icon for File
            this.icon = new el('span').class('file-icon').class('icon-xl');//<span class="file-icon file-pdf"></span>
            this.icon.class(`file-${ext}`);
        }
        this.singleUi.append(this.icon.element);
        //File Name
        this.singleUi.append(new el('div').class('file-name').append(
            new el('span').class('fname').html(name).element
        ).append(
            new el('span').html("." + ext).element
        ).element)
        //this.singleUi.append()
        return this.singleUi.element;
    }
}

class Attachments {
    constructor({ ...attachments }) {
        this.attachments = attachments;
        this.dom = null;
        this.render();
    }

    downloadAll() {

    }

    render() {
        if (this.attachments.attachments.length == 0) {
            return;
        }
        this.dom = new el('div').class('view-attachments-area');


        this.dom.append(new el('div').class('area-title').append(
            new el('strong').html(`${this.attachments.attachments.length} Attachment${this.attachments.attachments.length > 1 ? 's' : ''}`).element
        ).append(
            new el('label').class('single-action').class('down-icon').event('click', (e) => {
                this.popup();
            }).class('download-all').element
        ).element);

        let attwrap = new el('div').class('attachment-area');
        if (typeof this.attachments.attachments === 'object') {
            for (let key in this.attachments.attachments) {
                if (this.attachments.attachments.hasOwnProperty(key)) {
                    let file = this.attachments.attachments[key];
                    this.attachment = new Attachment(file);
                    attwrap.append(this.attachment.ui4SingleAttachment());
                }
            }
        } else {
            console.log(this.attachments.attachments);
            this.attachments.attachments.forEach((file) => {
                //file ={ filename: [ "SeoSearch.pdf", "642bd2ec472e6-_-SeoSearch.pdf" ], contentID: "f_lfxo417g0", Type: "application/pdf" }
                this.attachment = new Attachment(file);
                attwrap.append(this.attachment.ui4SingleAttachment());
            });
        }
        this.dom.append(attwrap.element);
    }

    removePopup() {
        this.popup.element.remove();
    }

    popup() {
        this.popup = new el('div').class('popup-wrap').append(
            new el('div').class('popup-body').append(
                new el('span').class('closePopup').event('click', () => {
                    this.removePopup();
                }).element
            ).append(
                new el('div').class('popup-inner').append(this.dom.element).element
            ).element
        );
        document.querySelector('body').appendChild(this.popup.element);
        //<div class="popup-wrap 1681371023564"><div class="popup-body " style="width:500px"><span class="closePopup"></span><div class="popup-inner"><div class="user-create">
    }
}


class MailBody {
    constructor(details, actions) {
        this.details = details;
        this.actions = (({ reply, replyAll, forward, resend }) => {
            const result = {};
            if (typeof reply !== 'undefined') {
                result.reply = reply;
            }
            if (typeof replyAll !== 'undefined') {
                result.replyAll = replyAll;
            }
            if (typeof forward !== 'undefined') {
                result.forward = forward;
            }
            if (typeof resend !== 'undefined') {
                result.resend = resend;
            }
            return result;
        })(actions);

        this.data = this.details.data;

        this.attachments = new Attachments(JSON.parse(this.data.attachments));

        this.dom = new el('div').class('mail-details-body');
        //this.dom.element.style.minHeight = "350px";

        this.loader = new el('div').class('mail-loader-overly').append(
            new el('div').class('load').class('load10').append(
                new el('div').element
            ).append(
                new el('div').element
            ).element);

        this.init();
    }

    init() {
        this.dom.append(this.loader.element);
        this.frame();
        //Here Attachment Dom
        if (this.attachments.dom) {
            this.dom.append(this.attachments.dom.element);
        }
        //Bottom Actions

        let directAct = new el('div').class('direct-actions').class('action-in-body');
        //console.log(this.actions);
        for (let action in this.actions) {
            let act = this.actions[action];
            directAct.append(this.details.actionBuild(act))
        }
        this.dom.append(directAct.element);
    }

    stripScripts(s) {
        var div = document.createElement('div');
        div.innerHTML = s;
        var scripts = div.getElementsByTagName('script');
        var i = scripts.length;
        while (i--) {
            scripts[i].parentNode.removeChild(scripts[i]);
        }
        return div.innerHTML;
    }

    frame() {
        this.frame = new el('iframe').class('mail-body-frame')
            //.attr('height', '100%')
            .attr('width', '100%')
            .attr('id', 'detailsFrame' + this.data.id)
            //.attr('scrolling', 'no')
            //.attr('onload', 'this.height=screen.height')
            .element;
        this.frame.srcdoc = this.stripScripts(this.data.body);
        this.frame.setAttribute('scrolling', 'no');

        this.frame.addEventListener('load', () => {
            this.resize();
            this.loader.element.remove();
            this.applyDefaultStyles(); // Call the method to apply default styles
            this.applyToggler();
        });
        this.frame.style.border = "none";


        this.frame.style.width = "100%";
        this.frame.style.height = "100%";
        this.dom.append(this.frame);
    }

    applyToggler() {
        const iframeDocument = this.frame.contentDocument || this.frame.contentWindow.document;

        if (!iframeDocument) {
            //console.error("Could not access the iframe document.");
            return;
        }
        console.log(iframeDocument);
        const toggler = iframeDocument.querySelector('.body-toggler');
        if (!toggler) {
            //console.error("Toggler element not found.");
            return;
        }

        // Extract the numeric part from the toggler's ID (e.g., "tgigger-152" -> "152")
        const idMatch = toggler.id.match(/tgigger-(\d+)/);
        if (!idMatch) {
            console.error("Invalid toggler ID format.");
            return;
        }

        const contentId = idMatch[1]; // Extracted ID, e.g., "152"
        const contentSection = iframeDocument.getElementById(contentId);

        if (!contentSection) {
            console.error(`Content section with ID '${contentId}' not found.`);
            return;
        }

        // Toggle display on click of the toggler
        toggler.addEventListener('click', () => {

            contentSection.style.display = (contentSection.style.display === "none") ? "block" : "none";
            this.updateHeight();
        });
    }


    applyDefaultStyles() {
        const iframeDocument = this.frame.contentDocument || this.frame.contentWindow.document;
        const style = iframeDocument.createElement('style');
        style.textContent = `
            body {
                font-family: Arial, sans-serif; /* Default font family */
                font-size: 14px; /* Default font size */
            }
            hr {
                border: 0;
                height: 1px;
                background: #f2f2f2;
            }
            .body-toggle-section {
                padding-left: 12px;
                border-left: 1px solid #eee;
            }
            .body-toggler > span {
                display: flex;
                margin: 10px 0;
                padding: 4px;
                width: 20px;
                justify-content: center;
                background: #f0f0f0;
                align-items: center;
                border-radius: 8px;
            }
           .dot {
                width: 3px;
                height: 3px;
                background: #8e8d8d;
                display: block;
                border-radius: 50%;
                margin: 1px;
                pointer-events: none;
            }
        `;
        iframeDocument.head.appendChild(style);
    }

    updateHeight() {
        // Get the iframe's document and body elements
        const iframeDocument = this.frame.contentDocument || this.frame.contentWindow.document;
        const iframeBody = iframeDocument.body;
        // Get the height of the content inside the iframe
        const contentHeight = iframeBody.scrollHeight + 40;
        // Set the iframe's height to match the content height
        this.frame.style.height = contentHeight + 'px';

    }

    resize() {

        // Get the iframe's document and body elements
        const iframeDocument = this.frame.contentDocument || this.frame.contentWindow.document;
        const iframeBody = iframeDocument.body;
        // const updateIframeHeight = () => {
        //     // Get the height of the content inside the iframe
        //     const contentHeight = iframeBody.scrollHeight + 40;
        //     // Set the iframe's height to match the content height
        //     this.frame.style.height = contentHeight + 'px';
        // }
        if (iframeBody.scrollHeight > 0) {
            this.updateHeight();
        } else {
            iframeBody.onload = this.updateHeight;
        }


        // this.bodyHeight = this.frame.contentWindow.document.body.offsetHeight;
        // this.frame.contentWindow.document.body.style.scrollbarWidth = 'thin';
        // //this.frame.style.height = this.bodyHeight + "px";
        // //this.dom.element.style.height = this.bodyHeight + "px";
        // this.iframeDoc = this.frame.contentWindow.document;
        // var contentHeight = this.iframeDoc.body.scrollHeight;
        // this.frame.style.height = (contentHeight + 25) + 'px';
        // this.iframeDoc.body.style.cssText = `scrollbar-width: thin;font-family: sans-serif;line-height: 1.5;font-size: 15px;color: #333;`;

    }

}

class DetailsList {
    constructor(data, details, singleItem) {
        this.data = data;
        this.singleItem = singleItem;
        this.details = details;
        this.headerData = data.headers ? JSON.parse(data.headers) : [];
        //console.log(this.headerData);
        this.directAction = ['assign', 'reply', 'trash', 'resend']; //Header Direct Action actions

        this.actions = this.singleItem.detailsActions;
        this.dom = new el('div').class('mail-details-single');
        if (this.details) {
            this.dom.class('loaded');
        }
        this.build();
    }

    scrolltop(wrap) {
        let domPosition = this.dom.element.getBoundingClientRect();
        wrap.scrollTop = (domPosition.y - 52);
    }

    build() {
        this.header = new el('div').class('mail-details-header');
        //If History Item
        if (!this.details) {
            this.header.append(
                new el('div').class('history-toggler').event('click', (event) => {
                    this.getHistoryDetails(event, this.data.id);
                }).element
            );
        }
        //Rs
        if (this.data.rs == '1') {
            this.header.class('outgoing-mail');
            //Outgoing
            //console.log(this.headerData.to[0].address);
            this.fromTo = new el('span').html('To :').append(
                new el('strong').class('fromto-name').html(this.headerData.to[0].display).element
            );
            if (this.headerData.to[0].address) {
                this.fromTo.append(new el('span').class('fromto-address').html("&lt;" + this.headerData.to[0].address + "&gt;").element)
            }
        } else {
            this.header.class('incomming-mail');
            //Incomming
            this.fromTo = new el('span').html('From :').append(
                new el('strong').class('fromto-name').html(this.headerData.from[0].display).element
            );
            if (this.headerData.from[0].address) {
                this.fromTo.append(new el('span').class('fromto-address').html("&lt;" + this.headerData.from[0].address + "&gt;").element)
            }
        }
        // this.fromTo = new el('span').html(this.data.rs == '1' ? 'To :' : 'From :').append(
        //     new el('strong').class('fromto-name').html(this.data.name).element
        // );
        // if (this.data.email) {
        //     this.fromTo.append(new el('span').class('fromto-address').html("&lt;" + this.data.email + "&gt;").element)
        // }

        this.mailTime = new el('label').class('mailTime').html(this.data.date);
        let meta = new el('div').class('meta').append(
            new el('label').class('formTo').append(this.fromTo.element).element
        ).append(
            this.mailTime.element
        )
        this.header.append(
            new el('div').class('mailDetailsInfo').append(
                meta.element
            ).element
        );
        //History snipet
        if (!this.details) {
            meta.append(
                new el('div').class('history-snipet').append(
                    new el('span').class('singleLine').html(
                        this.data.snippet
                    ).element
                ).element
            );
        }

        //SingleDetails Actions
        this.detailsActions = new el('div').class('details-actions');
        this.detailsActions.class('controlDetails');
        //Direct Action Buttons
        this.detailsActions.append(this.direcAction());
        //================================================
        //Dropdown Actions
        let toggler = new el('span').class('dropdown-tolggler').class('moreAction').event('click', (e) => {
            let target = e.target;
            if (target.parentNode.classList.contains('open')) {
                target.parentNode.classList.remove('open');
            } else {
                target.parentNode.classList.add('open');
            }
        }).html(`<span class='dot'></span><span class='dot'></span><span class='dot'></span>`);

        let dropdownWrap = new el('div').class('MailAction').class('dropdown').append(toggler.element);
        dropdownWrap.append(this.singleMailAction());
        this.detailsActions.append(dropdownWrap.element);

        this.header.append(this.detailsActions.element);
        this.dom.append(this.header.element);

        if (this.details) {
            this.body = new MailBody(this, this.actions);
            this.dom.append(this.body.dom.element);
        }
    }

    getHistoryDetails(event, id) {
        let singleDom = event.target.closest('.mail-details-single');
        if (singleDom.classList.contains('loaded')) {
            singleDom.classList.remove('loaded')
        } else {
            let sMailBodytag = singleDom.querySelector('.mail-details-body');
            if (sMailBodytag == null) {
                singleDom.classList.add('loading');
                axios.get(APP_URL + `/get-body/${id}`).then((response) => {
                    singleDom.classList.remove('loading');
                    if (response.data.msg_body != "" && response.data.attachments != "") {
                        this.data.body = response.data.msg_body;
                        this.data.attachments = response.data.attachments;
                        console.log(this.data);
                        let HistoryBody = new MailBody(this, this.actions);
                        singleDom.append(HistoryBody.dom.element);
                        singleDom.classList.add('loaded');
                    }

                }).catch((error) => {
                    singleDom.classList.remove('loading');
                    singleDom.classList.add('loading-error');
                });
            } else {
                singleDom.classList.add('loaded');
            }
        }
    }

    direcAction() {

        let directAct = new el('div').class('direct-actions');

        this.directAction.forEach(el => {
            if (this.actions.hasOwnProperty(el)) {
                let act = this.actions[el];
                directAct.append(this.actionBuild(act))
            }
        });

        return directAct.element;
    }

    singleMailAction() {
        let dropdown = new el('div').class('dropdown-items').class('actionItem');
        for (const name in this.actions) {
            let action = this.actions[name];
            dropdown.append(this.actionBuild(action));
        }
        return dropdown.element;
    }

    actionBuild(action) {
        //----------------------------------------------------------------
        let actDom = new el('div').class('action-wrap');

        if (typeof action.Mailflow !== 'undefined') {
            //When the action is for incomming mail -
            //then outgoing mail actions are not building
            if (this.data.rs === 0 && !action.Mailflow.includes('in')) {
                return actDom.element;
            } else if (this.data.rs === 1 && !action.Mailflow.includes('out')) {
                return actDom.element;
            }
        }

        if (action.label == 'Reminder') {
            actDom.class('set-reminder');
            actDom.append(new el('input').class('date-input').class('hide').attr('type', 'date').element);
        }

        actDom.append(new el('span')
            .class('single-action').attr('title', action.label)
            .class(action.icon).element);
        actDom.append(new el('div').class('action-label').html(action.label).element);
        actDom.event('click', (e) => {
            if (action.hasOwnProperty('singleCallback')) {
                action.singleCallback(this.singleItem, e);
            }
        })
        //----------------------------------------------------------------
        return actDom.element
    }
}

export { DetailsList, Attachments }
