import { Dombuilder, Dombuilder as el } from "@aponahmed/dombuilder";
import BalloonEditor from "@aponahmed/ckeditor5-ballon";//"@ckeditor/ckeditor5-build-balloon";
import { ConfirmBox, Notification, tooltip } from "./elements";
import fileUploader from "./fileUpload";
import axios from "axios";

const autoSaveTrigDur = 2000;

class Subject {
    constructor(subject, parent) {
        this.subject = subject || "";
        this.parent = parent || {};
    }
    ui() {
        this.dom = new el('input')
            .attr('type', 'text').attr('placeholder', 'Subject').attr('value', this.subject)
            .event('keyup', (e) => {
                this.subject = e.target.value;
                this.parent.data.subject = this.subject;
                clearTimeout(this.parent.autoSaveTimeout);
                this.parent.autoSaveTimeout = setTimeout(() => {
                    this.parent.updatedLocalDate().then(() => {
                        this.parent.autoSave();
                    })
                }, autoSaveTrigDur)
            })
            .class('subject').class('compose-input');
        return new el('div').class('subject-wrap').append(this.dom.element).element;
    }
}

class ComposeAddress {
    constructor(addresses = {}) {
        this.addresses = {
            ...{
                to: [],
                cc: [],
                bcc: []
            },
            ...addresses
        }
        this.UiHtm;
    }

    /**
     * Add a new address to the list of addresses
     * @param {object} elm input Object
     * @param {string} type type of recipients
     * @param {object} wrap domBuilder object
     * @returns {void}
     */
    addAddress(elm, type = null, wrap) {
        let str = elm.value.toString();
        if (str == '') {
            return;//Return if Empty string
        }
        //Check if the string is a valid address
        if (this.isEmail(str)) {
            this.addresses[type].push(str);
            let emailDom = this.emailDom(str, type);
            wrap.append(emailDom);
            elm.value = "";
        } else {
            new Notification({
                message: `"${str}" is not a valid email`,
                type: 'error',
            });
        }
    }

    /**
     * Remove addresses from the address list
     * @param {string} address
     * @param {string} type
     */
    removeAddress(address, type = null) {
        this.addresses[type].pop(address);
    }

    /**
     * Checks string to see if it contains an address
     * @param {string} str string of email address
     * @returns {Boolean}
     */
    isEmail(str) {
        var re =
            /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(str).toLowerCase());
    }

    /**
     * Builds a virtual dom for a single email address
     * @param {String} str email address
     * @param {String} type type of recipient
     * @returns {Object} of html DOM elements
     */
    emailDom(str, type) {
        return new el('div').class('singleAddress')
            .html(str)
            .append(
                new el('span').class('removeTo').event('click', (e) => {
                    console.log('remove');
                    //Remove the address
                    this.removeAddress(str, type);
                    e.target.parentNode.remove();
                }).html('&times;').element
            ).element
    }

    /**
     * Build the UI (addresses field) for the composer window
     * @returns {Object} DOM element
     */
    ui() {
        this.UiHtm = new el('div').class('contactArea').class('compose-header');
        for (const type in this.addresses) {

            let addresses = this.addresses[type];
            let uiType = new el('div').class('toArea').class('compose-input');
            uiType.attr('id', type + 'Prt');
            //Contact Wrapper
            let wrap = new el('div').attr('id', type + 'Contacts');
            if (addresses.length > 0) {
                addresses.forEach(email => {
                    this.addAddress[type].push(email);
                    wrap.append(this.emailDom(email));
                });
            }
            uiType.append(wrap.element);

            let input = new el('input')
                .class('contactIn')
                .attr('placeholder', type[0].toUpperCase() + type.slice(1))
                // .event('keyup', (event) => {
                //     if (event.keyCode == 13) {
                //         event.preventDefault();
                //         //this.addAddress(input.element, type, wrap);
                //     }
                // })
                .event('change', (event) => {
                    this.addAddress(input.element, type, wrap);
                })
                .attr('type', 'text');

            uiType.append(input.element)
            this.UiHtm.append(uiType.element);
        }
        return this.UiHtm.element;
    }

}


