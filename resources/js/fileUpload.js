import axios from "axios";

class fileUploader {

    constructor({ ...options }) {
        this.route = options.route || '';
        this.items = options.items || false;
        this.name = options.name || 'file';
        this.callback = options.callback || false;
        this.onProcess = options.onProcess || false;
        this.removeCallback = options.removeCallback || function () { };
        this.onComplete = options.onComplete || false;
        this.response = false;
        this.formData = options.FormData || false;//{'fild':val}
    }

    onUploadProgress = event => {
        const percentCompleted = Math.round((event.loaded * 100) / event.total);
        if (percentCompleted == 100 && this.onComplete) {
            this.onComplete(this);
            this.progressBar.style.display = 'none';
        }
        if (this.items) {
            this.item.querySelector('.up-progress').style.width = percentCompleted + "%";
            if (this.onProcess) {
                this.onProcess(this, event);
            }
            //console.log('onUploadProgress', percentCompleted, this.item);
        }

    }

    removeAttachment() {
        //this.item.remove();
        this.removeCallback(this);
        //Remove File from Server
        //Request to remove
    }

    createProgressbar = () => {
        this.item = document.createElement('div');
        this.item.classList.add('file-item');
        this.progressBar = document.createElement('span');
        this.progressBar.classList.add('up-progress');
        this.item.appendChild(this.progressBar);
        //File Name
        this.label = document.createElement('label');
        this.label.classList.add('file-name');

        this.label.innerHTML = this.file.name;

        this.cancelBtn = document.createElement('span');
        this.cancelBtn.classList.add('cancel-btn');
        this.cancelBtn.innerHTML = "&times;";
        this.cancelBtn.addEventListener('click', (e) => {
            this.removeAttachment();
        });
        this.item.appendChild(this.cancelBtn);
        this.item.appendChild(this.label);
        this.items.appendChild(this.item);
        this.setInfo();
    }


    setInfo() {
        this.item.dataset.tooltip = this.humanFileSize(this.file.size);
        //Icons Html will
        let iconHtml = document.createElement('span');
        iconHtml.classList.add('file-icon');
        iconHtml.classList.add(`file-${this.extension}`);
        this.item.appendChild(iconHtml);
    }

    upload = async file => {
        this.file = file;
        let re = /(?:\.([^.]+))?$/;
        this.extension = re.exec(this.file.name)[1];
        if (this.items) {
            this.createProgressbar();
        }
        //console.log(files);
        const data = new FormData();
        if (this.formData) {
            for (const field in this.formData) {
                data.append(field, this.formData[field]); //
            }
        }
        //for (const  of files) {
        data.append(this.name, file); // append all files
        // }

        try {
            this.response = await axios.post(this.route, data, { onUploadProgress: this.onUploadProgress });
        } catch (error) {
            console.error(error);
        } finally {
            console.log('Upload complete');
            if (this.callback) {
                this.callback(this.response, this);
            }
        }
    }

    /*
    * @param bytes Number of bytes.
    * @param si True to use metric (SI) units, aka powers of 1000. False to use
    *           binary (IEC), aka powers of 1024.
    * @param dp Number of decimal places to display.
    * @return Formatted string.
    */
    humanFileSize(bytes, si = true, dp = 1) {
        const thresh = si ? 1000 : 1024;
        if (Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        const units = si
            ? ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
            : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        let u = -1;
        const r = 10 ** dp;
        do {
            bytes /= thresh;
            ++u;
        } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);
        return bytes.toFixed(dp) + ' ' + units[u];
    }
}

export default fileUploader;
