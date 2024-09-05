import axios from "axios";
import { Dombuilder as el } from "@aponahmed/dombuilder";
import { DetailsList } from "./MailBody";
import { ConfirmBox, Notification } from "./elements";
//import SingleItem from "./MailSingleList";

export default class ReadMail {
    constructor(singleItem) {
        this.loadMoreAttempts = 0;
        this.singleItem = singleItem;
        this.body = document.querySelector('body');
        this.listData = this.singleItem.item;
        this.listDom = this.singleItem.dom;
        this.data = {};
        //Get Data from  server via API
        //console.log(this);
        this.getData();

        this.markapInitiator();
    }

    itemLoader() {
        let existingActive = document.querySelector(".current");
        if (existingActive) {
            existingActive.classList.remove("current");
        }
        this.listDom.classList.add('current');
        this.listDom.classList.add('loading');
    }

    /**
     * Current List Item scrolling to middle of list
     */
    scrolltop() {
        let domPosition = this.listDom.getBoundingClientRect();
        let wrap = this.listDom.parentNode;
        let wrapInfo = wrap.getBoundingClientRect();
        //console.log(this.listDom.offsetTop, wrap.height);//- (wrap.scrollHeight / 2)
        let top = this.listDom.offsetTop - (75 + (wrapInfo.height / 2)) + domPosition.height;
        wrap.scrollTop = top; //(this.listDom.offsetTop);
    }

    removeLoader() {
        this.listDom.classList.remove('loading');
        if (this.listDom.classList.contains('unreaded')) {
            this.listDom.classList.remove('unreaded');
        }
        this.scrolltop()

    }

    markapInitiator() {
        this.itemLoader();

        this.body.classList.add('detailsOpend');
        this.view = new el('div').class('mail-view');
        this.header = new el('div').class('mail-header');
        let closeBtn = new el('span').class('closeView').event('click', () => {
            this.closeWindow();
        }).html('<svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="48" d="M244 400L100 256l144-144M120 256h292"/></svg>').element;
        this.header.append(closeBtn).append(new el('h3').html(this.listData.subject).element);

        this.bodyWrapper = new el('div').class('details-wrapper');

        //History Loader
        this.historyLoader = null;

        this.view.append(this.header.element);
        this.view.append(this.bodyWrapper.element);
    }

    closeWindow() {
        this.body.classList.remove('detailsOpend');
        this.view.element.remove();
    }

    /**
     * Get Current Data and Thread lists
     */
    async getData() {
        axios.post(APP_URL + '/read-mail',
            {
                id: this.listData.id
            })
            .then((response) => {
                this.removeLoader();
                this.data = response.data;
                this.singleItem.detailsData = this.data;
                this.historyData = this.data.historyData;
                this.rander();
            }).catch(error => {
                console.log(error);
                new Notification({ type: 'error', message: error.message });
            });
    }

    render2(dom) {
        dom.appendChild(this.view.element);
    }

    rander() {
        //history
        if (this.historyData.hasMore) {
            this.historyLoader = new el('div').class('tooltip').class('history-loader').event('click', (e) => {
                this.loadMore();
            }).attr('title', 'Load More History')
                .append(new el('span').class('dot').element)
                .append(new el('span').class('dot').element)
                .append(new el('span').class('dot').element);

            this.bodyWrapper.append(this.historyLoader.element);
        }
        //Current Mail
        //inline actions
        if (this.historyData.history.length > 0) {
            this.historyWrap = new el('div').class('history-wrap');
            this.historyData.history.forEach(element => {
                let dList = new DetailsList(element, false, this.singleItem);
                this.historyWrap.append(dList.dom.element);
            });
            //this.bodyWrapper.append(historyWrap.element);
            this.rend(this.historyWrap.element);
        }
        let currentDetails = new DetailsList(this.data, true, this.singleItem);
        //this.bodyWrapper.append(currentDetails.dom.element);
        this.rend(currentDetails.dom.element).then(() => {
            currentDetails.scrolltop(this.bodyWrapper.element);
        });

    }

    async getMoreData() {
        this.historyLoader.class('loading-history');
        await axios.post(APP_URL + '/load-more-mail',
            {
                id: this.listData.id,
                lastid: this.historyData.nextid,
                number_of_items: this.loadMoreAttempts,
            })
            .then((response) => {
                this.historyLoader.element.classList.remove('loading-history');
                //this.removeLoader();
                let historyData = response.data;
                this.historyData.nextid = historyData.nextid;
                this.mergeObjectsDeepPrepend(this.historyData, historyData);
                //console.log(this.historyData);
                if (historyData.history.length > 0) {
                    historyData.history.forEach(element => {
                        let historyListItem = new DetailsList(element, false, this.singleItem);
                        //console.log(historyListItem);
                        historyListItem.dom.class('feed-in');
                        this.historyWrap.element.prepend(historyListItem.dom.element);
                    });
                }
                if (!historyData.hasMore && this.loadMoreAttempts != 'all') {
                    console.log('No more history data');
                    new Notification({ type: 'info', message: "No more history in this therad.", timeout: 3000 });
                    this.historyLoader.element.remove()
                }
                if (this.loadMoreAttempts == 'all') {
                    this.historyLoader.element.remove();
                }
                //this.rander();
            }).catch(error => {
                console.log(error);
                new Notification({ type: 'error', message: error.message });
            });
    }

    async loadMore() {
        this.loadMoreAttempts++;
        //loading more
        if (this.loadMoreAttempts > 1) {
            //Confirm
            new ConfirmBox({
                title: "Load All", message: "Load All from this conversation ?",
                yesCallback: async () => {
                    this.loadMoreAttempts = 'all';
                    await this.getMoreData();
                },
                noCallback: async () => {
                    await this.getMoreData();
                }
            });
        } else {
            await this.getMoreData();
        }

    }

    async rend(element) {
        await this.bodyWrapper.append(element);
    }

    mergeObjectsDeepPrepend(target, source) {
        if (Array.isArray(target) && Array.isArray(source)) {
            target.unshift(...source);
        } else if (typeof target === "object" && typeof source === "object") {
            for (let key in source) {
                if (source.hasOwnProperty(key)) {
                    if (target.hasOwnProperty(key) && typeof target[key] === "object" && typeof source[key] === "object") {
                        this.mergeObjectsDeepPrepend(target[key], source[key]);
                    } else {
                        target[key] = source[key];
                    }
                }
            }
        }
        return target;
    }
}