class Compose {
    constructor({ ...options }) {
        this.SingleItem = options.SingleItem;
        this.dom = null;
        this.type = 'New';
        this.renderTo = options.renderTo;//Dom Element
        this.editor = null;
        this.editorDom = null;

        this.removeExistingWindow();
        this.wraper = new el('div').class('composer-wrapper');
        this.expand = false;

        this.typecls = this.type.replace(" ", "-");
        this.autoSaveTimeout = null
        //Data ---
        this.subjectControl = null;
        this.addressControl = null;

        this.data = {
            id: null,
            replyOf: null, //aplicable for reply and Reply all
            subject: null,
            address: {},
            body: "",
            // [{
            // "filename": [
            //     "SeoSearch.pdf",
            //     "643f92e1b1b11-_-SeoSearch.pdf"
            // ],
            // "contentID": "f_lfxo417g0",
            // "Type": "application/pdf",
            // "size": 4922334
            // },...]
            attachments: []
        };

    }


    removeExistingWindow() {
        let ex = document.querySelector('.composer-wrapper');//Remove Existing Window
        if (ex) {
            ex.remove();
            this.SingleItem.composeWindow = false;
        }
    }

    GoogleDriveWindow() {
        new Notification({ message: "Google Drive Feature in under developement" });
    }

    async send() {
        this.data.status = 'active';
        this.sendButton.class('sending').html('<span class="working"></span>Sending');
        //Leter
        axios.post(APP_URL + '/compose-inline', this.data)
            .then(response => {
                if (response.data.error) {
                    new Notification({
                        message: response.data.message,
                        type: 'error',
                    });
                } else {
                    new Notification({
                        message: response.data.message,
                        type: 'success',
                    });
                    document.querySelector('body').classList.remove('detailsOpend');
                    document.querySelector('.viewWrap').innerHTML = "";
                    this.SingleItem.list.loadData('refresh');
                    this.dom.element.remove();
                }
            }).catch(error => {
                console.log(response);
            });
    }

    async autoSave() {
        console.log(this);
        this.data.status = 'DRAFT';
        if (this.autoSaveDom.element.classList.contains('saved')) {
            this.autoSaveDom.element.classList.remove('saved');
        }
        this.autoSaveDom.element.classList.add("working");//saved
        //console.log(this.data);
        //Save draft to database
        axios.post(APP_URL + '/compose-inline', this.data)
            .then(response => {
                this.autoSaveDom.attr('title', 'Auto saved Succcessfully');
                if (this.autoSaveDom.element.classList.contains('working')) {
                    this.autoSaveDom.element.classList.remove('working');
                }
                if (this.autoSaveDom.element.classList.contains('error')) {
                    this.autoSaveDom.element.classList.remove('error');
                }
                this.autoSaveDom.element.classList.add("saved");//saved
                //console.log(response);
                this.data.id = response.data.id;
            }).catch(error => {
                this.autoSaveDom.attr('title', 'Auto save Error');
                if (this.autoSaveDom.element.classList.contains('error')) {
                    this.autoSaveDom.element.classList.remove('error');
                }
                if (this.autoSaveDom.element.classList.contains('working')) {
                    this.autoSaveDom.element.classList.remove('working');
                }

                this.autoSaveDom.element.classList.add("error");//saved
            });
    }

    async discart() {
        new ConfirmBox({
            param: { obj: this },
            title: "Discard Draft",
            message: "Are you sure to Discard ?",
            yesCallback: (params) => {
                //Remove request to server
                console.log('Remote server request to remove draft');
                params.obj.dom.element.remove();

                //Compose Window State Update
                if (this.SingleItem.composeWindow = this.type) {
                    this.SingleItem.composeWindow = false;
                }
            }
        });

    }

    expandTrig() {
        if (!this.expand) {
            this.expand = true;
            this.wraper.element.classList.add('expanded');
            this.expControll.element.classList.remove('expand-icon');
            this.expControll.element.classList.add('expand-icon-alt');
            this.expControll.attr('title', 'Shrink Window');
        } else {
            this.expand = false;
            this.wraper.element.classList.remove('expanded');
            this.scrollToElement();
            this.expControll.element.classList.remove('expand-icon-alt');
            this.expControll.element.classList.add('expand-icon');
            this.expControll.attr('title', 'Expand Window');
        }
    }

