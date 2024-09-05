import $ from "jquery";

//Mail View
class mailview {
    constructor(ApiResponse) {
        this.response = ApiResponse.data;
        this.init();
        this.controlbtn = {
            //'admin': { reply: "Reply", foreword: "Foreword" },
            reply: {
                label: "Reply",
                access: [],
            },
        };
    }

    init() {
        //console.log("initializing Mail View...");
        this.structure = `
    <span class="closeView" onclick="closeDetails()">×</span>
    <div class="mail-details-single">
        <div class="mail-details-header">
            <h3 class="mail-details-subject"></h3>
            <div class="mailDetailsInfo">
                <div class="meta">
                  <label class="formTo"><span></span></label>
                  <div><label class='mailTime'></label></div>
                </div>
                <div class="controlDetails">
                  <a href="">Reply</a>
                  <div class="MailAction dropdown">
                     <span class="dropdown-tolggler moreAction"><span class='dot'></span><span class='dot'></span><span class='dot'></span></span>
                      <div class="dropdown-items actionItem">
                          <a href="">Reply</a>
                          <a href="">Forword</a>
                          <a href="">Spam</a>
                          <a href="">Print</a>

                          <a href="">Mark Unread</a>
                          <a href="">Trash</a>
                          <a href="">Delete Forever</a>

                      </div>
                  </div>
                </div>
            </div>

        </div>
        <div class="mail-details-body"></div>
    </div>`;
    }
    append2() {
        //console.log(this.response.subject);
        $(".viewWrap").html(this.structure);
        $(".mail-details-subject").html(this.response.subject);
        let formTo =
            "<span class='customerName'>" + this.response.customer + "</span>";
        if (this.response.customerEmail != "... ...") {
            //if Not Filtered
            formTo += "&nbsp;&lt;" + this.response.customerEmail + "&gt;";
        }
        if (this.response.rs == 0) {
            $(".formTo").find("span").html("Form : ");
        } else {
            $(".formTo").find("span").html("To : ");
        }
        $(".mailTime").html(this.response.date);

        $(".formTo").append(formTo);
        let frame = document.createElement("iframe");
        frame.setAttribute("id", "detailsFrame");
        frame.style.border = "none";
        frame.style.width = "100%";
        frame.style.height = "100%";
        frame.srcdoc = this.response.body;
        $(".mail-details-body").append(frame);
    }
}




function dragElement(elmnt) {
    var pos1 = 0,
        pos2 = 0,
        pos3 = 0,
        pos4 = 0;
    if (document.querySelector(".popUpHeader")) {
        // if present, the header is where you move the DIV from:
        document.querySelector(".popUpHeader").onmousedown = dragMouseDown;
    } else {
        // otherwise, move the DIV from anywhere inside the DIV:
        elmnt.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = elmnt.offsetTop - pos2 + "px";
        elmnt.style.left = elmnt.offsetLeft - pos1 + "px";
    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

export { mailview, dragElement };
