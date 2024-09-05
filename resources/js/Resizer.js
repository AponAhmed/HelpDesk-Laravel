export default class Resizer {
    constructor(resizeArea = null, options = null) {
        this.resizeArea = resizeArea || document.querySelector('.resizable');
        this.resizeType = this.resizeArea.dataset?.resizeType || options.type || 'horizontal';

        this.isDragging = false;
        this.callback = null;

        this.props = {
            width: this.resizeType.width,
            height: this.resizeType.height
        }

        this.createHandler();

        if (options) {
            if (options.hasOwnProperty('handler')) {
                this.handler = options.handler;
            }
            this.callback = options.hasOwnProperty('callback') ? options.callback : () => { };
        }

        this.handler.addEventListener('mousedown', (e) => {
            this.handleMouseDown(e);
        });
        document.addEventListener('mousemove', (e) => {
            this.handleMouseMove(e);
        });
        document.addEventListener('mouseup', (e) => {
            this.handleMouseUp(e);
        });
    }

    createHandler() {
        this.handler = document.createElement('div');
        this.handler.classList.add(`resizer-${this.resizeType}`);
        this.resizeArea.appendChild(this.handler);

    }

    handleMouseDown(event) {

        this.isDragging = true;
        this.startPosition = {
            x: event.clientX,
            y: event.clientY,
            width: this.resizeArea.clientWidth,
            height: this.resizeArea.clientHeight
        };

        //event.preventDefault();
    }

    handleMouseMove(event) {
        //console.log( this.isDragging);
        if (this.isDragging) {
            let deltaX = event.clientX - this.startPosition.x;
            let deltaY = event.clientY - this.startPosition.y;
            //console.log(deltaX);
            if (this.resizeType === 'horizontal') {
                this.props.width = (this.startPosition.width + (deltaX *= 1.3));
                this.resizeArea.style.width = this.props.width + 'px';

            } else if (this.resizeType === 'vertical') {
                this.props.height = (this.startPosition.height + deltaY);
                this.resizeArea.style.height = this.props.height + 'px';
            }
        }
    }

    handleMouseUp() {
        if (this.isDragging) {
            if (this.callback) {
                this.callback(this.props);
            }
        }
        this.isDragging = false;
    }
}