    buildUi() {
        this.dom = new el('div').class('compose');
        this.header = new el('div').class('compose-header-inline');
        //Left Controll  //Dropdown Container
        this.composeOptions = new el('div').class('dropdown-items');
        this.composeOptions.append(
            new el('div').class('item-act').event('click', () => {
                this.discart();
            }).html('Discart').element
        )
        this.header.append(
            new el('div').class('dropdown').append(
                new el('span').class('dropdown-tolggler').event('click', (e) => {
                    if (e.target.parentNode.classList.contains('open')) {
                        e.target.parentNode.classList.remove('open');
                    } else {
                        e.target.parentNode.classList.add('open');
                    }
                }).html(`<span class='dot'></span><span class='dot'></span><span class='dot'></span>`).element
            ).append(
                this.composeOptions.element
            ).element
        );

        //right Controll
        this.quickControll = new el('div').class('compose-quick-controll');
        this.expControll = new el('div').class('expand-controll').event('click', () => {
            this.expandTrig();
        }).class('single-action').class('expand-icon').attr('title', 'Expand Window');
        this.quickControll.append(this.expControll.element);
        //Discart
        this.disCartBtn = new el('div')
            .class('single-action').class('trash-icon')
            .event('click', () => {
                this.discart();
            })
            .class('discart-icon').attr('title', 'Discart');
        this.quickControll.append(this.disCartBtn.element);

        this.header.append(this.quickControll.element);

        //Body
        this.dom.append(this.header.element);
        this.editorDom = new el('div').class('editor-element').attr('id', 'editorElement').class(this.typecls);
        this.body = new el('div').class('compose-body').class(this.typecls);
        this.body.append(this.editorDom.element);
        this.dom.append(this.body.element);

        //Composer Controllers
        this.sendButton = new el('button').html('Send').event('click', () => {
            this.send().then(() => {
                console.log('Sent message')
            });
        }).attr('type', 'button').class('btn').class('btn-primary');

        this.footer = new el('div').class('compose-footer').append(
            //SendButton Append
            this.sendButton.element
        ).class(this.typecls);
        //Auto Save Viewer
        this.autoSaveDom = new el('span').class('autosave');
        this.footer.append(this.autoSaveDom.element);
        //Attachment from local
        let attArea = new el('div').class('attachment-area');
        attArea.append(new el('div').class('attachments').element);

        this.mediaInput = new el('input').class('collapse')
            .attr('type', 'file').attr('id', 'attachment-selector')
            .event('change', () => {
                this.addAttachment();
            });
        attArea.append(this.mediaInput.element);
        attArea.append(
            new el('label').class('tooltip')
                .attr('data-position', 'top').attr('data-bg', '#555')
                .attr('for', 'attachment-selector')
                .attr('title', 'Attach File')
                .html(`<svg xmlns='http://www.w3.org/2000/svg' class='ionicon' viewBox='0 0 512 512'><title>Attach</title><path d='M216.08 192v143.85a40.08 40.08 0 0080.15 0l.13-188.55a67.94 67.94 0 10-135.87 0v189.82a95.51 95.51 0 10191 0V159.74' fill='none' stroke='currentColor' stroke-linecap='round' stroke-miterlimit='10' stroke-width='32'/></svg>`)
                .element
        )
        //Google Drive Attachment Handler
        attArea.append(
            new el('label').class('tooltip')
                .attr('title', 'Attach File from Google Drive')
                .attr('data-position', 'top').attr('data-bg', '#555')
                .class('attach-gdrive').class('tooltip')
                .html(`<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 32 32"><path d="M 11.4375 5 L 11.15625 5.46875 L 3.15625 18.46875 L 2.84375 18.96875 L 3.125 19.5 L 7.125 26.5 L 7.40625 27 L 24.59375 27 L 24.875 26.5 L 28.875 19.5 L 29.15625 18.96875 L 28.84375 18.46875 L 20.84375 5.46875 L 20.5625 5 Z M 13.78125 7 L 19.4375 7 L 26.21875 18 L 20.5625 18 Z M 12 7.90625 L 14.96875 12.75 L 8.03125 24.03125 L 5.15625 19 Z M 16.15625 14.65625 L 18.21875 18 L 14.09375 18 Z M 12.875 20 L 26.28125 20 L 23.40625 25 L 9.78125 25 Z"></path></svg>`)
                .event('click', () => {
                    this.GoogleDriveWindow()
                }).element
        )
        this.footer.append(attArea.element);
        //Attachment Size
        this.footer.append(new el('div').attr('id', 'attach_size').html('0 KB').element);

        this.dom.append(new el('div').class('compose-head').append(this.footer.element).element);
    }

    attSizeCalc() {
        let size = 0;
        this.data.attachments.forEach(element => {
            size += element.size;
        });

        document.getElementById("attach_size").innerHTML = this.uploader.humanFileSize(size);
    }

    async addAttachment() {
        let file = this.mediaInput.element;
        let upwrap = document.querySelector(".attachments");
        this.uploader = new fileUploader({
            route: APP_URL + "/upload-attachment",//Upload Route
            items: upwrap,
            name: 'mail_attachment',
            removeCallback: (obj) => {
                let contentIDToFind = obj.item.getAttribute('data-id');
                const indexToRemove = this.data.attachments.findIndex(elm => elm.contentID === contentIDToFind);
                if (indexToRemove !== -1) {
                    this.data.attachments.splice(indexToRemove, 1);
                    obj.item.remove();
                }

                this.autoSave();
                this.attSizeCalc();
            },
            onProcess: (obj, p) => {
                //console.log(obj.file);

            },
            onComplete: (obj) => {
                console.log('upload complete');
            },
            callback: (res, obj) => {
                if (res.data.error) {
                    //console.log('done uploading')
                    ntf(res.data.msg, 'error');
                } else {
                    let indx = this.data.attachments.push(res.data.details) - 1;
                    obj.item.setAttribute("data-id", res.data.details.contentID);
                    this.autoSave();
                    this.attSizeCalc();
                }
            },
            FormData: {
                'upload-type': 'attachment',
            },
        });
        let files = file.files;
        this.uploader.upload(files[0]);
    }

    async updatedLocalDate() {
        this.data.body = this.editor.getData();
        this.data.subject = this.subjectControl.subject;
    }

    async initEditor() {
        await BalloonEditor.create(this.editorDom.element, {
            height: 200,
            dataAllowedContent: 'style[type]',
            allowedContent: ['p', 'style'],
            htmlSupport: {
                allow: ['style'],
                disallow: [ /* HTML features to disallow */]
            }
        })
            .then((editor) => {
                this.editor = editor;
                this.editor.model.document.on('change:data', (data) => {
                    clearTimeout(this.autoSaveTimeout);
                    this.autoSaveTimeout = setTimeout(() => {
                        this.updatedLocalDate().then(() => {
                            this.autoSave();
                        })
                    }, autoSaveTrigDur)
                })

            })
            .catch((error) => {
                console.error("There was a problem initializing the editor.", error);
            });
    }

    render() {
        if (this.SingleItem.composeWindow !== this.type) {

            this.SingleItem.composeWindow = this.type;

            this.wraper.append(this.dom.element);
            this.renderTo.appendChild(this.wraper.element);
        }
    }

    scrollToElement() {
        let el = document.querySelector('.composer-wrapper');
        let vewWindow = document.querySelector('.details-wrapper');
        //console.log(el.offsetTop, vewWindow.scrollHeight, el.offsetHeight);
        //vewWindow.scrollTop = el.offsetTop;
        vewWindow.scrollTop = vewWindow.scrollHeight + el.offsetHeight;

    }

    afterRender() {

        this.initEditor().then(() => {
            this.scrollToElement();
            //console.log('After Render');
            this.afterRenderCh();
            //Update Local Datas
            this.updatedLocalDate().then(() => {
                console.log('Local data Updated');
            });
        });
        new tooltip({
            selector: '.tooltip'
        });

    }
}

class Replay extends Compose {
    async init() {
        this.type = 'Reply';
        this.buildUi();

        //Subject Control
        this.subjectControl = new Subject(this.SingleItem.item.subject, this);
        this.data.replyOf = { id: this.SingleItem.item.id, type: 'reply' };
        this.header.append(
            new el('div').class('header-inputs')
                .append(new el('div').class('name').html("<span class='composeAction'>Reply To:</span> " + this.SingleItem.item.customerName).element)
                .append(
                    this.subjectControl.ui()
                ).element
        );
        this.composeOptions.append(
            new el('div').class('item-act').class('subjectTrigger').event('click', (e) => {
                let ccWrap = e.target.closest('.composer-wrapper').querySelector('.subject-wrap');
                ccWrap.classList.toggle('open');
                e.target.classList.toggle('open');
                if (e.target.classList.contains('open')) {
                    e.target.innerHTML = "Close editing Subject";
                } else {
                    e.target.innerHTML = "Edit Subject";
                }
                e.target.closest('.dropdown').classList.remove('open');
            }).html('Edit Subject').element
        )
        //Render DOM
        this.render();
    }

    afterRenderCh() {

    }
}

class ReplayAll extends Compose {
    async init() {
        this.type = 'Reply All';
        this.buildUi();
        //Subject Control
        this.subjectControl = new Subject("Re : " + this.SingleItem.item.subject);
        this.data.replyOf = { id: this.SingleItem.item.id, type: 'replyall' };
        this.header.append(
            new el('div').class('header-inputs')
                .append(new el('div').class('name').html("<span class='composeAction'>Reply To All:</span> " + this.SingleItem.item.customerName).element)
                .append(
                    this.subjectControl.ui()
                ).element
        );
        this.composeOptions.append(
            new el('div').class('item-act').class('subjectTrigger').event('click', (e) => {
                let ccWrap = e.target.closest('.composer-wrapper').querySelector('.subject-wrap');
                ccWrap.classList.toggle('open');
                e.target.classList.toggle('open');
                if (e.target.classList.contains('open')) {
                    e.target.innerHTML = "Close editing Subject";
                } else {
                    e.target.innerHTML = "Edit Subject";
                }
                e.target.closest('.dropdown').classList.remove('open');
            }).html('Edit Subject').element
        )
        this.render();
    }

    afterRenderCh() {

    }
}

class Forward extends Compose {
    async init() {
        this.type = 'Forward';
        this.buildUi();
        //Compose Type
        this.header.append(new el('div').class('name').html("<span class='composeAction'>Forward</span>").element);
        this.addressControl = new ComposeAddress();
        this.subjectControl = new Subject();
        this.data.address = this.addressControl.addresses;
        console.log(this.SingleItem);
        let forwStr = `---------- Forwarded message ---------<br>
        From: ${this.SingleItem.item.customerName} <hello@updates.truecaller.com><br>
        Date: ${this.SingleItem.item.date}<br>
        Subject: ${this.SingleItem.item.subject}<br>
        To: <apon2041@gmail.com><br>`;
        forwStr += this.SingleItem.detailsData.body
        this.editorDom.html(forwStr);

        this.header.append(
            new el('div').class('header-inputs')
                .append(
                    this.addressControl.ui()
                )
                .append(
                    this.subjectControl.ui()
                ).element
        );

        this.composeOptions.append(
            new el('div').class('item-act').class('subjectTrigger').event('click', (e) => {
                let ccWrap = e.target.closest('.composer-wrapper').querySelector('.subject-wrap');
                ccWrap.classList.toggle('open');
                e.target.classList.toggle('open');
                if (e.target.classList.contains('open')) {
                    e.target.innerHTML = "Close editing Subject";
                } else {
                    e.target.innerHTML = "Edit Subject";
                }
                e.target.closest('.dropdown').classList.remove('open');
            }).html('Edit Subject').element
        )
        this.composeOptions.append(
            new el('div').class('item-act').class('ccTrigger').event('click', (e) => {
                let ccWrap = e.target.closest('.composer-wrapper').querySelector('#ccPrt');
                ccWrap.classList.toggle('open');
                e.target.classList.toggle('open');
                if (e.target.classList.contains('open')) {
                    e.target.innerHTML = "Close CC";
                } else {
                    e.target.innerHTML = "CC";
                }
                e.target.closest('.dropdown').classList.remove('open');
            }).html('CC').element
        )
        this.composeOptions.append(
            new el('div').class('item-act').class('bccTrigger').event('click', (e) => {
                let bccWrap = e.target.closest('.composer-wrapper').querySelector('#bccPrt');
                bccWrap.classList.toggle('open');
                e.target.classList.toggle('open');
                if (e.target.classList.contains('open')) {
                    e.target.innerHTML = "Close BCC";
                } else {
                    e.target.innerHTML = "BCC";
                }
                e.target.closest('.dropdown').classList.remove('open');
            }).html('BCC').element
        )

        //console.log(this);
        this.render();
    }

    afterRenderCh() {
        let forwStr = `---------- Forwarded message ---------<br>
        From: Truecaller <hello@updates.truecaller.com><br>
        Date: Fri, Mar 10, 2023 at 3:46 PM<br>
        Subject: The new Truecaller is better than ever!<br>
        To: <apon2041@gmail.com><br>`;
        forwStr += this.SingleItem.detailsData.body
        //this.editor.setData(forwStr);
        //this.editor.SetHtml(forwStr);
    }
}


export { Replay, ReplayAll, Forward }
